<?php
header("Content-Type: application/json");
include("includes/db.php");

$sql = "SELECT id, title, description, image, type, created_at FROM items WHERE type IN ('Lost', 'Found') ORDER BY created_at DESC";
$result = $conn->query($sql);

$items = [];
while ($row = $result->fetch_assoc()) {

    $row['image'] = !empty($row['image']) ? "uploads/" . $row['image'] : "uploads/default-image.jpg";
    $items[] = $row;
}

echo json_encode($items);
?>
