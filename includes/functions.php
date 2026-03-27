<?php
/**
 * Global functions for Gatepilot
 * NOTE: Core auth and utility functions are in includes/init.php.
 * This file contains specialized dynamic audit helpers.
 */

/**
 * Returns a connection to the centralized support database for issue reporting
 */
function getSupportDatabaseConnection() {
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
        'save_user', 'save_transporter', 'save_driver', 'save_vehicle',
        'save_employee', 'save_register', 'save_department', 'save_patrol',
        'save_material', 'save_supplier', 'save_purpose', 'save_webhook',
        'submit_inward', 'submit_outward', 'update_inward', 'update_outward',
        'update_register', 'update_ticket', 'import_employees', 'import_suppliers',
        'git_sync', 'upload_logo', 'add_type', 'update_fields',
        'user_id', 'trans_id', 'driver_id', 'vehicle_id', 'e_id', 'department_id',
        'location_id', 'material_id', 'supplier_id', 'purpose_id', 'inward_id', 'id',
        'register_type', 'reg_type',
        'password', 'password_confirm', 'new_password', 'old_password',
        'ajax', '_token', 'items', 'qr_code_data', 'qr_raw_data',
        'dynamic_data', 'commit_remarks', 'webhook_url',
    ];
    $skip_all = array_merge($system_skip, $skip);
    $parts = [];
    foreach ($post as $key => $val) {
        if (in_array($key, $skip_all, true))
            continue;
        if (is_array($val))
            continue;
        $val = trim((string)$val);
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
        'save_user', 'save_transporter', 'save_driver', 'save_vehicle',
        'save_employee', 'save_register', 'save_department', 'save_patrol',
        'save_material', 'save_supplier', 'save_purpose', 'save_webhook',
        'submit_inward', 'submit_outward', 'update_inward', 'update_outward',
        'update_register', 'update_ticket', 'import_employees', 'import_suppliers',
        'git_sync', 'upload_logo', 'add_type', 'update_fields',
        'user_id', 'trans_id', 'driver_id', 'vehicle_id', 'e_id', 'department_id',
        'location_id', 'material_id', 'supplier_id', 'purpose_id', 'inward_id', 'id',
        'register_type', 'reg_type',
        'password', 'password_confirm', 'new_password', 'old_password',
        'ajax', '_token', 'items', 'qr_code_data', 'qr_raw_data',
        'dynamic_data', 'commit_remarks', 'webhook_url',
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
        $new_val = trim((string)$new_val);
        $old_val = trim((string)($old_row[$key] ?? ''));
        if ($old_val !== $new_val) {
            $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
            $changes[] = "$label: [$old_val ➔ $new_val]";
        }
    }
    return implode("\n", $changes);
}
