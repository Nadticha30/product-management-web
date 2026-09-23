<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "ConnDB.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
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
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง']);
        exit;
    }

    // อ่านคอลัมน์จริงจากตาราง tb_products
    $stmtCols =$conn->query("SHOW COLUMNS FROM tb_products");
    $rawCols =$stmtCols->fetchAll(PDO::FETCH_COLUMN);

    if (empty($rawCols)) {
        throw new Exception("ไม่พบตาราง tb_products ในฐานข้อมูล");
    }

    // ค้นหาชื่อคอลัมน์จริงในตารางด้วย Keyword
    $colName = null; $colSup = null; $colCat = null; $colUnit = null; $colPrice = null; $colStock = null;

    foreach ($rawCols as$col) {
        $lc = strtolower($col);
        if (!$colName && strpos($lc, 'name') !== false) $colName =$col;
        if (!$colSup && strpos($lc, 'supplier') !== false) $colSup =$col;
        if (!$colCat && strpos($lc, 'cat') !== false) $colCat =$col;
        if (!$colPrice && strpos($lc, 'price') !== false) $colPrice =$col;
        if (!$colStock && (strpos($lc, 'stock') !== false || strpos($lc, 'quantity') !== false) && strpos($lc, 'unit') === false) $colStock =$col;
        if (!$colUnit && (strpos($lc, 'unit') !== false \vert{}\vert{} strpos($lc, 'quantityperunit') !== false) && strpos($lc, 'price') === false && strpos($lc, 'stock') === false) $colUnit =$col;
    }

    // ค่าสำรองกรณีคอลัมน์ไม่ตรง
    $colName  =$colName  ?? 'ProductName';
    $colSup   =$colSup   ?? 'SupplierID';
    $colCat   =$colCat   ?? 'CategoryID';
    $colUnit  =$colUnit  ?? 'QuantityPerUnit';
    $colPrice =$colPrice ?? 'UnitPrice';
    $colStock =$colStock ?? 'UnitsInStock';

    $sql = "INSERT INTO tb_products (`$colName`, `$colSup`, `$colCat`, `$colUnit`, `$colPrice`, `$colStock`) 
            VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)";

    $stmt =$conn->prepare($sql);$stmt->bindValue(':productName', $productName, PDO::PARAM_STR);$stmt->bindValue(':supplierId', $supplierId, PDO::PARAM_INT);$stmt->bindValue(':catId', $catId, PDO::PARAM_INT);$stmt->bindValue(':unit', $unit, PDO::PARAM_STR);$stmt->bindValue(':price', $price);$stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);$stmt->execute();

    $lastId =$conn->lastInsertId();

    ob_clean();
    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
