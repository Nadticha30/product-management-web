<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'ConnDB.php';

try {
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

    ob_end_clean();
    echo json_encode($products);
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
