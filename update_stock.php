<?php

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $table_name = $data['table_name'];
    $code = $data['code'];
    $new_stock = $data['new_stock'];

$conn = mysqli_connect("localhost:3307", "root", "", "products");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
    $stmt = $conn->prepare("UPDATE $table_name SET quantity = ? WHERE code = ?");
    $stmt->bind_param("is", $new_stock, $code); 

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
