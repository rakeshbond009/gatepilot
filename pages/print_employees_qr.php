<?php
// pages/print_employees_qr.php
if (!isLoggedIn()) {
    header("Location: ?page=login");
    exit;
}

// Fetch all active employees
$employees = mysqli_query($conn, "SELECT * FROM employee_master ORDER BY employee_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee QR Codes - Bulk Print</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f1f5f9; }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 15mm;
            box-sizing: border-box;
            page-break-after: always;
        }
        .qr-card {
            border: 2px dashed #e2e8f0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 12px;
        }
        .qr-image { margin-bottom: 15px; }
        .emp-name { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; text-transform: uppercase; }
        .emp-id { font-size: 14px; color: #64748b; margin: 5px 0; }
        .emp-vehicle { font-size: 16px; font-weight: 600; color: #4f46e5; margin: 5px 0; background: #eef2ff; padding: 4px 12px; border-radius: 6px; }
        .dept { font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .no-print-header {
            background: #1e293b;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { margin: 0; box-shadow: none; border: none; }
            .no-print-header { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print-header">
    <div>
        <h2 style="margin:0; font-size: 18px;">🖨️ Bulk QR Code Print (4 per Page)</h2>
        <p style="margin: 3px 0 0 0; font-size: 12px; color: #94a3b8;">Format: A4 Paper, 2 Columns, 2 Rows</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" style="background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">Print Now</button>
        <button onclick="window.close()" style="background: #475569; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">Close Window</button>
    </div>
</div>

<?php
$count = 0;
$total = mysqli_num_rows($employees);
while ($emp = mysqli_fetch_assoc($employees)) {
    if ($count % 4 == 0) echo '<div class="page">';
    
    $qr_data = json_encode([
        'type' => 'employee',
        'id' => $emp['employee_id'],
        'name' => $emp['employee_name'],
        'vehicle' => $emp['vehicle_number']
    ]);
    ?>
    <div class="qr-card">
        <div class="dept"><?php echo htmlspecialchars($emp['department']); ?></div>
        <div id="qr_<?php echo $emp['id']; ?>" class="qr-image"></div>
        <p class="emp-name"><?php echo htmlspecialchars($emp['employee_name']); ?></p>
        <p class="emp-id">ID: <?php echo htmlspecialchars($emp['employee_id']); ?></p>
        <div class="emp-vehicle"><?php echo htmlspecialchars($emp['vehicle_number']); ?></div>
        
        <script>
            new QRCode(document.getElementById("qr_<?php echo $emp['id']; ?>"), {
                text: <?php echo json_encode($qr_data); ?>,
                width: 160,
                height: 160,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        </script>
    </div>
    <?php
    $count++;
    if ($count % 4 == 0 || $count == $total) echo '</div>';
}
?>

</body>
</html>
