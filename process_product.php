<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

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
    // ดึงตัวอย่างโครงสร้างคอลัมน์จริงจากเซิร์ฟเวอร์
    $stmtCheck =$conn->query("SELECT * FROM tb_products LIMIT 1");
    $sample = $stmtCheck->fetch(PDO::FETCH_ASSOC);$cols = $sample ? array_keys($sample) : [];

    // ฟังก์ชันจับคู่ชื่อคอลัมน์อัตโนมัติ
    $findCol = function($candidates, $default) use ($cols) {
        foreach ($candidates as$c) {
            if (in_array($c, $cols)) return$c;
        }
        return $default;
    };

    $colName  =$findCol(['c_ProductName', 'ProductName'], 'ProductName');
    $colSup   =$findCol(['i_SupplierID', 'SupplierID'], 'SupplierID');
    $colCat   =$findCol(['i_CategoryID', 'CategoryID'], 'CategoryID');
    $colUnit  =$findCol(['c_Unit', 'QuantityPerUnit', 'Unit'], 'QuantityPerUnit');
    $colPrice =$findCol(['UnitPrice', 'f_UnitPrice', 'f_Price', 'Price'], 'UnitPrice');
    $colStock =$findCol(['UnitsInStock', 'i_UnitsInStock', 'Quantity'], 'UnitsInStock');

    $sql = "INSERT INTO tb_products (`$colName`, `$colSup`, `$colCat`, `$colUnit`, `$colPrice`, `$colStock`) 
            VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)";

    $stmt =$conn->prepare($sql);$stmt->bindValue(':productName', $productName, PDO::PARAM_STR);$stmt->bindValue(':supplierId', $supplierId, PDO::PARAM_INT);$stmt->bindValue(':catId', $catId, PDO::PARAM_INT);$stmt->bindValue(':unit', $unit, PDO::PARAM_STR);$stmt->bindValue(':price', $price);$stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);$stmt->execute();

    $lastId =$conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()]);
}
?>
