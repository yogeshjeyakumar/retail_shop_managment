<?php

$conn = mysqli_connect("localhost:3307", "root", "", "products");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name'], $data['phone'], $data['address'], $data['payment_option'], $data['total'], $data['products'])) {
    $name = $conn->real_escape_string($data['name']);
    $phone = $conn->real_escape_string($data['phone']);
    $address = $conn->real_escape_string($data['address']);
    $payment_option = $conn->real_escape_string($data['payment_option']);
    $total = floatval($data['total']);  
    $products = $conn->real_escape_string(implode(", ", $data['products'])); 

    $sql = "INSERT INTO `customer` (`name`, `phone`, `address`, `payment_option`, `total`, `products_names`) 
    VALUES ('$name', '$phone', '$address', '$payment_option', '$total', '$products')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Customer details saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving customer details: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}

$conn->close();
?>
