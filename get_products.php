<?php
header('Content-Type: application/json; charset=utf-8');
require_once "ConnDB.php";

try {
    $sql = "SELECT * FROM tb_products ORDER BY i_ProductID DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products, JSON_UNESCAPED_UNICODE);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
