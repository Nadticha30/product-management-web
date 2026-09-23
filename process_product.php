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

if ($productName === '' \vert{}\vert{}$supplierId === '' || $catId === '' \vert{}\vert{}$unit === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง']);
    exit;
}

try {
    // ตรวจสอบคอลัมน์จริงที่มีในฐานข้อมูล Railway อัตโนมัติ
    $stmtCols =$conn->query("SHOW COLUMNS FROM tb_products");
    $existingCols =$stmtCols->fetchAll(PDO::FETCH_COLUMN);

    // หาชื่อคอลัมน์ราคา
    $priceCol = 'UnitPrice';
    if (in_array('f_Price', $existingCols))$priceCol = 'f_Price';
    elseif (in_array('f_UnitPrice', $existingCols))$priceCol = 'f_UnitPrice';
    elseif (in_array('UnitPrice', $existingCols))$priceCol = 'UnitPrice';
    elseif (in_array('Price', $existingCols))$priceCol = 'Price';

    // หาชื่อคอลัมน์จำนวนคงเหลือ
    $stockCol = 'UnitsInStock';
    if (in_array('i_UnitsInStock', $existingCols))$stockCol = 'i_UnitsInStock';
    elseif (in_array('UnitsInStock', $existingCols))$stockCol = 'UnitsInStock';
    elseif (in_array('i_Quantity', $existingCols))$stockCol = 'i_Quantity';
    elseif (in_array('Quantity', $existingCols))$stockCol = 'Quantity';

    // หาชื่อคอลัมน์อื่นๆ
    $nameCol = in_array('c_ProductName', $existingCols) ? 'c_ProductName' : 'ProductName';$supCol  = in_array('i_SupplierID', $existingCols) ? 'i_SupplierID' : 'SupplierID';$catCol  = in_array('i_CategoryID', $existingCols) ? 'i_CategoryID' : 'CategoryID';$unitCol = in_array('c_Unit', $existingCols) ? 'c_Unit' : (in_array('QuantityPerUnit',$existingCols) ? 'QuantityPerUnit' : 'Unit');

    // บันทึกข้อมูลด้วยชื่อคอลัมน์ที่ถูกต้องแน่นอน
    $sql = "INSERT INTO tb_products (`$nameCol`, `$supCol`, `$catCol`, `$unitCol`, `$priceCol`, `$stockCol`)
            VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)";

    $stmt =$conn->prepare($sql);$stmt->bindParam(':productName', $productName, PDO::PARAM_STR);$stmt->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);$stmt->bindParam(':catId', $catId, PDO::PARAM_INT);$stmt->bindParam(':unit', $unit, PDO::PARAM_STR);$stmt->bindParam(':price', $price, PDO::PARAM_STR);$stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);$stmt->execute();

    $lastId =$conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()]);
}
?>
