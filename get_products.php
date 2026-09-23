<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json; charset=utf-8');
require_once 'ConnDB.php';

try {
    if (!isset($conn) || !$conn) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }

    $conn->exec("SET NAMES utf8mb4");

    $stmt = $conn->prepare("
        SELECT 
            p.i_ProductID,
            p.c_ProductName,
            p.i_CategoryID,
            c.c_CategoryName,
            p.i_SupplierID,
            s.c_CompanyName AS c_SupplierName,
            p.f_Price,
            p.i_UnitsInStock,
            '' AS c_QuantityPerUnit
        FROM tb_products p
        LEFT JOIN tb_categories c ON p.i_CategoryID = c.i_CategoryID
        LEFT JOIN tb_suppliers s ON p.i_SupplierID = s.i_SupplierID
        ORDER BY p.i_ProductID DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    array_walk_recursive($products, function (&$item) {
        if (is_string($item)) {
            $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        }
    });

    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'data' => $products
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
