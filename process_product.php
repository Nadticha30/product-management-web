<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if (file_exists("inc/ConnDB.php")) {
    require_once "inc/ConnDB.php";
} else {
    require_once "ConnDB.php";
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$productName = trim($_POST['ProductName'] ?? '');
$supplierId  = trim($_POST['SupplierID'] ?? '');
$catId       = trim($_POST['CatID'] ?? '');
$unit        = trim($_POST['Unit'] ?? '');
$price       = trim($_POST['Price'] ?? '');
$quantity    = trim($_POST['Quantity'] ?? '0');
$productID   = $_POST['ProductID'] ?? '';
$action      = $_POST['action'] ?? '';

if ($productName === '' || $supplierId === '' || $catId === '' || $unit === '' || $price === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($action === 'update' && !empty($productID)) {
        $sql = "UPDATE tb_products 
                SET c_ProductName = :productName, 
                    i_SupplierID = :supplierId, 
                    i_CategoryID = :catId, 
                    c_Unit = :unit, 
                    f_Price = :price,
                    i_UnitsInStock = :quantity 
                WHERE i_ProductID = :productID";
                    
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':productName', $productName, PDO::PARAM_STR);
        $stmt->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
        $stmt->bindParam(':catId', $catId, PDO::PARAM_INT);
        $stmt->bindParam(':unit', $unit, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':productID', $productID, PDO::PARAM_INT);
        $stmt->execute();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'id' => $productID,
            'message' => 'อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว'
        ], JSON_UNESCAPED_UNICODE);

    } else {
        $sql = "INSERT INTO tb_products (c_ProductName, i_SupplierID, i_CategoryID, c_Unit, f_Price, i_UnitsInStock)
                VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':productName', $productName, PDO::PARAM_STR);
        $stmt->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
        $stmt->bindParam(':catId', $catId, PDO::PARAM_INT);
        $stmt->bindParam(':unit', $unit, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->execute();

        $lastId = $conn->lastInsertId();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'id' => $lastId,
            'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดจากฐานข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
