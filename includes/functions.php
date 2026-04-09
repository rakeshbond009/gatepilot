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

        // Normalize Datetime comparison
        if (strpos($key, 'datetime') !== false || strpos($key, 'date') !== false) {
            if (strtotime($old_val) === strtotime($new_val)) continue;
        }

        if ($old_val !== $new_val) {
            $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
            $changes[] = "$label: [$old_val ➔ $new_val]";
        }
    }
    return implode("\n", $changes);
}

/**
 * CENTRAL PERMISSION REGISTRY
 * Defines all available pages, master categories, and actions.
 * If $is_tenant_admin is true, it excludes Super-Admin specialized pages.
 */
function getAppDefaultPermissions($is_tenant_admin = true) {
    $perms = [
        'pages' => [
            'dashboard' => true, 'inward' => true, 'outward' => true, 'reports' => true,
            'loading' => true, 'unloading' => true, 'masters' => true, 'management' => true,
            'tickets' => true, 'permissions' => true, 'guard_patrol' => true, 'inside' => true,
            'history' => true, 'register' => true, 'register_types' => true, 'audit_logs' => true,
            'employee_scan' => true, 'app_issues' => true
        ],
        'masters' => [
            'transporters' => true, 'drivers' => true, 'vehicles' => true, 'purposes' => true,
            'employees' => true, 'departments' => true, 'patrol' => true, 'materials' => true,
            'suppliers' => true, 'users' => true
        ],
        'actions' => [
            'edit_record' => true, 'delete_record' => true, 'view_buttons' => true
        ]
    ];

    // SECURE: Enforce exclusions for non-Super Admins
    if ($is_tenant_admin) {
        $excl = ['multi-tenancy', 'cloud_config', 'db_manager', 'settings'];
        foreach ($excl as $e) {
            unset($perms['pages'][$e]);
        }
    }

    return $perms;
}

/**
 * Provision a new tenant database and register it in the master table
 * Restricted to Super Admins only
 */
function createTenant($customer_name, $slug, $admin_username, $admin_password, $contact_person = '', $mobile = '', $email = '', $address = '', $gst_no = '', $db_host = 'localhost', $db_user = '', $db_pass = '', $custom_db_name = '', $user_limit = 10)
{
    // 1. Validate Input
    if (empty($admin_username) || empty($admin_password)) {
        return ["success" => false, "message" => "❌ Admin Username and Password are mandatory fields!"];
    }

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $slug));
    $db_host = !empty($db_host) ? $db_host : (defined('DB_HOST') ? DB_HOST : 'localhost');

    // Determine target database name
    if (!empty($custom_db_name)) {
        $db_name = $custom_db_name;
    } else {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'LOCAL') {
            $db_name = "gp_" . $slug; // Local prefix
        } else {
            // PRODUCTION: Use Hostinger account prefix if available
            $prefix = explode('_', DB_NAME)[0]; // Extract account ID (e.g. u875321134)
            $db_name = $prefix . "_gp_" . $slug;
        }
    }

    $master_conn = getMasterDatabaseConnection();

    // Check if slug exists
    $check = mysqli_query($master_conn, "SELECT id FROM tenants WHERE slug = '$slug'");
    if (mysqli_num_rows($check) > 0) {
        return ["success" => false, "message" => "This slug '$slug' is already taken!"];
    }

    // 3. Create or Link New Database
    // Try custom credentials if provided, otherwise fallback to master credentials
    $conn_user = !empty($db_user) ? $db_user : DB_USER;
    $conn_pass = (!empty($db_user) || !empty($db_pass)) ? $db_pass : DB_PASS;

    // Connect to Host without selecting a DB first to check for creation
    $conn = @mysqli_connect($db_host, $conn_user, $conn_pass);
    if (!$conn) {
        return ["success" => false, "message" => "Database server connection failed: " . mysqli_connect_error()];
    }

    // Attempt creation (May fail on shared hosting like Hostinger - that is OK)
    $create_q = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    @mysqli_query($conn, $create_q);

    // Now try to select the database
    if (!@mysqli_select_db($conn, $db_name)) {
        $err = mysqli_error($conn);
        return [
            "success" => false, 
            "message" => "Could not access database '$db_name'. 
                          If you are on Hostinger, you MUST first create the database & user manually in hPanel before linking it here. 
                          Error: $err"
        ];
    }

    // 4. Import Schema from database/schema.sql
    $schema_file = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schema_file)) {
        return ["success" => false, "message" => "Schema file not found at $schema_file"];
    }

    // Check if the database is already populated
    $check_tables = mysqli_query($conn, "SHOW TABLES LIKE 'user_master'");
    if (mysqli_num_rows($check_tables) == 0) {
        $sql = file_get_contents($schema_file);

        // Split and run each query
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        $import_errors = [];
        foreach ($queries as $query) {
            if (!empty($query)) {
                if (!mysqli_query($conn, $query)) {
                    $import_errors[] = mysqli_error($conn);
                }
            }
        }

        if (!empty($import_errors)) {
            error_log("Tenant Schema Warnings for $db_name: " . implode(", ", array_slice($import_errors, 0, 3)));
        }
    } else {
        error_log("Skipping schema import for $db_name - tables already exist.");
    }

    // 5. Create Initial Admin User in the NEW database
    $hashed_pass = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Default Dynamic Permissions for the primary Admin
    $perms_array = getAppDefaultPermissions(true);
    $full_perms = mysqli_real_escape_string($conn, json_encode($perms_array));

    $esc_mobile = mysqli_real_escape_string($conn, $mobile);
    $esc_email = mysqli_real_escape_string($conn, $email);

    $admin_sql = "INSERT INTO user_master (username, password, full_name, role, super_admin, is_active, permissions, mobile, email) 
                  VALUES ('$admin_username', '$hashed_pass', '$customer_name Admin', 'admin', 0, 1, '$full_perms', '$esc_mobile', '$esc_email')
                  ON DUPLICATE KEY UPDATE 
                  password = VALUES(password), 
                  full_name = VALUES(full_name), 
                  role = 'admin',
                  is_active = 1,
                  permissions = VALUES(permissions),
                  mobile = VALUES(mobile),
                  email = VALUES(email)";
    mysqli_query($conn, $admin_sql);

    // 6. Register in Master DB
    $register_sql = "INSERT INTO tenants (customer_name, slug, db_host, db_name, db_user, db_pass, admin_username, admin_password_hash, user_limit, contact_person, mobile, email, address, gst_no) 
                     VALUES ('$customer_name', '$slug', 
                             '" . mysqli_real_escape_string($master_conn, $db_host) . "',
                             '$db_name', 
                             '" . mysqli_real_escape_string($master_conn, $db_user) . "', 
                             '" . mysqli_real_escape_string($master_conn, $db_pass) . "', 
                             '" . mysqli_real_escape_string($master_conn, $admin_username) . "', 
                             '$hashed_pass', 
                             " . (int)$user_limit . ", 
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

