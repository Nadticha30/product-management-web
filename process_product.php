<?php
header('Content-Type: application/json; charset=utf-8');
require_once "ConnDB.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$productName = trim($_POST['ProductName'] ?? '');
$supplierId  = trim($_POST['SupplierID'] ?? '');
$catId       = trim($_POST['CatID'] ?? '');
$unit        = trim($_POST['Unit'] ?? '');
$price       = trim($_POST['Price'] ?? '0');
$quantity    = trim($_POST['Quantity'] ?? '0');

if ($productName === '' || $supplierId === '' || $catId === '' || $unit === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง']);
    exit;
}

// รูปแบบคำสั่ง SQL สำรอง เพื่อรองรับโครงสร้างตารางทุกรูปแบบใน Railway
$sqlQueries = [
    "INSERT INTO tb_products (c_ProductName, i_SupplierID, i_CategoryID, c_Unit, f_UnitPrice, i_UnitsInStock) VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)",
    "INSERT INTO tb_products (ProductName, SupplierID, CategoryID, QuantityPerUnit, UnitPrice, UnitsInStock) VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)",
    "INSERT INTO tb_products (c_ProductName, i_SupplierID, i_CategoryID, c_Unit, f_Price, i_UnitsInStock) VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)"
];

$lastError = "";
$success = false;
$lastId = 0;

foreach ($sqlQueries as $sql) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':productName', $productName, PDO::PARAM_STR);
        $stmt->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
        $stmt->bindParam(':catId', $catId, PDO::PARAM_INT);
        $stmt->bindParam(':unit', $unit, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->execute();
        
        $lastId = $conn->lastInsertId();
        $success = true;
        break; // บันทึกสำเร็จ ให้หยุดลูปทันที
    } catch (PDOException $e) {
        $lastError = $e->getMessage();
    }
}

if ($success) {
    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $lastError]);
}
?>
