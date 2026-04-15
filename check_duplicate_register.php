<?php
require_once 'includes/init.php';

header('Content-Type: application/json');

if (!isset($_GET['title'])) {
    echo json_encode(['exists' => false]);
    exit;
}

$title = mysqli_real_escape_string($conn, $_GET['title']);
$query = "SELECT id FROM register_types WHERE title = '$title' LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}
?>
