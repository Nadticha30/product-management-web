<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "ConnDB.php";

    $productName = trim($_POST['ProductName'] ?? '');
    $rawSupplier = trim($_POST['SupplierID'] ?? '');
    $rawCat      = trim($_POST['CatID'] ?? '');
    $unit        = trim($_POST['Unit'] ?? '');
    $price       = trim($_POST['Price'] ?? '0');
    $quantity    = trim($_POST['Quantity'] ?? '0');

    if ($productName === '') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อสินค้า']);
        exit;
    }

    // อ่านชื่อคอลัมน์จริงจากฐานข้อมูล
    $stmtCols =$conn->query("SHOW COLUMNS FROM tb_products");
    $cols =$stmtCols->fetchAll(PDO::FETCH_COLUMN);

    // ค่าเริ่มต้นของชื่อคอลัมน์
    $cName = 'ProductName';$cSup = 'SupplierID'; $cCat = 'CategoryID';$cUnit = 'QuantityPerUnit'; $cPrice = 'UnitPrice';$cStock = 'UnitsInStock';

    // จับคู่ชื่อคอลัมน์เป๊ะๆ ป้องกันการบันทึกลงผิดช่อง
    foreach ($cols as$col) {
        $lk = strtolower($col);
        if (in_array($lk, ['productname', 'name', 'c_productname'])) $cName =$col;
        if (in_array($lk, ['supplierid', 'supid', 'i_supplierid'])) $cSup =$col;
        if (in_array($lk, ['categoryid', 'catid', 'i_categoryid'])) $cCat =$col;
        if (in_array($lk, ['quantityperunit', 'unit', 'c_unit'])) $cUnit =$col;
        if (in_array($lk, ['unitprice', 'price', 'f_price'])) $cPrice =$col;
        if (in_array($lk, ['unitsinstock', 'quantity', 'stock', 'i_unitsinstock'])) $cStock =$col;
    }

    // กรองเอาเฉพาะตัวเลข (แก้บั๊กกรณีหน้าเว็บส่งข้อความ SUP001 / CAT02 มา)
    $supInt = intval(preg_replace('/[^0-9]/', '',$rawSupplier));
    $catInt = intval(preg_replace('/[^0-9]/', '',$rawCat));

    $sql = "INSERT INTO tb_products (`$cName`, `$cSup`, `$cCat`, `$cUnit`, `$cPrice`, `$cStock`) 
            VALUES (:name, :sup, :cat, :unit, :price, :qty)";
    
    $stmt =$conn->prepare($sql);$stmt->bindValue(':name', $productName);$stmt->bindValue(':sup', $supInt > 0 ?$supInt : 1);
    $stmt->bindValue(':cat',$catInt > 0 ? $catInt : 1);$stmt->bindValue(':unit', $unit);$stmt->bindValue(':price', floatval($price));$stmt->bindValue(':qty', intval($quantity));$stmt->execute();

    ob_clean();
    echo json_encode([
        'success' => true, 
        'id' => $conn->lastInsertId(),
        'message' => 'บันทึกสำเร็จ'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    http_response_code(200); // บังคับส่ง 200 ให้หน้าเว็บอ่าน Error จริงๆ ออกมา ไม่ใช่ฟ้องแค่ Server Error
    echo json_encode([
        'success' => false, 
        'message' => 'DB Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
