<?php if ($page == 'dashboard'):

    // Get statistics
    $total_inward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE DATE(inward_date) = CURDATE()"));
    $total_outward = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_outward WHERE DATE(outward_date) = CURDATE()"));
    $trucks_inside = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM truck_inward WHERE status != 'exited'"));
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
    // Get all activities for TODAY
    $today_date = date('Y-m-d');
    
    // Recent activities - combine truck_inward, loading, unloading and truck_outward
    // Get all activities for TODAY using reliable columns from Reports logic
    $today_date = date('Y-m-d');
    $all_activities = [];

    // 1. Inward Activities (Entries)
    $inward_query = mysqli_query($conn, 
        "SELECT 'inward' as type, id, entry_number as ref_number, vehicle_number, driver_name,
                transporter_name, from_location, to_location,
                inward_datetime as datetime, status, purpose_name as purpose
         FROM truck_inward 
         WHERE DATE(inward_datetime) = '$today_date' OR DATE(inward_date) = '$today_date'
         ORDER BY inward_datetime DESC"
    );
    if ($inward_query) {
        while ($row = mysqli_fetch_assoc($inward_query)) { $all_activities[] = $row; }
    }

    // 2. Outward Activities (using truck_outward table for robustness)
    $outward_query = mysqli_query($conn, 
        "SELECT 'outward' as type, tou.inward_id as id, tou.inward_id, ti.entry_number as ref_number, ti.vehicle_number, ti.driver_name, 
                tou.outward_datetime as datetime, 'exited' as status, ti.purpose_name as purpose,
                ti.transporter_name, ti.from_location, ti.to_location
         FROM truck_outward tou
         JOIN truck_inward ti ON tou.inward_id = ti.id
         WHERE DATE(tou.outward_datetime) = '$today_date' OR DATE(tou.outward_date) = '$today_date'
         ORDER BY tou.outward_datetime DESC"
    );
    if ($outward_query) {
        while ($row = mysqli_fetch_assoc($outward_query)) { $all_activities[] = $row; }
    }

    // 3. Loading Activities
    if (mysqli_num_rows($check_loading) > 0) {
        $loading_query = mysqli_query($conn, 
            "SELECT 'loading' as type, lc.id, lc.inward_id, lc.document_id as ref_number, lc.vehicle_registration_number as vehicle_number, lc.driver_name, 
                    lc.reporting_datetime as datetime, lc.status, 'Loading' as purpose,
                    lc.transport_company_name as transporter_name, lc.loading_location as from_location
             FROM vehicle_loading_checklist lc
             WHERE DATE(lc.reporting_datetime) = '$today_date'
             ORDER BY lc.reporting_datetime DESC"
        );
        if ($loading_query) {
            while ($row = mysqli_fetch_assoc($loading_query)) { $all_activities[] = $row; }
        }
    }

    // 4. Unloading Activities
    if (mysqli_num_rows($check_unloading) > 0) {
        $unloading_query = mysqli_query($conn, 
            "SELECT 'unloading' as type, uc.id, uc.inward_id, uc.document_id as ref_number, uc.vehicle_registration_number as vehicle_number, uc.driver_name, 
                    uc.reporting_datetime as datetime, uc.status, 'Unloading' as purpose,
                    uc.transport_company_name as transporter_name, uc.vendor_name as from_location
             FROM vehicle_unloading_checklist uc 
             WHERE DATE(uc.reporting_datetime) = '$today_date'
             ORDER BY uc.reporting_datetime DESC"
        );
        if ($unloading_query) {
            while ($row = mysqli_fetch_assoc($unloading_query)) { $all_activities[] = $row; }
        }
    }

    // Combine and sort by strictly datetime newest first
    usort($all_activities, function ($a, $b) {
        return strtotime($b['datetime']) - strtotime($a['datetime']);
    });

    // Show top 15 for "Today" on dashboard
    $all_activities = array_slice($all_activities, 0, 15);

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
                    <strong>Reg Types</strong>
                </a>
            <?php endif; ?>
        </div> <!-- End of action-grid -->

        <!-- Today's Activity Section -->
        <div class="card" style="margin-top: 25px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding: 5px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
                        📅
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #1e293b;">Today's Activity</h2>
                        <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Chronological timeline of gate operations</p>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="location.reload()" style="background: #f8fafc; border: 1px solid #e2e8f0; width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="Refresh Activity">🔄</button>
                    <a href="?page=reports" style="font-size: 12px; font-weight: 700; color: #6366f1; text-decoration: none; background: #f5f3ff; padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 6px;">
                        Reports ➔
                    </a>
                </div>
            </div>

            <?php if (empty($all_activities)): ?>
                <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 20px; border: 2px dashed #e2e8f0; margin: 10px;">
                    <div style="font-size: 40px; margin-bottom: 15px; filter: grayscale(1); opacity: 0.5;">🚛</div>
                    <h3 style="margin: 0; color: #475569; font-weight: 700;">No Activity Recorded</h3>
                    <p style="margin: 5px 0 0; color: #94a3b8; font-size: 14px;">Incoming and outgoing vehicle actions for today will appear here.</p>
                </div>
            <?php else: ?>
                <!-- ACTIVITY TIMELINE LIST -->
                <div style="position: relative; padding: 30px 5px 30px 45px; background: #f1f5f9; border-radius: 24px; border: 1px solid #e2e8f0; margin: 15px; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);">
                    <!-- The vertical progress line -->
                    <div style="position: absolute; left: 19px; top: 30px; bottom: 30px; width: 3px; background: linear-gradient(to bottom, #6366f1 0%, #a855f7 100%); border-radius: 10px; opacity: 0.15;"></div>

                    <?php foreach ($all_activities as $act): 
                        $type = $act['type'];
                        $label = strtoupper($act['type']);
                        $color = '#3b82f6'; // Default
                        $bg_soft = '#eff6ff';
                        if ($type == 'loading') { $color = '#8b5cf6'; $bg_soft = '#f5f3ff'; $icon = '📦'; }
                        elseif ($type == 'unloading') { $color = '#f59e0b'; $bg_soft = '#fffbeb'; $icon = '📥'; }
                        elseif ($type == 'outward') { $color = '#10b981'; $bg_soft = '#ecfdf5'; $icon = '📤'; }
                        else { $icon = '🚛'; }

                        $time = date('h:i A', strtotime($act['datetime']));
                        $badge_class = 'primary';
                        if ($act['status'] == 'exited' || $act['status'] == 'completed' || $act['status'] == 'Completed') $badge_class = 'success';
                        
                        $link_url = "";
                        if ($type == 'inward') $link_url = "?page=inward-details&id=" . $act['id'];
                        elseif ($type == 'outward') $link_url = "?page=outward-details&id=" . ($act['inward_id'] ?? $act['id']);
                        elseif ($type == 'loading') $link_url = "?page=loading-details&id=" . $act['id'];
                        elseif ($type == 'unloading') $link_url = "?page=unloading-details&id=" . $act['id'];
                    ?>
                        <div style="position: relative; margin-bottom: 28px;">
                            <!-- Marker -->
                            <div style="position: absolute; left: -34px; top: 14px; width: 22px; height: 22px; border-radius: 50%; background: #fff; border: 4px solid <?php echo $color; ?>; z-index: 2; box-shadow: 0 4px 10px <?php echo $bg_soft; ?>;"></div>
                            
                            <div class="activity-timeline-card" onclick="window.location='<?php echo $link_url; ?>'" style="background: white; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.03), 0 4px 6px -4px rgba(0,0,0,0.02); cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 10px; font-weight: 800; color: <?php echo $color; ?>; background: <?php echo $bg_soft; ?>; padding: 3px 10px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo $label; ?></span>
                                        <span style="font-size: 14px; font-weight: 800; color: #1e293b;"><?php echo $act['vehicle_number']; ?></span>
                                    </div>
                                    <span style="font-size: 11px; font-weight: 800; color: #64748b; background: #f8fafc; padding: 3px 10px; border-radius: 8px; border: 1px solid #f1f5f9;"><?php echo $time; ?></span>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; margin-bottom: 18px;">
                                    <div>
                                        <p style="margin: 0; font-size: 9px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">Driver</p>
                                        <p style="margin: 2px 0 0; font-size: 12px; color: #334155; font-weight: 700;"><?php echo htmlspecialchars($act['driver_name'] ?: 'N/A'); ?></p>
                                    </div>
                                    <div>
                                        <p style="margin: 0; font-size: 9px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">Transporter</p>
                                        <p style="margin: 2px 0 0; font-size: 12px; color: #334155; font-weight: 700;"><?php echo htmlspecialchars($act['transporter_name'] ?: 'N/A'); ?></p>
                                    </div>
                                    <div>
                                        <p style="margin: 0; font-size: 9px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">Purpose / Ref</p>
                                        <p style="margin: 2px 0 0; font-size: 12px; color: #334155; font-weight: 700;"><?php echo htmlspecialchars($act['purpose'] ?: '-'); ?> <small style="color:#94a3b8; font-weight:500;">(<?php echo $act['ref_number']; ?>)</small></p>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                                    <span class="badge badge-<?php echo $badge_class; ?>" style="font-size: 10px; padding: 5px 12px; font-weight: 800; border-radius: 8px;"><?php echo strtoupper($act['status']); ?></span>
                                    <button onclick="event.stopPropagation(); showTruckTimeline(<?php echo ($act['inward_id'] ?? ($type == 'inward' ? $act['id'] : 0)); ?>, '<?php echo $act['vehicle_number']; ?>')" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #6366f1; padding: 8px 16px; border-radius: 12px; font-size: 10px; font-weight: 800; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                        VIEW JOURNEY ⏱
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JOURNEY LOG MODAL (Timeline) -->
    <div id="truckTimelineModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px);">
        <div style="background-color: #f1f5f9; margin: 40px auto; padding: 0; border: none; width: 92%; max-width: 550px; border-radius: 28px; overflow: hidden; box-shadow: 0 30px 60px -12px rgba(0,0,0,0.6); animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 id="truck_timeline_vehicle" style="margin: 0; font-size: 20px; font-weight: 900; letter-spacing: -0.5px;">Vehicle Journey</h3>
                    <p style="margin: 4px 0 0; font-size: 12px; opacity: 0.7; font-weight: 500;">Complete operation sequence logs</p>
                </div>
                <span onclick="document.getElementById('truckTimelineModal').style.display='none'" style="font-size: 20px; font-weight: bold; cursor: pointer; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.08); border-radius: 12px; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">&times;</span>
            </div>
            
            <div id="truck_timeline_content" style="padding: 30px 25px 40px 50px; position: relative; max-height: 70vh; overflow-y: auto; background: #f1f5f9;">
                <!-- Timeline items will be injected here -->
            </div>

            <div style="padding: 20px; background: white; border-top: 1px solid #e2e8f0; display: flex; gap: 10px;">
                <button onclick="document.getElementById('truckTimelineModal').style.display='none'" style="flex: 1; padding: 15px; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius: 16px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">Close Journey Log</button>
            </div>
        </div>
    </div>

    <script>
        function showTruckTimeline(inwardId, vehicleNo) {
            if (!inwardId || inwardId == 0) {
                Swal.fire('Info', 'Full journey history requires a valid Inward Entry.', 'info');
                return;
            }

            const modal = document.getElementById('truckTimelineModal');
            const content = document.getElementById('truck_timeline_content');
            document.getElementById('truck_timeline_vehicle').textContent = "Journey: " + vehicleNo;

            content.innerHTML = '<div style="padding: 50px; text-align: center; color: #64748b;"><div class="spinner" style="margin:0 auto 20px;"></div><p style="font-weight:700; font-size:14px; color:#1e293b;">Loading Journey Data...</p></div>';
            modal.style.display = 'block';

            fetch(`?page=get-truck-timeline&inward_id=${inwardId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        content.innerHTML = `<div style="padding: 40px; text-align: center; color: #ef4444; background: white; border-radius: 20px; margin: 10px; border: 1px solid #fee2e2;">
                            <div style="font-size:40px; margin-bottom:15px;">⚠️</div>
                            <strong style="font-size:16px;">Journey Not Found</strong>
                            <p style="font-size:13px; margin-top:8px; opacity:0.7;">${data.message}</p>
                        </div>`;
                        return;
                    }

                    // Clear previous and add vertical line
                    content.innerHTML = '<div style="position: absolute; left: 19px; top: 10px; bottom: 10px; width: 3px; background: linear-gradient(to bottom, #6366f1, #a855f7); border-radius: 10px; opacity: 0.2;"></div>';

                    if (!data.timeline || data.timeline.length === 0) {
                        content.innerHTML += '<div style="text-align: center; color: #94a3b8; padding: 40px; background: white; border-radius: 20px; margin: 10px; border: 1px dashed #e2e8f0;">No operation logs found for this journey yet.</div>';
                        return;
                    }

                    data.timeline.forEach((step, index) => {
                        const item = document.createElement('div');
                        item.style.position = 'relative';
                        item.style.marginBottom = '28px';

                        const marker = document.createElement('div');
                        marker.style.position = 'absolute';
                        marker.style.left = '-34px';
                        marker.style.top = '5px';
                        marker.style.width = '20px';
                        marker.style.height = '20px';
                        marker.style.borderRadius = '50%';
                        marker.style.background = '#fff';
                        marker.style.border = '4px solid #6366f1';
                        marker.style.zIndex = '2';
                        marker.style.boxShadow = '0 0 0 5px rgba(99, 102, 241, 0.1)';

                        const timeObj = new Date(step.time);
                        const timeStr = isNaN(timeObj.getTime()) ? (step.time || 'N/A') : timeObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                        const dateStr = isNaN(timeObj.getTime()) ? '' : timeObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });

                        item.innerHTML = `
                            <div style="background: white; padding: 18px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default;" onmouseover="this.style.transform='translateX(6px)'; this.style.borderColor='#6366f1';" onmouseout="this.style.transform='translateX(0)'; this.style.borderColor='#e2e8f0';">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 18px;">${step.icon || '📍'}</span>
                                        <span style="font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">${step.type}</span>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 11px; font-weight: 800; color: #6366f1;">${timeStr}</div>
                                        <div style="font-size: 9px; color: #94a3b8; font-weight: 700;">${dateStr}</div>
                                    </div>
                                </div>
                                <div style="font-size: 12px; color: #334155; line-height: 1.6; margin-bottom: 12px; background: #f8fafc; padding: 10px 14px; border-radius: 12px; border-left: 4px solid #e2e8f0; font-weight: 500;">${step.details}</div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f8fafc; padding-top: 10px;">
                                    <span style="font-size: 10px; font-weight: 800; background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 8px; border: 1px solid #e2e8f0;">👤 ${step.by || 'Internal'}</span>
                                    <span style="font-size: 10px; font-weight: 900; color: #059669; background: #ecfdf5; padding: 3px 10px; border-radius: 8px; text-transform: uppercase; border: 1px solid #d1fae5;">${step.status || 'Verified'}</span>
                                </div>
                            </div>
                        `;
                        item.appendChild(marker);
                        content.appendChild(item);
                    });
                })
                .catch(err => {
                    content.innerHTML = `<div style="padding: 40px; text-align: center; color: #ef4444; background: white; border-radius: 20px;">
                        <div style="font-size:40px; margin-bottom:15px;">🔌</div>
                        <strong>Network Disconnected</strong>
                        <p style="font-size:13px; margin-top:8px; opacity:0.7;">Unable to reach server. Please check your connection.</p>
                    </div>`;
                });
        }
    </script>

    <style>
        .activity-timeline-card:hover { border-color: #6366f1 !important; transform: translateY(-4px); box-shadow: 0 12px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.05); }
        .spinner { width: 35px; height: 35px; border: 4px solid #f3f3f3; border-radius: 50%; border-top: 4px solid #6366f1; animation: spin 0.8s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
<?php endif; ?>