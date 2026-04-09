<?php if ($page == 'dashboard'):

    // Get statistics
    $total_inward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE DATE(inward_date) = CURDATE()"));
    $total_outward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_outward WHERE DATE(outward_date) = CURDATE()"));
    $trucks_inside = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE status = 'inside'"));
    $weekly_inward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE inward_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"));

    // Initialize loading/unloading tables if needed
    require_once dirname(__DIR__) . '/database/init_loading_unloading_tables.php';
    initLoadingUnloadingTables($conn);

    // Get loading/unloading statistics
    $check_loading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_loading_checklist'");
    $check_unloading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_unloading_checklist'");
    $total_loading_today = 0;
    $total_unloading_today = 0;
    if (mysqli_num_rows($check_loading) > 0) {
        $total_loading_today = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vehicle_loading_checklist WHERE DATE(reporting_datetime) = CURDATE()"));
    }
    if (mysqli_num_rows($check_unloading) > 0) {
        $total_unloading_today = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vehicle_unloading_checklist WHERE DATE(reporting_datetime) = CURDATE()"));
    }

    // Patrol Issues / Tickets Stats
    require_once dirname(__DIR__) . '/database/init_issue_tables.php'; // Ensure table exists
    $tickets_open = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM patrol_issues WHERE status IN ('Open','Assigned')"));
    $tickets_resolved = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM patrol_issues WHERE status IN ('Resolved','Closed') AND DATE(resolved_at) = CURDATE()"));

    // Check for documents expiring within 15 days - Group by Vehicle
    $alert_days = 15;
    $alert_threshold = date('Y-m-d', strtotime("+$alert_days days"));
    $today = date('Y-m-d');

    // Get all vehicles with expiring documents
    $vehicles_query = mysqli_query($conn, "SELECT id, vehicle_number, 
        registration_validity, 
        fitness_validity, 
        pollution_validity, 
        insurance_validity,
        permit_validity,
        DATEDIFF(COALESCE(
            LEAST(
                COALESCE(registration_validity, '9999-12-31'),
                COALESCE(fitness_validity, '9999-12-31'),
                COALESCE(pollution_validity, '9999-12-31'),
                COALESCE(insurance_validity, '9999-12-31'),
                COALESCE(permit_validity, '9999-12-31')
            ), '9999-12-31'
        ), CURDATE()) as min_days_remaining
        FROM vehicle_master 
        WHERE (
            (registration_validity IS NOT NULL AND registration_validity <= '$alert_threshold') OR
            (fitness_validity IS NOT NULL AND fitness_validity <= '$alert_threshold') OR
            (pollution_validity IS NOT NULL AND pollution_validity <= '$alert_threshold') OR
            (insurance_validity IS NOT NULL AND insurance_validity <= '$alert_threshold') OR
            (permit_validity IS NOT NULL AND permit_validity <= '$alert_threshold')
        )
        ORDER BY min_days_remaining ASC");

    $vehicles_with_alerts = [];
    while ($vehicle = mysqli_fetch_assoc($vehicles_query)) {
        $vehicle_id = $vehicle['id'];
        $vehicle_number = $vehicle['vehicle_number'];
        $docs = [];

        // Check each document type
        if ($vehicle['registration_validity'] && $vehicle['registration_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['registration_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Registration Certificate',
                'expiry_date' => $vehicle['registration_validity'],
                'days_remaining' => $days,
                'icon' => '🆔'
            ];
        }

        if ($vehicle['fitness_validity'] && $vehicle['fitness_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['fitness_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Fitness Certificate',
                'expiry_date' => $vehicle['fitness_validity'],
                'days_remaining' => $days,
                'icon' => '✅'
            ];
        }

        if ($vehicle['pollution_validity'] && $vehicle['pollution_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['pollution_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Pollution Certificate',
                'expiry_date' => $vehicle['pollution_validity'],
                'days_remaining' => $days,
                'icon' => '🌿'
            ];
        }

        if ($vehicle['insurance_validity'] && $vehicle['insurance_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['insurance_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Insurance Certificate',
                'expiry_date' => $vehicle['insurance_validity'],
                'days_remaining' => $days,
                'icon' => '🛡️'
            ];
        }

        if (isset($vehicle['permit_validity']) && $vehicle['permit_validity'] && $vehicle['permit_validity'] <= $alert_threshold) {
            $expiry_timestamp = strtotime($vehicle['permit_validity']);
            $today_timestamp = strtotime($today);
            $days = floor(($expiry_timestamp - $today_timestamp) / (60 * 60 * 24));
            $docs[] = [
                'type' => 'Permit',
                'expiry_date' => $vehicle['permit_validity'],
                'days_remaining' => $days,
                'icon' => '📄'
            ];
        }

        if (count($docs) > 0) {
            // Sort documents by urgency
            usort($docs, function ($a, $b) {
                return $a['days_remaining'] - $b['days_remaining'];
            });

            $vehicles_with_alerts[] = [
                'id' => $vehicle_id,
                'vehicle_number' => $vehicle_number,
                'link' => "?page=vehicle-detail&id=$vehicle_id",
                'docs' => $docs,
                'min_days_remaining' => $vehicle['min_days_remaining']
            ];
        }
    }

    // Get expiring driver licenses (separate section)
    $expiring_licenses = [];
    $licenses_query = mysqli_query($conn, "SELECT id, driver_name, mobile, license_number, license_expiry, DATEDIFF(license_expiry, CURDATE()) as days_remaining 
        FROM driver_master 
        WHERE license_expiry IS NOT NULL 
        AND license_expiry <= '$alert_threshold' 
        AND is_active = 1 
        ORDER BY license_expiry ASC");
    while ($row = mysqli_fetch_assoc($licenses_query)) {
        $expiring_licenses[] = [
            'id' => $row['id'],
            'driver_name' => $row['driver_name'],
            'license_number' => $row['license_number'],
            'expiry_date' => $row['license_expiry'],
            'days_remaining' => $row['days_remaining'],
            'link' => "?page=driver-detail&id={$row['id']}"
        ];
    }

    // Sort vehicles by most urgent document
    usort($vehicles_with_alerts, function ($a, $b) {
        return $a['min_days_remaining'] - $b['min_days_remaining'];
    });

    // Recent activities - combine truck_inward, loading, and unloading
    // Date/Time in Recent Activities should reflect created_at (fallback to inward_datetime if legacy schema)
    $inward_datetime_col = 'inward_datetime';
    $check_inward_created_at = mysqli_query($conn, "SHOW COLUMNS FROM truck_inward LIKE 'created_at'");
    if ($check_inward_created_at && mysqli_num_rows($check_inward_created_at) > 0) {
        $inward_datetime_col = 'created_at';
    }
    $recent_inward = mysqli_query(
        $conn,
        "SELECT 'inward' as type, id, entry_number as ref_number, vehicle_number, driver_name, {$inward_datetime_col} as datetime, status
         FROM truck_inward
         ORDER BY {$inward_datetime_col} DESC
         LIMIT 3"
    );

    // Get recent loading and unloading
    $recent_loading = [];
    $recent_unloading = [];
    $check_loading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_loading_checklist'");
    $check_unloading = mysqli_query($conn, "SHOW TABLES LIKE 'vehicle_unloading_checklist'");

    if (mysqli_num_rows($check_loading) > 0) {
        // Date/Time in Recent Activities should reflect created_at (fallback to reporting_datetime if legacy schema)
        $loading_datetime_col = 'reporting_datetime';
        $check_loading_created_at = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_loading_checklist LIKE 'created_at'");
        if ($check_loading_created_at && mysqli_num_rows($check_loading_created_at) > 0) {
            $loading_datetime_col = 'created_at';
        }
        $loading_query = mysqli_query(
            $conn,
            "SELECT 'loading' as type, id, document_id as ref_number, vehicle_registration_number as vehicle_number, driver_name, {$loading_datetime_col} as datetime, status
             FROM vehicle_loading_checklist
             ORDER BY {$loading_datetime_col} DESC
             LIMIT 3"
        );
        while ($row = mysqli_fetch_assoc($loading_query)) {
            $recent_loading[] = $row;
        }
    }

    if (mysqli_num_rows($check_unloading) > 0) {
        // Date/Time in Recent Activities should reflect created_at (fallback to reporting_datetime if legacy schema)
        $unloading_datetime_col = 'reporting_datetime';
        $check_unloading_created_at = mysqli_query($conn, "SHOW COLUMNS FROM vehicle_unloading_checklist LIKE 'created_at'");
        if ($check_unloading_created_at && mysqli_num_rows($check_unloading_created_at) > 0) {
            $unloading_datetime_col = 'created_at';
        }
        $unloading_query = mysqli_query(
            $conn,
            "SELECT 'unloading' as type, id, document_id as ref_number, vehicle_registration_number as vehicle_number, driver_name, {$unloading_datetime_col} as datetime, status
             FROM vehicle_unloading_checklist
             ORDER BY {$unloading_datetime_col} DESC
             LIMIT 3"
        );
        while ($row = mysqli_fetch_assoc($unloading_query)) {
            $recent_unloading[] = $row;
        }
    }

    // Combine all activities and sort by datetime
    $all_activities = [];
    while ($row = mysqli_fetch_assoc($recent_inward)) {
        $all_activities[] = $row;
    }
    $all_activities = array_merge($all_activities, $recent_loading, $recent_unloading);

    // Sort by datetime descending
    usort($all_activities, function ($a, $b) {
        return strtotime($b['datetime']) - strtotime($a['datetime']);
    });

    // Take top 5
    $all_activities = array_slice($all_activities, 0, 5);
    ?>
    <div class="container">
        <h1 style="margin-bottom: 20px;">Dashboard</h1>

        <?php if (count($vehicles_with_alerts) > 0 || count($expiring_licenses) > 0): ?>
            <!-- Document Expiry Alerts - Vehicle Wise -->
            <div
                style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #92400e; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                        <span>⚠️</span> Document Expiry Alerts (Expiring within
                        <?php echo $alert_days; ?> days)
                    </h3>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span
                            style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                            <?php echo count($vehicles_with_alerts); ?>
                            Vehicle
                            <?php echo count($vehicles_with_alerts) != 1 ? 's' : ''; ?>
                            <?php if (count($expiring_licenses) > 0): ?>
                                ,
                                <?php echo count($expiring_licenses); ?>
                                Driver
                                <?php echo count($expiring_licenses) != 1 ? 's' : ''; ?>
                                <?php
                            endif; ?>
                        </span>
                        <a href="?page=document-expiry-alerts"
                            style="background: #3b82f6; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; white-space: nowrap; transition: all 0.2s;"
                            onmouseover="this.style.background='#2563eb'; this.style.transform='scale(1.05)';"
                            onmouseout="this.style.background='#3b82f6'; this.style.transform='scale(1)';">
                            View All →
                        </a>
                    </div>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($vehicles_with_alerts as $vehicle):
                        $is_urgent = $vehicle['min_days_remaining'] <= 7 || $vehicle['min_days_remaining'] < 0;
                        $bg_color = $is_urgent ? '#fee2e2' : '#fef3c7';
                        $border_color = $is_urgent ? '#ef4444' : '#f59e0b';
                        ?>
                        <div
                            style="background: <?php echo $bg_color; ?>; border-left: 4px solid <?php echo $border_color; ?>; padding: 15px; margin-bottom: 12px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div
                                style="display: flex; align-items: start; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                <div style="flex: 1; min-width: 250px;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                        <span style="font-size: 24px;">🚛</span>
                                        <div>
                                            <strong style="color: #92400e; font-size: 18px;">
                                                <?php echo htmlspecialchars($vehicle['vehicle_number']); ?>
                                            </strong>
                                            <div
                                                style="color: <?php echo $is_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 600; font-size: 14px; margin-top: 2px;">
                                                <?php
                                                if ($vehicle['min_days_remaining'] < 0) {
                                                    echo '❌ Most urgent document expired ' . abs($vehicle['min_days_remaining']) . ' day' . (abs($vehicle['min_days_remaining']) > 1 ? 's' : '') . ' ago!';
                                                } elseif ($vehicle['min_days_remaining'] == 0) {
                                                    echo '⚠️ Most urgent document expires today!';
                                                } elseif ($vehicle['min_days_remaining'] <= 7) {
                                                    echo '⚠️ Most urgent document expires in ' . $vehicle['min_days_remaining'] . ' days!';
                                                } else {
                                                    echo '⚠️ Documents expiring within ' . $alert_days . ' days';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-left: 34px;">
                                        <?php foreach ($vehicle['docs'] as $doc):
                                            $doc_urgent = $doc['days_remaining'] <= 7 || $doc['days_remaining'] < 0;
                                            ?>
                                            <div
                                                style="background: <?php echo $doc_urgent ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)'; ?>; padding: 10px; margin-bottom: 8px; border-radius: 5px; border-left: 3px solid <?php echo $doc_urgent ? '#ef4444' : '#f59e0b'; ?>;">
                                                <div
                                                    style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                                    <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                                        <span style="font-size: 18px;">
                                                            <?php echo $doc['icon']; ?>
                                                        </span>
                                                        <div>
                                                            <strong style="color: #78350f; font-size: 14px;">
                                                                <?php echo htmlspecialchars($doc['type']); ?>
                                                            </strong>
                                                            <div style="color: #a16207; font-size: 12px; margin-top: 2px;">
                                                                Expires:
                                                                <?php echo date('d M, Y', strtotime($doc['expiry_date'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <div
                                                            style="color: <?php echo $doc_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 700; font-size: 14px;">
                                                            <?php
                                                            if ($doc['days_remaining'] < 0) {
                                                                echo '❌ Expired ' . abs($doc['days_remaining']) . ' day' . (abs($doc['days_remaining']) > 1 ? 's' : '') . ' ago';
                                                            } elseif ($doc['days_remaining'] == 0) {
                                                                echo '⚠️ Expires Today';
                                                            } elseif ($doc['days_remaining'] == 1) {
                                                                echo '⚠️ Expires Tomorrow';
                                                            } else {
                                                                echo $doc['days_remaining'] . ' days left';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        endforeach; ?>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <a href="<?php echo $vehicle['link']; ?>"
                                        style="background: <?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; white-space: nowrap; transition: all 0.2s;"
                                        onmouseover="this.style.background='<?php echo $is_urgent ? '#dc2626' : '#d97706'; ?>'; this.style.transform='scale(1.05)';"
                                        onmouseout="this.style.background='<?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>'; this.style.transform='scale(1)';">
                                        View Vehicle →
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach; ?>

                    <?php if (count($expiring_licenses) > 0): ?>
                        <!-- Driver License Alerts -->
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f59e0b;">
                            <h4
                                style="color: #92400e; font-size: 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <span>🪪</span> Driver License Expiry Alerts
                            </h4>
                            <?php foreach ($expiring_licenses as $license):
                                $is_urgent = $license['days_remaining'] <= 7 || $license['days_remaining'] < 0;
                                $bg_color = $is_urgent ? '#fee2e2' : '#fef3c7';
                                $border_color = $is_urgent ? '#ef4444' : '#f59e0b';
                                ?>
                                <div
                                    style="background: <?php echo $bg_color; ?>; border-left: 4px solid <?php echo $border_color; ?>; padding: 15px; margin-bottom: 12px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                    <div
                                        style="display: flex; align-items: start; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                        <div style="flex: 1; min-width: 250px;">
                                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                                <span style="font-size: 24px;">🪪</span>
                                                <div>
                                                    <strong style="color: #92400e; font-size: 18px;">
                                                        <?php echo htmlspecialchars($license['driver_name']); ?>
                                                    </strong>
                                                    <div
                                                        style="color: <?php echo $is_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 600; font-size: 14px; margin-top: 2px;">
                                                        <?php
                                                        if ($license['days_remaining'] < 0) {
                                                            echo '❌ License expired ' . abs($license['days_remaining']) . ' day' . (abs($license['days_remaining']) > 1 ? 's' : '') . ' ago!';
                                                        } elseif ($license['days_remaining'] == 0) {
                                                            echo '⚠️ License expires today!';
                                                        } elseif ($license['days_remaining'] <= 7) {
                                                            echo '⚠️ License expires in ' . $license['days_remaining'] . ' days!';
                                                        } else {
                                                            echo '⚠️ License expiring within ' . $alert_days . ' days';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="margin-left: 34px;">
                                                <div
                                                    style="background: <?php echo $is_urgent ? 'rgba(239, 68, 68, 0.1)' : 'rgba(245, 158, 11, 0.1)'; ?>; padding: 10px; margin-bottom: 8px; border-radius: 5px; border-left: 3px solid <?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>;">
                                                    <div
                                                        style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                                        <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                                            <span style="font-size: 18px;">🪪</span>
                                                            <div>
                                                                <strong style="color: #78350f; font-size: 14px;">Driver
                                                                    License</strong>
                                                                <div style="color: #a16207; font-size: 12px; margin-top: 2px;">
                                                                    License Number:
                                                                    <strong>
                                                                        <?php echo htmlspecialchars($license['license_number']); ?>
                                                                    </strong>
                                                                </div>
                                                                <div style="color: #a16207; font-size: 12px; margin-top: 2px;">
                                                                    Expires:
                                                                    <?php echo date('d M, Y', strtotime($license['expiry_date'])); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div style="text-align: right;">
                                                            <div
                                                                style="color: <?php echo $is_urgent ? '#dc2626' : '#92400e'; ?>; font-weight: 700; font-size: 14px;">
                                                                <?php
                                                                if ($license['days_remaining'] < 0) {
                                                                    echo '❌ Expired ' . abs($license['days_remaining']) . ' day' . (abs($license['days_remaining']) > 1 ? 's' : '') . ' ago';
                                                                } elseif ($license['days_remaining'] == 0) {
                                                                    echo '⚠️ Expires Today';
                                                                } elseif ($license['days_remaining'] == 1) {
                                                                    echo '⚠️ Expires Tomorrow';
                                                                } else {
                                                                    echo $license['days_remaining'] . ' days left';
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <a href="<?php echo $license['link']; ?>"
                                                style="background: <?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; white-space: nowrap; transition: all 0.2s;"
                                                onmouseover="this.style.background='<?php echo $is_urgent ? '#dc2626' : '#d97706'; ?>'; this.style.transform='scale(1.05)';"
                                                onmouseout="this.style.background='<?php echo $is_urgent ? '#ef4444' : '#f59e0b'; ?>'; this.style.transform='scale(1)';">
                                                View Driver →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            endforeach; ?>
                        </div>
                        <?php
                    endif; ?>
                </div>
            </div>
            <?php
        endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="number">
                    <?php echo $total_inward; ?>
                </div>
                <div class="label">Inward Today</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="number">
                    <?php echo $total_outward; ?>
                </div>
                <div class="label">Outward Today</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="number">
                    <?php echo $trucks_inside; ?>
                </div>
                <div class="label">Trucks Inside</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="number">
                    <?php echo $weekly_inward; ?>
                </div>
                <div class="label">This Week</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ec4899, #db2777);">
                <div class="number">
                    <?php echo $total_loading_today; ?>
                </div>
                <div class="label">Loading Today</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #14b8a6, #0d9488);">
                <div class="number">
                    <?php echo $total_unloading_today; ?>
                </div>
                <div class="label">Unloading Today</div>
            </div>
        </div>

        <!-- Quick Search Record -->
        <div class="card" style="margin-top: 20px; border-left: 5px solid #6366f1; background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);">
            <h3 style="margin: 0 0 15px 0; color: #4338ca; display: flex; align-items: center; gap: 10px;">
                <span>🔍</span> Quick Record Lookup
            </h3>
            <form action="index.php" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="hidden" name="page" value="reports">
                <div style="flex: 2; min-width: 200px;">
                    <label style="font-size: 11px; color: #6b7280; font-weight: 700; margin-bottom: 5px; display: block;">VEHICLE OR ENTRY #</label>
                    <input type="text" name="vehicle" placeholder="MH12AB1234 or Entry #" 
                        style="width: 100%; padding: 12px; border: 2px solid #e0e7ff; border-radius: 8px; font-weight: 600;" 
                        list="dashboard_vehicle_list" required>
                    <datalist id="dashboard_vehicle_list">
                        <?php
                        $search_v_list = mysqli_query($conn, "SELECT DISTINCT vehicle_number FROM truck_inward ORDER BY inward_datetime DESC LIMIT 50");
                        while($v = mysqli_fetch_assoc($search_v_list)) {
                            echo "<option value='{$v['vehicle_number']}'>";
                        }
                        ?>
                    </datalist>
                </div>
                <div style="flex: 1; min-width: 150px; align-self: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; height: 48px; background: #6366f1; border-radius: 8px; font-weight: 700; border: none; color: white; cursor: pointer;">
                        FIND ENTRY
                    </button>
                </div>
            </form>
            <p style="margin: 10px 0 0 0; font-size: 11px; color: #6b7280;">Bypasses date filters to find records from any time in history.</p>
        </div>

        <!-- Issues / Tickets Stats -->
        <?php if (hasPermission('pages.tickets')): ?>
            <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-top: -10px;">
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 15px;">
                    <div class="number" style="font-size: 24px;">
                        <?php echo $tickets_open; ?>
                    </div>
                    <div class="label" style="font-size: 13px;">Open Tickets</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669); padding: 15px;">
                    <div class="number" style="font-size: 24px;">
                        <?php echo $tickets_resolved; ?>
                    </div>
                    <div class="label" style="font-size: 13px;">Resolved Today</div>
                </div>
            </div>
            <?php
        endif; ?>

        <!-- Quick Actions -->
        <div class="actions-grid">
            <?php if (hasPermission('pages.employee_scan')): ?>
                <a href="javascript:void(0)" onclick="openEmployeeModal('scan')" class="action-card"
                    style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
                    <div class="icon" style="color: white; filter: brightness(0) invert(1);">📷</div>
                    <strong style="color: white;">Employee Scan</strong>
                    <span style="font-size: 10px; opacity: 0.8; margin-top: 5px;">Auto Entry/Exit</span>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.tickets')): ?>
                <a href="?page=tickets" class="action-card">
                    <div class="icon">🎫</div>
                    <strong>Tickets</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.inward')): ?>
                <a href="?page=inward" class="action-card">
                    <div class="icon">➕</div>
                    <strong>New Inward</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.outward')): ?>
                <a href="?page=outward" class="action-card">
                    <div class="icon">➡️</div>
                    <strong>Outward Exit</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.inside')): ?>
                <a href="?page=inside" class="action-card">
                    <div class="icon">🚛</div>
                    <strong>Trucks Inside</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.guard_patrol')): ?>
                <a href="?page=guard-patrol" class="action-card">
                    <div class="icon">👮</div>
                    <strong>Guard Patrol</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.reports')): ?>
                <a href="?page=reports" class="action-card">
                    <div class="icon">📊</div>
                    <strong>Reports</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.history')): ?>
                <a href="?page=vehicle-history" class="action-card">
                    <div class="icon">🧭</div>
                    <strong>Vehicle History</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.loading')): ?>
                <a href="?page=loading" class="action-card">
                    <div class="icon">📦</div>
                    <strong>Loading Checklist</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.unloading')): ?>
                <a href="?page=unloading" class="action-card">
                    <div class="icon">📥</div>
                    <strong>Unloading Checklist</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.management')): ?>
                <a href="?page=management" class="action-card">
                    <div class="icon">📊</div>
                    <strong>Management</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.masters')): ?>
                <a href="?page=admin" class="action-card">
                    <div class="icon">⚙️</div>
                    <strong>Masters</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.register')): ?>
                <a href="?page=register-entry" class="action-card">
                    <div class="icon">📝</div>
                    <strong>Register</strong>
                </a>
                <?php
            endif; ?>

            <?php if (hasPermission('pages.register_types')): ?>
                <a href="?page=manage-register-types" class="action-card">
                    <div class="icon">⚙️</div>
                    <strong>Register Types</strong>
                </a>
                <?php
            endif; ?>
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <h2>Recent Activities</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Ref #</th>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Date/Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_activities)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px; color: #9ca3af;">No recent
                                    activities</td>
                            </tr>
                            <?php
                        else: ?>
                            <?php foreach ($all_activities as $row):
                                $type_label = '';
                                $type_color = '';
                                $link_url = '';

                                if ($row['type'] == 'inward') {
                                    $type_label = '🚛 Inward';
                                    $type_color = '#3b82f6';
                                    $link_url = '?page=details&id=' . intval($row['id']);
                                } elseif ($row['type'] == 'loading') {
                                    $type_label = '📦 Loading';
                                    $type_color = '#ec4899';
                                    $link_url = '?page=loading-details&id=' . intval($row['id']);
                                } elseif ($row['type'] == 'unloading') {
                                    $type_label = '📥 Unloading';
                                    $type_color = '#14b8a6';
                                    $link_url = '?page=unloading-details&id=' . intval($row['id']);
                                }
                                ?>
                                <tr onclick="window.location='<?php echo $link_url; ?>'" style="cursor: pointer;">
                                    <td><span style="color: <?php echo $type_color; ?>; font-weight: 600;">
                                            <?php echo $type_label; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['ref_number'] ?? '-'); ?>
                                    </td>
                                    <td><strong>
                                            <?php echo htmlspecialchars($row['vehicle_number']); ?>
                                        </strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['driver_name'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y h:i A', strtotime($row['datetime'])); ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo ($row['status'] == 'completed' || $row['status'] == 'inside') ? 'success' : (($row['status'] == 'draft') ? 'warning' : 'secondary'); ?>">
                                            <?php echo strtoupper($row['status'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            endforeach; ?>
                            <?php
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
endif; ?>