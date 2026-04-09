<?php
/**
 * DynamicRegisters Manager
 * Handles fetching definitions and saving entries for the Manual Register system.
 */

class DynamicRegisters {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    /**
     * Get register types from the database
     */
    public function getAllTypes($only_active = true, $bypass_tenant_filter = false) {
        $types = [];
        $tenant_slug = $_SESSION['tenant_slug'] ?? 'global';
        $is_super_admin = ($_SESSION['super_admin'] ?? 0) == 1;
        
        $where = $only_active ? "WHERE is_active = 1" : "WHERE 1=1";
        
        $tenant_filter = "";
        if (!$bypass_tenant_filter && !$is_super_admin) {
            $tenant_filter = " AND (tenant_slug = 'global' OR tenant_slug = '$tenant_slug' OR tenant_slug IS NULL)";
        }
        
        // Filter by tenant: either global or specific to this tenant
        $sql = "SELECT * FROM register_types 
                $where 
                $tenant_filter
                ORDER BY title ASC";
                
        $res = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $row['fields'] = json_decode($row['fields_json'], true) ?: [];
            $types[$row['slug']] = $row;
        }
        return $types;
    }

    /**
     * Get a specific register type by slug
     */
    public function getType($slug) {
        $slug = mysqli_real_escape_string($this->conn, $slug);
        $res = mysqli_query($this->conn, "SELECT * FROM register_types WHERE slug = '$slug' LIMIT 1");
        if ($row = mysqli_fetch_assoc($res)) {
            $row['fields'] = json_decode($row['fields_json'], true);
            return $row;
        }
        return null;
    }

    /**
     * Get types in a format suitable for the Reports/View Register page
     */
    public function getTypesMap() {
        $map = [];
        $types = $this->getAllTypes();
        foreach ($types as $slug => $type) {
            $cols = [];
            
            // Add entry date by default
            $cols['Date'] = 'entry_date';
            
            // Map common fields to columns
            foreach ($type['fields'] as $field) {
                // Limit to 10 columns for cleaner view
                if (count($cols) > 10) break;
                
                $label = $field['label'] ?? $field['name'];
                $name = $field['name'];
                
                // Avoid redundant date col
                if ($name == 'date') continue;
                
                $cols[$label] = $name;
            }
            
            $map[$slug] = [
                'title' => $type['title'],
                'color' => $type['color'] ?? '#4f46e5',
                'columns' => $cols
            ];
        }
        return $map;
    }

    /**
     * Save a new register entry
     */
    public function saveEntry($type_slug, $post_data, $user_id) {
        $type = $this->getType($type_slug);
        if (!$type) return ['status' => 'error', 'message' => 'Invalid register type'];

        // Map standard fields for DB columns
        $entry_date = $post_data['date'] ?? date('Y-m-d');
        $vehicle_no = $post_data['vehicle_no'] ?? '';
        $challan_no = $post_data['challan_no'] ?? '';
        $gate_pass_no = $post_data['gate_pass_no'] ?? '';
        $time_in = $post_data['in_time'] ?? null;
        $time_out = $post_data['out_time'] ?? null;
        $party_name = $post_data['party_name'] ?? '';
        $supp_code = $post_data['supp_code'] ?? '';
        $material_desc = $post_data['material_desc'] ?? '';
        $material_code = $post_data['material_code'] ?? '';
        $category = $post_data['category'] ?? '';
        $quantity = $post_data['quantity'] ?? '';
        $pack_size = $post_data['pack_size'] ?? '';
        $transporter_name = $post_data['transporter_name'] ?? '';
        $security_sign = $post_data['security_sign'] ?? '';
        $remarks = $post_data['remarks'] ?? '';
        $department = $post_data['department'] ?? '';
        $recheck_status = $post_data['recheck'] ?? '';
        $return_date_time = (!empty($post_data['return_date_time'])) ? $post_data['return_date_time'] : null;
        $received_by = $post_data['received_by'] ?? '';
        $handed_over_to = $post_data['handed_over_to'] ?? '';
        $reference_no = $post_data['reference_no'] ?? '';
        $received_quantity = $post_data['received_quantity'] ?? '';
        $out_time_date = (!empty($post_data['out_time_date'])) ? $post_data['out_time_date'] : null;

        // Collect extra dynamic data
        $dynamic_data = [];
        $standard_keys = ['date', 'vehicle_no', 'challan_no', 'gate_pass_no', 'in_time', 'out_time', 'party_name', 'supp_code', 'material_desc', 'material_code', 'category', 'quantity', 'pack_size', 'transporter_name', 'security_sign', 'remarks', 'department', 'recheck', 'return_date_time', 'received_by', 'handed_over_to', 'reference_no', 'received_quantity', 'out_time_date', 'register_type', 'save_register'];
        
        foreach ($post_data as $key => $val) {
            if (!in_array($key, $standard_keys)) {
                $dynamic_data[$key] = $val;
            }
        }

        $dynamic_json = mysqli_real_escape_string($this->conn, json_encode($dynamic_data));
        
        $sql = "INSERT INTO manual_registers (
            register_type, entry_date, vehicle_no, challan_no, gate_pass_no,
            time_in, time_out, party_name, supp_code, material_desc,
            material_code, category, quantity, pack_size, transporter_name,
            security_sign, remarks, department, recheck_status, return_date_time,
            received_by, handed_over_to, reference_no, received_quantity, out_time_date,
            dynamic_data
        ) VALUES (
            '$type_slug', '$entry_date', '$vehicle_no', '$challan_no', '$gate_pass_no',
            " . ($time_in ? "'$time_in'" : "NULL") . ", " . ($time_out ? "'$time_out'" : "NULL") . ", 
            '$party_name', '$supp_code', '$material_desc', '$material_code', '$category',
            '$quantity', '$pack_size', '$transporter_name', '$security_sign', '$remarks', '$department',
            '$recheck_status', " . ($return_date_time ? "'$return_date_time'" : "NULL") . ",
            '$received_by', '$handed_over_to', '$reference_no', '$received_quantity', 
            " . ($out_time_date ? "'$out_time_date'" : "NULL") . ", '$dynamic_json'
        )";

        if (mysqli_query($this->conn, $sql)) {
            return ['status' => 'success', 'id' => mysqli_insert_id($this->conn)];
        } else {
            return ['status' => 'error', 'message' => mysqli_error($this->conn)];
        }
    }
}
