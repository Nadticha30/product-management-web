<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    if (!file_exists('ConnDB.php')) {
        throw new Exception("ไม่พบไฟล์ ConnDB.php ในระบบ");
    }
    require_once 'ConnDB.php';

    if (!isset($conn) || !$conn) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }

    $conn->exec("SET NAMES utf8mb4");

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
    } catch (Exception $e1) {
        $stmt = $conn->prepare("SELECT * FROM tb_products ORDER BY 1 DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    ob_end_clean();
    echo json_encode([
        'status'   => 'success',
        'success'  => true,
        'message'  => 'success',
        'data'     => $products,
        'products' => $products
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'success' => false,
        'message' => $e->getMessage(),
        'error'   => $e->getMessage(),
        'data'    => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
