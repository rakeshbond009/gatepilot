<?php
/**
 * GATEPILOT FUNCTIONS - STABLE VERSION
 */

/**
 * Filter POST data to show differences between old and new state
 */
function auditDiff(array $old_row, array $post, array $skip = [], array $labels = []): string
{
    $system_skip = [
        'save_user', 'save_tenant', 'create_tenant', 'admin_pass', 'save_transporter',
        'id', 'ajax', '_token'
    ];
    $skip_all = array_merge($system_skip, $skip);
    $changes = [];
    foreach ($post as $key => $new_val) {
        if (in_array($key, $skip_all, true)) continue;
        if (is_array($new_val)) continue;
        if (!array_key_exists($key, $old_row)) continue;
        
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
 */
function createTenant($customer_name, $slug, $admin_username, $admin_password, $contact_person = '', $mobile = '', $email = '', $address = '', $gst_no = '')
{
    // 1. Basic Validation
    if (empty($admin_username) || empty($admin_password)) {
        return ["success" => false, "message" => "Admin Username and Password are required!"];
    }

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $slug));
    
    // Database naming based on environment
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'LOCAL') {
        $db_name = "gp_" . $slug;
    } else {
        $prefix = explode('_', DB_NAME)[0]; 
        $db_name = $prefix . "_gp_" . $slug;
    }

    $master_conn = getMasterDatabaseConnection();
    if (!$master_conn) {
        return ["success" => false, "message" => "Master connection failed!"];
    }

    // 2. Check duplicate slug
    $slug_esc = mysqli_real_escape_string($master_conn, $slug);
    $check = mysqli_query($master_conn, "SELECT id FROM tenants WHERE slug = '$slug_esc'");
    if ($check && mysqli_num_rows($check) > 0) {
        return ["success" => false, "message" => "Slug '$slug' is already in use."];
    }

    // 3. Connect to server to create DB
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$conn) {
        return ["success" => false, "message" => "MySQL Connection Failed: " . mysqli_connect_error()];
    }

    // Try creating database
    $create_q = mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$create_q) {
        return ["success" => false, "message" => "Cannot create database '$db_name'. Permission Denied on Hostinger."];
    }

    if (!mysqli_select_db($conn, $db_name)) {
        return ["success" => false, "message" => "Database created, but access was denied."];
    }

    // 4. Import Schema
    $schema_file = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schema_file)) {
        return ["success" => false, "message" => "schema.sql missing!"];
    }

    $sql = file_get_contents($schema_file);
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $q) {
        if (!empty($q)) mysqli_query($conn, $q);
    }

    // 5. Initialize Admin
    $hashed_pass = password_hash($admin_password, PASSWORD_DEFAULT);
    $admin_user_esc = mysqli_real_escape_string($conn, $admin_username);
    $cust_name_esc = mysqli_real_escape_string($conn, $customer_name);
    $mob_esc = mysqli_real_escape_string($conn, $mobile);
    $em_esc = mysqli_real_escape_string($conn, $email);

    $admin_sql = "INSERT INTO user_master (username, password, full_name, role, super_admin, is_active, permissions, mobile, email) 
                  VALUES ('$admin_user_esc', '$hashed_pass', '$cust_name_esc Admin', 'admin', 0, 1, '{}', '$mob_esc', '$em_esc')";
    mysqli_query($conn, $admin_sql);

    // 6. Final Registration
    $reg_sql = "INSERT INTO tenants (customer_name, slug, db_name, admin_password_hash, contact_person, mobile, email, address, gst_no) 
                VALUES (
                    '" . mysqli_real_escape_string($master_conn, $customer_name) . "',
                    '$slug_esc',
                    '" . mysqli_real_escape_string($master_conn, $db_name) . "',
                    '$hashed_pass',
                    '" . mysqli_real_escape_string($master_conn, $contact_person) . "',
                    '" . mysqli_real_escape_string($master_conn, $mobile) . "',
                    '" . mysqli_real_escape_string($master_conn, $email) . "',
                    '" . mysqli_real_escape_string($master_conn, $address) . "',
                    '" . mysqli_real_escape_string($master_conn, $gst_no) . "'
                )";

    if (mysqli_query($master_conn, $reg_sql)) {
        return ["success" => true, "message" => "System '$customer_name' provisioned successfully!"];
    } else {
        return ["success" => false, "message" => "Provisioning partial success, but Master Registry failed."];
    }
}
