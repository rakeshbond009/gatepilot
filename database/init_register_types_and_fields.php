<?php
/**
 * Initialize Register Types System
 */

function initRegisterTypesTable($conn)
{
    // 1. Create table for storing register definitions (types)
    $sql_types = "CREATE TABLE IF NOT EXISTS `register_types` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `title` VARCHAR(255) NOT NULL,
        `icon` VARCHAR(50) DEFAULT '📝',
        `color` VARCHAR(50) DEFAULT '#4f46e5',
        `fields_json` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `is_active` TINYINT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!mysqli_query($conn, $sql_types)) {
        error_log("❌ ERROR creating `register_types`: " . mysqli_error($conn));
        return false;
    }

    // 2. Add 'dynamic_data' column to `manual_registers` if not exists
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM manual_registers LIKE 'dynamic_data'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE manual_registers ADD COLUMN dynamic_data TEXT NULL AFTER remarks");
    }

    // 3. Seed initial register types from existing hardcoded config
    $initial_types = [
        'scrap_outward' => [
            'title' => 'Scrap Material Outward Register',
            'color' => '#f59e0b',
            'icon' => '♻️',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'uppercase' => true, 'required' => true],
                ['name' => 'challan_no', 'label' => 'Challan No', 'type' => 'text'],
                ['name' => 'gate_pass_no', 'label' => 'Gate Pass No', 'type' => 'text'],
                ['name' => 'out_time', 'label' => 'Out Time', 'type' => 'time', 'required' => true, 'default' => 'now'],
                ['name' => 'party_name', 'label' => 'Name of the Party', 'type' => 'text', 'required' => true],
                ['name' => 'material_desc', 'label' => 'Description of Material', 'type' => 'textarea'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
                ['name' => 'transporter_name', 'label' => 'Transporter Name', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Signature of Security', 'type' => 'text', 'placeholder' => 'Enter Security Name/ID'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'hazardous_outward' => [
            'title' => 'Hazardous Material Outward Register',
            'color' => '#ef4444',
            'icon' => '☣️',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'uppercase' => true, 'required' => true],
                ['name' => 'challan_no', 'label' => 'Challan No', 'type' => 'text'],
                ['name' => 'gate_pass_no', 'label' => 'Gate Pass No', 'type' => 'text'],
                ['name' => 'out_time', 'label' => 'Out Time', 'type' => 'time', 'required' => true, 'default' => 'now'],
                ['name' => 'party_name', 'label' => 'Name of the Party', 'type' => 'text', 'required' => true],
                ['name' => 'material_desc', 'label' => 'Description of Material', 'type' => 'textarea'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
                ['name' => 'transporter_name', 'label' => 'Transporter', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Name & Signature of Security', 'type' => 'text'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'tanker_recheck' => [
            'title' => 'Tanker Outward Recheck Register',
            'color' => '#3b82f6',
            'icon' => '🚛',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'uppercase' => true, 'required' => true],
                ['name' => 'challan_no', 'label' => 'Challan No', 'type' => 'text'],
                ['name' => 'gate_pass_no', 'label' => 'Gate Pass No', 'type' => 'text'],
                ['name' => 'out_time', 'label' => 'Out Time', 'type' => 'time', 'required' => true, 'default' => 'now'],
                ['name' => 'party_name', 'label' => 'Name of the Party', 'type' => 'text', 'required' => true],
                ['name' => 'material_desc', 'label' => 'Description of Material', 'type' => 'textarea'],
                ['name' => 'recheck', 'label' => 'Recheck Status', 'type' => 'text'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
                ['name' => 'transporter_name', 'label' => 'Transporter Name', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Security Sign', 'type' => 'text'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'returnable_outward' => [
            'title' => 'Outward Returnable Material Register',
            'color' => '#10b981',
            'icon' => '🔄',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'uppercase' => true, 'required' => true],
                ['name' => 'challan_no', 'label' => 'Challan No', 'type' => 'text'],
                ['name' => 'out_time', 'label' => 'Out Time', 'type' => 'time', 'required' => true, 'default' => 'now'],
                ['name' => 'party_name', 'label' => 'Party Name', 'type' => 'text', 'required' => true],
                ['name' => 'material_desc', 'label' => 'Description of Material', 'type' => 'textarea'],
                ['name' => 'department', 'label' => 'Department', 'type' => 'text'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Security Sign', 'type' => 'text'],
                ['name' => 'return_date_time', 'label' => 'Date & In Time (Return)', 'type' => 'datetime-local'],
                ['name' => 'received_by', 'label' => 'Received By Security', 'type' => 'text'],
                ['name' => 'handed_over_to', 'label' => 'Handed over to Name', 'type' => 'text'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'cod_register' => [
            'title' => 'COD Register',
            'color' => '#8b5cf6',
            'icon' => '💵',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'uppercase' => true],
                ['name' => 'challan_no', 'label' => 'Challan No', 'type' => 'text'],
                ['name' => 'gate_pass_no', 'label' => 'Gate Pass No', 'type' => 'text'],
                ['name' => 'out_time', 'label' => 'Out Time', 'type' => 'time', 'required' => true, 'default' => 'now'],
                ['name' => 'party_name', 'label' => 'Name of the Party', 'type' => 'text', 'required' => true],
                ['name' => 'material_desc', 'label' => 'Description of Material', 'type' => 'textarea'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
                ['name' => 'transporter_name', 'label' => 'Transporter Name', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Security Of Sign', 'type' => 'text'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'non_returnable_outward' => [
            'title' => 'Outward Non-Returnable Material Register',
            'color' => '#6b7280',
            'icon' => '📤',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'reference_no', 'label' => 'Reference No', 'type' => 'text'],
                ['name' => 'party_name', 'label' => 'Party Name', 'type' => 'text', 'required' => true],
                ['name' => 'material_desc', 'label' => 'Description of Material', 'type' => 'textarea'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
                ['name' => 'department', 'label' => 'Department', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Security Name & Sign', 'type' => 'text'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'third_party_in_out' => [
            'title' => 'Third Party Material In/Out Register',
            'color' => '#ec4899',
            'icon' => '🏭',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'default' => 'today'],
                ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'uppercase' => true],
                ['name' => 'reference_no', 'label' => 'Reference No', 'type' => 'text'],
                ['name' => 'in_time', 'label' => 'In Time', 'type' => 'time'],
                ['name' => 'party_name', 'label' => 'Party Name', 'type' => 'text'],
                ['name' => 'material_desc', 'label' => 'Description Of Material', 'type' => 'textarea'],
                ['name' => 'department', 'label' => 'Department', 'type' => 'text'],
                ['name' => 'received_quantity', 'label' => 'Received Quantity', 'type' => 'text'],
                ['name' => 'security_sign', 'label' => 'Security Sign', 'type' => 'text'],
                ['name' => 'out_time_date', 'label' => 'Out Time & Date', 'type' => 'datetime-local'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea']
            ]
        ],
        'visitor_entry' => [
            'title' => 'Visitor',
            'color' => '#10b981',
            'icon' => '👤',
            'fields' => [
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                ['name' => 'party_name', 'label' => 'Visitor Name', 'type' => 'text', 'required' => true],
                ['name' => 'remarks', 'label' => 'Purpose', 'type' => 'text']
            ]
        ]
    ];

    foreach ($initial_types as $slug => $data) {
        $check_exists = mysqli_query($conn, "SELECT id FROM register_types WHERE slug = '$slug'");
        if ($check_exists && mysqli_num_rows($check_exists) == 0) {
            $title = mysqli_real_escape_string($conn, $data['title']);
            $icon = mysqli_real_escape_string($conn, $data['icon']);
            $color = mysqli_real_escape_string($conn, $data['color']);
            $fields_json = mysqli_real_escape_string($conn, json_encode($data['fields']));

            $insert_sql = "INSERT INTO register_types (slug, title, icon, color, fields_json) 
                           VALUES ('$slug', '$title', '$icon', '$color', '$fields_json')";
            mysqli_query($conn, $insert_sql);
        }
    }
    return true;
}