<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once "ConnDB.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง']);
        exit;
    }

    // 1. อ่านตัวอย่างคอลัมน์ที่มีอยู่จริงในตาราง tb_products
    $stmtCheck =$conn->query("SELECT * FROM tb_products LIMIT 1");
    $sample = $stmtCheck->fetch(PDO::FETCH_ASSOC);$cols = $sample ? array_keys($sample) : [];

    // 2. แมปชื่อคอลัมน์จริงพร้อมค่าตั้งต้นสำรอง (Prevent Null Column)
    $colName  = 'ProductName';
    foreach (['c_ProductName', 'ProductName', 'name'] as $c) {
        if (in_array($c,$cols)) { $colName =$c; break; }
    }

    $colSup   = 'SupplierID';
    foreach (['i_SupplierID', 'SupplierID'] as $c) {
        if (in_array($c,$cols)) { $colSup =$c; break; }
    }

    $colCat   = 'CategoryID';
    foreach (['i_CategoryID', 'CategoryID', 'CatID'] as $c) {
        if (in_array($c,$cols)) { $colCat =$c; break; }
    }

    $colUnit  = 'QuantityPerUnit';
    foreach (['QuantityPerUnit', 'c_Unit', 'Unit'] as $c) {
        if (in_array($c,$cols)) { $colUnit =$c; break; }
    }

    $colPrice = 'UnitPrice';
    foreach (['UnitPrice', 'f_UnitPrice', 'f_Price', 'Price'] as $c) {
        if (in_array($c,$cols)) { $colPrice =$c; break; }
    }

    $colStock = 'UnitsInStock';
    foreach (['UnitsInStock', 'i_UnitsInStock', 'Quantity', 'Stock'] as $c) {
        if (in_array($c,$cols)) { $colStock =$c; break; }
    }

    // 3. ทำการ Insert ข้อมูล
    $sql = "INSERT INTO tb_products (`$colName`, `$colSup`, `$colCat`, `$colUnit`, `$colPrice`, `$colStock`) 
            VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)";

    $stmt =$conn->prepare($sql);$stmt->bindValue(':productName', $productName, PDO::PARAM_STR);$stmt->bindValue(':supplierId', $supplierId, PDO::PARAM_INT);$stmt->bindValue(':catId', $catId, PDO::PARAM_INT);$stmt->bindValue(':unit', $unit, PDO::PARAM_STR);$stmt->bindValue(':price', $price);$stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);$stmt->execute();

    $lastId =$conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดจาก Server: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
