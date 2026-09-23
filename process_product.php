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
    $rawSupplier = trim($_POST['SupplierID'] ?? '');
    $rawCat      = trim($_POST['CatID'] ?? '');
    $unit        = trim($_POST['Unit'] ?? '');
    $price       = trim($_POST['Price'] ?? '0');
    $quantity    = trim($_POST['Quantity'] ?? '0');

    if ($productName === '' \vert{}\vert{}$rawSupplier === '' || $rawCat === '' \vert{}\vert{}$unit === '') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง']);
        exit;
    }

    // แปลงรหัส SUP001 / CAT02 ให้เป็นตัวเลขสำหรับ MySQL Integer
    $supplierId = intval(preg_replace('/[^0-9]/', '',$rawSupplier));
    if ($supplierId === 0)$supplierId = 1;

    $catId = intval(preg_replace('/[^0-9]/', '',$rawCat));
    if ($catId === 0)$catId = 1;

    $priceNum = floatval($price);
    $stockNum = intval($quantity);

    // ดึงคอลัมน์จริงจาก MySQL
    $stmtCols =$conn->query("SHOW COLUMNS FROM tb_products");
    $rawCols =$stmtCols->fetchAll(PDO::FETCH_COLUMN);

    $colsLower = array_map('strtolower', $rawCols);$colMap = array_combine($colsLower,$rawCols);

    $findCol = function($candidates) use ($colMap) {
        foreach ($candidates as$cand) {
            $lc = strtolower($cand);
            if (isset($colMap[$lc])) return $colMap[$lc];
        }
        return null;
    };

    $colName  =$findCol(['ProductName', 'c_ProductName', 'Name']) ?? 'ProductName';
    $colSup   =$findCol(['SupplierID', 'i_SupplierID', 'SupID']) ?? 'SupplierID';
    $colCat   =$findCol(['CategoryID', 'i_CategoryID', 'CatID']) ?? 'CategoryID';
    $colUnit  =$findCol(['QuantityPerUnit', 'c_Unit', 'Unit']) ?? 'QuantityPerUnit';
    $colPrice =$findCol(['UnitPrice', 'f_UnitPrice', 'f_Price', 'Price']) ?? 'UnitPrice';
    $colStock =$findCol(['UnitsInStock', 'i_UnitsInStock', 'Quantity', 'Stock']) ?? 'UnitsInStock';

    $sql = "INSERT INTO tb_products (`{$colName}`, `{$colSup}`, `{$colCat}`, `{$colUnit}`, `{$colPrice}`, `{$colStock}`) 
            VALUES (:productName, :supplierId, :catId, :unit, :price, :quantity)";

    $stmt =$conn->prepare($sql);$stmt->bindValue(':productName', $productName, PDO::PARAM_STR);$stmt->bindValue(':supplierId', $supplierId, PDO::PARAM_INT);$stmt->bindValue(':catId', $catId, PDO::PARAM_INT);$stmt->bindValue(':unit', $unit, PDO::PARAM_STR);$stmt->bindValue(':price', $priceNum);$stmt->bindValue(':quantity', $stockNum, PDO::PARAM_INT);$stmt->execute();

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
