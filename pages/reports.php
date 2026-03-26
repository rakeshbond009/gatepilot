<?php if ($page == 'reports-center'): ?>
                    <div class="container">
                        <a href="?page=dashboard" class="btn btn-secondary btn-full" style="margin-bottom: 20px;">← Back
                            to
                            Dashboard</a>

                        <div
                            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="font-size: 40px;">📊</div>
                                <div>
                                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">Reports
                                        Center
                                    </h1>
                                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9);">Access all system
                                        reports
                                        and
                                        registers
                                    </p>
                                </div>
                            </div>
                        </div>

                        <h3 style="margin-bottom: 15px; color: #374151;">Available Reports</h3>
                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">

                            <!-- Manual Registers Reports -->
                            <a href="?page=view-registers" class="card"
                                style="display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: transform 0.2s;">
                                <div
                                    style="background: #eef2ff; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                    📝</div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">Registers Report
                                    </h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280;">Scrap & Hazardous Material
                                        Outward
                                    </p>
                                </div>
                            </a>

                            <!-- Material Inward Report -->
                            <!-- Material Inward Report -->
                            <!-- Material Inward Report -->
                            <a href="?page=view-registers&type=material_inward_reg" class="card"
                                style="display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: transform 0.2s;">
                                <div
                                    style="background: #ecfdf5; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                    📦</div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">Material Inward
                                        Register
                                    </h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280;">View Incoming Material
                                        Entries
                                    </p>
                                </div>
                            </a>

                            <!-- Document Expiry Report -->
                            <a href="?page=document-expiry-alerts" class="card"
                                style="display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: transform 0.2s;">
                                <div
                                    style="background: #fef2f2; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                    ⚠️</div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">Document Alerts</h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280;">Expiring Vehicles Documents
                                    </p>
                                </div>
                            </a>

                            <!-- Truck Activity Report -->
                            <a href="?page=reports" class="card"
                                style="display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: transform 0.2s;">
                                <div
                                    style="background: #eff6ff; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                    🚚</div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">Advance Truck Report</h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280;">Inward, Outward & Checklists
                                    </p>
                                </div>
                            </a>

                            <!-- Employee Movement Report -->
                            <a href="?page=reports" class="card"
                                style="display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: transform 0.2s;">
                                <div
                                    style="background: #fdf2f8; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                    👥</div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">Employee Movement</h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280;">Staff Vehicle Entries/Exits
                                    </p>
                                </div>
                            </a>

                            <!-- Patrol Log Report -->
                            <a href="?page=reports" class="card"
                                style="display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: transform 0.2s;">
                                <div
                                    style="background: #fff7ed; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                    🔦</div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1f2937;">Security Patrols</h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280;">Watchman Scan History
                                    </p>
                                </div>
                            </a>

                        </div>
                    </div>
                    <?php
// ==================== VIEW REGISTERS ====================
elseif ($page == 'view-registers'):
    // Handle Delete
    if (isset($_POST['delete_register_id'])) {
        $del_id = intval($_POST['delete_register_id']);
        if (mysqli_query($conn, "DELETE FROM manual_registers WHERE id=$del_id")) {
            echo '<div class="alert alert-success" style="margin: 20px;">✅ Record deleted successfully</div>';
        }
    }

    // DYNAMIC CONFIG FOR REPORTS
    $register_configs = $registers_manager->getTypesMap();

    // Filter Logic
    $filter_type = isset($_GET['type']) ? $_GET['type'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

    $where_clauses = [];
    if ($filter_type) {
        $where_clauses[] = "register_type = '" . mysqli_real_escape_string($conn, $filter_type) . "'";
    }
    if ($start_date) {
        $where_clauses[] = "entry_date >= '$start_date'";
    }
    if ($end_date) {
        $where_clauses[] = "entry_date <= '$end_date'";
    }
    if ($search_query) {
        $where_clauses[] = "(vehicle_no LIKE '%$search_query%' OR party_name LIKE '%$search_query%' OR material_desc LIKE '%$search_query%' OR remarks LIKE '%$search_query%' OR challan_no LIKE '%$search_query%')";
    }

    $where_sql = $where_clauses ? "WHERE " . implode(' AND ', $where_clauses) : "";
    $limit_sql = $start_date || $search_query ? "LIMIT 500" : "LIMIT 100"; // Show more if filtering

    $query = "SELECT * FROM manual_registers $where_sql ORDER BY entry_date DESC, created_at DESC $limit_sql";
    $registers_query = mysqli_query($conn, $query);

    // Determine Columns to Show
    $columns_to_show = [];
    if ($filter_type && isset($register_configs[$filter_type])) {
        $columns_to_show = $register_configs[$filter_type]['columns'];
    }
    else {
        // Default Common Columns
        $columns_to_show = [
            'Date' => 'entry_date',
            'Type' => 'register_type',
            'Vehicle No' => 'vehicle_no',
            'Party Name' => 'party_name',
            'Material' => 'material_desc',
            'Qty' => 'quantity',
            'Ref/Challan' => 'challan_no',
            'Remarks' => 'remarks'
        ];
    }
?>
                    <div class="container">
                        <a href="?page=reports" class="btn btn-secondary btn-full" style="margin-bottom: 20px;">
                            ← Back to Reports Center
                        </a>

                        <div
                            style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.25);">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 36px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📋
                                    </div>
                                    <div>
                                        <h1 style="margin: 0; color: white; font-size: 24px; font-weight: 700;">
                                            <?php echo $filter_type && isset($register_configs[$filter_type]) ? htmlspecialchars($register_configs[$filter_type]['title']) : 'Register Reports'; ?>
                                        </h1>
                                        <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9);">
                                            View and manage entries for this register
                                        </p>
                                    </div>
                                </div>
                                <div
                                    style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; color: white; font-weight: 600; font-size: 14px;">
                                    <?php echo mysqli_num_rows($registers_query); ?> Records Found
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="card" style="margin-bottom: 20px; padding: 20px;">
                            <form method="GET"
                                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                                <input type="hidden" name="page" value="view-registers">

                                <div class="form-group" style="margin: 0;">
                                    <label
                                        style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; display: block;">Register
                                        Type</label>
                                    <select name="type" onchange="this.form.submit()"
                                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                        <?php foreach ($register_configs as $key => $config): ?>
                                            <option value="<?php echo $key; ?>" <?php echo $filter_type == $key ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($config['title']); ?>
                                            </option>
                                        <?php
    endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group" style="margin: 0;">
                                    <label
                                        style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; display: block;">Start
                                        Date</label>
                                    <input type="date" name="start_date"
                                        value="<?php echo htmlspecialchars($start_date); ?>"
                                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                </div>

                                <div class="form-group" style="margin: 0;">
                                    <label
                                        style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; display: block;">End
                                        Date</label>
                                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"
                                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                </div>

                                <div class="form-group" style="margin: 0;">
                                    <label
                                        style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; display: block;">Search</label>
                                    <input type="text" name="search" placeholder="Vehicle, Party, Material..."
                                        value="<?php echo htmlspecialchars($search_query); ?>"
                                        style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px;">
                                </div>

                                <div style="display: flex; gap: 8px; align-items: stretch; width: 100%;">
                                    <button type="submit" class="btn btn-primary"
                                        style="height: 42px; flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; font-weight: 600; font-size: 14px; padding: 0 10px;">
                                        <span>🔍</span> Filter
                                    </button>
                                    <a href="?page=view-registers" class="btn btn-secondary"
                                        style="height: 42px; flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; font-weight: 600; font-size: 14px; text-decoration: none; padding: 0 10px; background: #64748b; color: white;">
                                        <span>🔄</span> Reset
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="card" style="overflow: hidden;">
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <?php foreach ($columns_to_show as $label => $field): ?>
                                                <th
                                                    style="font-size: 13px; text-transform: uppercase; color: #64748b; font-weight: 700; white-space: nowrap;">
                                                    <?php echo $label; ?>
                                                </th>
                                            <?php
    endforeach; ?>
                                            <th
                                                style="font-size: 13px; text-transform: uppercase; color: #64748b; font-weight: 700; white-space: nowrap;">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($registers_query) == 0): ?>
                                            <tr>
                                                <td colspan="<?php echo count($columns_to_show) + 1; ?>"
                                                    style="text-align: center; padding: 40px; color: #9ca3af;">
                                                    <div style="font-size: 30px; margin-bottom: 10px;">📂</div>
                                                    No register entries found matching your criteria.
                                                </td>
                                            </tr>
                                        <?php
    else: ?>
                                            <?php while ($row = mysqli_fetch_assoc($registers_query)):
                                                $dynamic = !empty($row['dynamic_data']) ? json_decode($row['dynamic_data'], true) : [];
                                            ?>
                                                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                                    <?php foreach ($columns_to_show as $label => $field): ?>
                                                        <td
                                                            style="padding: 12px 15px; font-size: 14px; color: #374151; vertical-align: top;">
                                                            <?php
                                                            // Check main row first, then dynamic_data
                                                            $val = isset($row[$field]) ? $row[$field] : (isset($dynamic[$field]) ? $dynamic[$field] : '-');
                                                            if (empty($val)) $val = '-';
                                                            
                                                            // Custom formatting
                                                            if ($field == 'register_type') {
                                                                echo '<span class="badge badge-primary" style="font-size: 11px;">' . ucwords(str_replace('_', ' ', $val)) . '</span>';
                                                            }
                                                            elseif (strpos($field, 'date') !== false && $val && $val != '-' && strtotime($val)) {
                                                                echo date('d-M-Y', strtotime($val)) . (strpos($val, ':') ? ' <span style="color:#9ca3af; font-size:12px;">' . date('h:i A', strtotime($val)) . '</span>' : '');
                                                            }
                                                            elseif (strpos($field, 'time') !== false && $val && $val != '-' && strlen($val) <= 8) {
                                                                echo date('h:i A', strtotime($val));
                                                            }
                                                            elseif ($field == 'vehicle_no') {
                                                                echo '<strong style="color: #111827;">' . htmlspecialchars($val) . '</strong>';
                                                            }
                                                            else {
                                                                echo nl2br(htmlspecialchars($val));
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php
            endforeach; ?>
                                                    <td style="padding: 12px 15px; white-space: nowrap;">
                                                        <div style="display: flex; gap: 5px;">
                                                            <a href="?page=edit-register-entry&id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm"
                                                                style="background: #3b82f6; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none;">✏️</a>
                                                            <button type="button"
                                                                onclick="showDeleteModal('register', <?php echo $row['id']; ?>)"
                                                                class="btn btn-sm"
                                                                style="background: #ef4444; color: white; padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer;">🗑️</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php
        endwhile; ?>
                                        <?php
    endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if (hasPermission('pages.register_types')): ?>
                        <div style="margin-top: 30px; text-align: center;">
                            <a href="?page=manage-register-types" 
                                style="color: #64748b; font-size: 13px; text-decoration: none; border: 1px dashed #d1d5db; padding: 8px 15px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
                                ⚙️ Manage Register Types
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Shared Delete Modal -->
                    <div id="deleteModal"
                        style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                        <div
                            style="background-color: white; padding: 30px; border-radius: 12px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                            <h3 style="margin: 0 0 10px 0; color: #111827;">Confirm Deletion</h3>
                            <p style="margin: 0 0 20px 0; color: #6b7280; font-size: 15px;">Are you sure you want to
                                delete
                                this
                                record? This action cannot be undone.</p>

                            <form method="POST" id="deleteForm">
                                <input type="hidden" name="dummy_name" id="deleteInputId">
                                <div style="display: flex; gap: 10px; justify-content: center;">
                                    <button type="button"
                                        onclick="document.getElementById('deleteModal').style.display='none'"
                                        style="padding: 10px 20px; border-radius: 6px; border: 1px solid #d1d5db; background: white; color: #374151; font-weight: 600; cursor: pointer;">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        style="padding: 10px 20px; border-radius: 6px; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">
                                        Yes, Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                        function showDeleteModal(type, id) {
                            const modal = document.getElementById('deleteModal');
                            const form = document.getElementById('deleteForm');
                            const input = document.getElementById('deleteInputId');

                            modal.style.display = 'flex';

                            if (type === 'register') {
                                input.name = 'delete_register_id';
                            } else if (type === 'material') {
                                input.name = 'delete_material_inward_id';
                            }

                            input.value = id;
                        }

                        // Close modal when clicking outside
                        window.onclick = function (event) {
                            const modal = document.getElementById('deleteModal');
                            if (event.target == modal) {
                                modal.style.display = "none";
                            }
                        }
                    </script>
                    <?php
endif; ?>
