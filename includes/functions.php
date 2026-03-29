<?php
/**
 * Global functions for Gatepilot
 * NOTE: Core auth and utility functions are in includes/init.php.
 * This file contains specialized dynamic audit helpers.
 */

/**
 * Returns a connection to the centralized support database for issue reporting
 */
function getSupportDatabaseConnection()
{
    // 1. Try connecting to the Centralized Support DB
    try {
        $conn = @mysqli_connect(SUPPORT_DB_HOST, SUPPORT_DB_USER, SUPPORT_DB_PASS, SUPPORT_DB_NAME);
        if ($conn) {
            mysqli_query($conn, "SET time_zone = '+05:30'");
            mysqli_set_charset($conn, "utf8mb4");
            return $conn;
        }
    } catch (Throwable $e) {
        error_log("CENTRAL SUPPORT DB CONNECTION FAILED: " . $e->getMessage());
    }

    // 2. FALLBACK: If local environment, use the primary local database for testing
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'LOCAL') {
        global $conn; // Uses the main connection from init.php
        if ($conn) {
            // Check if app_issues table exists in primary DB
            $res = mysqli_query($conn, "SHOW TABLES LIKE 'app_issues'");
            if (mysqli_num_rows($res) == 0) {
                // Initialize it locally for testing
                include_once __DIR__ . '/../database/init_app_issues.php';
                initAppIssuesTable($conn);
            }
            return $conn;
        }
    }

    return null;
}

/**
 * ============================================================
 * DYNAMIC AUDIT HELPERS
 * ============================================================
 * These functions make audit logging self-maintaining:
 *   auditFromPost()  → logs every submitted field on CREATE
 *   auditDiff()      → logs only changed fields on UPDATE
 *
 * Adding a new field to a form + DB column is automatically
 * captured in audit logs with zero extra code changes.
 * ============================================================
 */

/**
 * Build a human-readable audit string from all non-empty POST
 * fields (for CREATE operations). Automatically skips internal
 * system fields, button names, and sensitive values.
 *
 * @param array  $post    The $_POST  superglobal (or subset)
 * @param array  $skip    Additional field names to skip
 * @param array  $labels  Optional ['field_name' => 'Human Label']
 * @return string         "Field1: [val1], Field2: [val2], ..."
 */
function auditFromPost(array $post, array $skip = [], array $labels = []): string
{
    $system_skip = [
        'save_user',
        'save_transporter',
        'save_driver',
        'save_vehicle',
        'save_employee',
        'save_register',
        'save_department',
        'save_patrol',
        'save_material',
        'save_supplier',
        'save_purpose',
        'save_webhook',
        'submit_inward',
        'submit_outward',
        'update_inward',
        'update_outward',
        'update_register',
        'update_ticket',
        'import_employees',
        'import_suppliers',
        'git_sync',
        'upload_logo',
        'add_type',
        'update_fields',
        'user_id',
        'trans_id',
        'driver_id',
        'vehicle_id',
        'e_id',
        'department_id',
        'location_id',
        'material_id',
        'supplier_id',
        'purpose_id',
        'inward_id',
        'id',
        'register_type',
        'reg_type',
        'password',
        'password_confirm',
        'new_password',
        'old_password',
        'ajax',
        '_token',
        'items',
        'qr_code_data',
        'qr_raw_data',
        'dynamic_data',
        'commit_remarks',
        'webhook_url',
    ];
    $skip_all = array_merge($system_skip, $skip);
    $parts = [];
    foreach ($post as $key => $val) {
        if (in_array($key, $skip_all, true))
            continue;
        if (is_array($val))
            continue;
        $val = trim((string) $val);
        if ($val === '')
            continue;
        $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
        $parts[] = "$label: [$val]";
    }
    return implode("\n", $parts);
}

/**
 * Build a human-readable diff string by comparing submitted POST
 * fields against the existing DB row (for UPDATE operations).
 * Only fields present in BOTH $_POST and the DB row are compared.
 *
 * @param array  $old_row  Associative array fetched from DB
 * @param array  $post     The $_POST superglobal (or subset)
 * @param array  $skip     Additional field names to skip
 * @param array  $labels   Optional ['field_name' => 'Human Label']
 * @return string          "Field1: [old ➔ new], ..."
 */
function auditDiff(array $old_row, array $post, array $skip = [], array $labels = []): string
{
    $system_skip = [
        'save_user',
        'save_transporter',
        'save_driver',
        'save_vehicle',
        'save_employee',
        'save_register',
        'save_department',
        'save_patrol',
        'save_material',
        'save_supplier',
        'save_purpose',
        'save_webhook',
        'submit_inward',
        'submit_outward',
        'update_inward',
        'update_outward',
        'update_register',
        'update_ticket',
        'import_employees',
        'import_suppliers',
        'git_sync',
        'upload_logo',
        'add_type',
        'update_fields',
        'user_id',
        'trans_id',
        'driver_id',
        'vehicle_id',
        'e_id',
        'department_id',
        'location_id',
        'material_id',
        'supplier_id',
        'purpose_id',
        'inward_id',
        'id',
        'register_type',
        'reg_type',
        'password',
        'password_confirm',
        'new_password',
        'old_password',
        'ajax',
        '_token',
        'items',
        'qr_code_data',
        'qr_raw_data',
        'dynamic_data',
        'commit_remarks',
        'webhook_url',
    ];
    $skip_all = array_merge($system_skip, $skip);
    $changes = [];
    foreach ($post as $key => $new_val) {
        if (in_array($key, $skip_all, true))
            continue;
        if (is_array($new_val))
            continue;
        if (!array_key_exists($key, $old_row))
            continue;
        $new_val = trim((string) $new_val);
        $old_val = trim((string) ($old_row[$key] ?? ''));
        if ($old_val !== $new_val) {
            $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
            $changes[] = "$label: [$old_val ➔ $new_val]";
        }
    }
    return implode("\n", $changes);
}

/**
 * Provision a new tenant database and register it in the master table
 * Restricted to Super Admins only
 */
function createTenant($customer_name, $slug, $admin_username, $admin_password, $contact_person = '', $mobile = '', $email = '', $address = '', $gst_no = '')
{
    // 1. Validate Input
    if (empty($admin_username) || empty($admin_password)) {
        return ["success" => false, "message" => "❌ Admin Username and Password are mandatory fields!"];
    }

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $slug));

    // Prefix for grouping in phpMyAdmin and shortening for compliance
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'LOCAL') {
        $db_name = "gp_" . $slug; // Local prefix
    } else {
        // PRODUCTION: Use Hostinger account prefix if available
        // Usually, the DB_USER or DB_NAME will have the prefix like u875321134_
        $prefix = explode('_', DB_NAME)[0]; // Extract account ID (e.g. u875321134)
        $db_name = $prefix . "_gp_" . $slug;
    }

    $master_conn = getMasterDatabaseConnection();

    // Check if slug exists
    $check = mysqli_query($master_conn, "SELECT id FROM tenants WHERE slug = '$slug'");
    if (mysqli_num_rows($check) > 0) {
        return ["success" => false, "message" => "This slug '$slug' is already taken!"];
    }

    // 2. Validate Password uniqueness across all tenants
    $admin_password_trimmed = trim($admin_password);
    $all_pw_q = mysqli_query($master_conn, "SELECT slug, admin_password_hash FROM tenants WHERE admin_password_hash IS NOT NULL AND admin_password_hash != ''");
    while ($row = mysqli_fetch_assoc($all_pw_q)) {
        if (password_verify($admin_password_trimmed, $row['admin_password_hash'])) {
            $existing_slug = strtoupper($row['slug']);
            return ["success" => false, "message" => "❌ Security Violation: This password is already being used by the '$existing_slug' system! For security compliance, every customer must have a unique administrative password."];
        }
    }

    // 3. Create New Database
    // CAUTION: On Hostinger, dynamic CREATE DATABASE is blocked. 
    // We catch the SQL exception to prevent a system crash (White Page).
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$conn) {
        return ["success" => false, "message" => "MySQL Server connection failed: " . mysqli_connect_error()];
    }

    try {
        $create_q = mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        // This is where Hostinger blocks us
        $msg = $e->getMessage();
        if (strpos($msg, 'Access denied') !== false) {
            return [
                "success" => false, 
                "message" => "❌ <b>Database Creation Blocked by Hostinger:</b> Your hosting plan does not allow automatic database creation via PHP.<br><br><b>What to do now:</b><br>1. Log into your Hostinger hPanel.<br>2. Manually create a MySQL Database named: <b>$db_name</b><br>3. Assign the user <b>" . DB_USER . "</b> to that database.<br>4. Run this 'Create Tenant' tool again.<br><br><i>(Technical Error: $msg)</i>"
            ];
        }
        return ["success" => false, "message" => "Failed to provision database: " . $msg];
    }
    
    if (!mysqli_select_db($conn, $db_name)) {
        return ["success" => false, "message" => "❌ Database <b>$db_name</b> exists, but your user '" . DB_USER . "' does not have permission to access it. Please add this user to the database in your Hostinger Panel."];
    }

    // 4. Import Schema from database/schema.sql
    $schema_file = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schema_file)) {
        return ["success" => false, "message" => "Schema file not found at $schema_file"];
    }

    $sql = file_get_contents($schema_file);

    // Split and run each query
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (!empty($query)) {
            // Use @ to suppress errors if tables exist, or check result
            @mysqli_query($conn, $query);
        }
    }

    // 5. Create Initial Admin User in the NEW database (WITH DYNAMIC PERMISSIONS)
    $hashed_pass = password_hash($admin_password, PASSWORD_DEFAULT);
    $full_perms = '{}'; // Admin permissions are dynamically bypassed in init.php->hasPermission()

    // Sanitize metadata for the NEW tenant database connection ($conn)
    $esc_mobile = mysqli_real_escape_string($conn, $mobile);
    $esc_email = mysqli_real_escape_string($conn, $email);

    $admin_sql = "INSERT INTO user_master (username, password, full_name, role, super_admin, is_active, permissions, mobile, email) 
                  VALUES ('$admin_username', '$hashed_pass', '$customer_name Admin', 'admin', 0, 1, '$full_perms', '$esc_mobile', '$esc_email')
                  ON DUPLICATE KEY UPDATE 
                  password = VALUES(password), 
                  full_name = VALUES(full_name), 
                  role = 'admin',
                  super_admin = 0,
                  is_active = 1,
                  permissions = VALUES(permissions),
                  mobile = VALUES(mobile),
                  email = VALUES(email)";
    mysqli_query($conn, $admin_sql);

    // 6. Register in Master DB
    $register_sql = "INSERT INTO tenants (customer_name, slug, db_name, admin_password_hash, contact_person, mobile, email, address, gst_no) 
                     VALUES ('$customer_name', '$slug', '$db_name', '$hashed_pass', 
                             '" . mysqli_real_escape_string($master_conn, $contact_person) . "', 
                             '" . mysqli_real_escape_string($master_conn, $mobile) . "', 
                             '" . mysqli_real_escape_string($master_conn, $email) . "', 
                             '" . mysqli_real_escape_string($master_conn, $address) . "', 
                             '" . mysqli_real_escape_string($master_conn, $gst_no) . "')";
    if (mysqli_query($master_conn, $register_sql)) {
        return ["success" => true, "message" => "Tenant '$customer_name' created successfully! Database: $db_name"];
    } else {
        return ["success" => false, "message" => "New database created, but Master registration failed: " . mysqli_error($master_conn)];
    }
}

