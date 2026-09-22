<?php
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
                   i_Price = :price 
               WHERE i_ProductID = :productID";
                   
       $result = $conn->prepare($sql);
       $result->bindParam(':productName', $productName, PDO::PARAM_STR);
       $result->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
       $result->bindParam(':catId', $catId, PDO::PARAM_INT);
       $result->bindParam(':unit', $unit, PDO::PARAM_STR);
       $result->bindParam(':price', $price, PDO::PARAM_STR);
       $result->bindParam(':productID', $productID, PDO::PARAM_INT);
       $result->execute();

       http_response_code(200);
       echo json_encode([
           'success' => true,
           'id' => $productID,
           'message' => 'อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว'
       ], JSON_UNESCAPED_UNICODE);

   } else {
       $sql = "INSERT INTO tb_products (c_ProductName, i_SupplierID, i_CategoryID, c_Unit, i_Price)
               VALUES (:productName, :supplierId, :catId, :unit, :price)";

       $result = $conn->prepare($sql);
       $result->bindParam(':productName', $productName, PDO::PARAM_STR);
       $result->bindParam(':supplierId', $supplierId, PDO::PARAM_INT);
       $result->bindParam(':catId', $catId, PDO::PARAM_INT);
       $result->bindParam(':unit', $unit, PDO::PARAM_STR);
       $result->bindParam(':price', $price, PDO::PARAM_STR);
       $result->execute();

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
       'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
   ], JSON_UNESCAPED_UNICODE);
}
?>