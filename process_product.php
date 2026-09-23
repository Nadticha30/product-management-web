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

    // 1. ดึงชื่อคอลัมน์ "ที่มีอยู่จริง" ในตาราง เพื่อป้องกัน Error 1054
    $stmtCols =$conn->query("SELECT * FROM tb_products LIMIT 0");
    $cols = [];
    for ($i = 0; $i <$stmtCols->columnCount(); $i++) {$meta = $stmtCols->getColumnMeta($i);
        if ($meta) $cols[] =$meta['name'];
    }

    // 2. ฟังก์ชันค้นหาคอลัมน์อัตโนมัติ
    function findCol($cols, $keywords,$exclude = []) {
        foreach ($cols as $col) {$lk = strtolower($col);$isExcluded = false;
            foreach ($exclude as$ex) {
                if (strpos($lk, $ex) !== false)$isExcluded = true;
            }
            if ($isExcluded) continue;
            
            foreach ($keywords as$kw) {
                if (strpos($lk, $kw) !== false) return$col;
            }
        }
        return null;
    }

    // 3. จับคู่คอลัมน์จริงกับข้อมูล
    $cName  = findCol($cols, ['name']);
    $cSup   = findCol($cols, ['sup']);
    $cCat   = findCol($cols, ['cat']);
    $cPrice = findCol($cols, ['price']);
    $cStock = findCol($cols, ['stock']);
    if (!$cStock) $cStock = findCol($cols, ['quantity'], ['unit', 'per']); // ป้องกันไปชนกับ QuantityPerUnit
    $cUnit  = findCol($cols, ['unit', 'perunit'], ['price', 'stock']);

    $supInt = intval(preg_replace('/[^0-9]/', '',$rawSupplier));
    $catInt = intval(preg_replace('/[^0-9]/', '',$rawCat));

    // 4. เตรียมข้อมูลที่จะบันทึก (เอาเฉพาะคอลัมน์ที่หาเจอเท่านั้น)
    $insertData = [];
    if ($cName)$insertData[$cName]  =$productName;
    if ($cSup)   $insertData[$cSup]   = $supInt > 0 ?$supInt : 1;
    if ($cCat)   $insertData[$cCat]   = $catInt > 0 ?$catInt : 1;
    if ($cUnit)$insertData[$cUnit]  =$unit;
    if ($cPrice)$insertData[$cPrice] = floatval($price);
    if ($cStock)$insertData[$cStock] = intval($quantity);

    // 5. บันทึกข้อมูล
    $fields = array_keys($insertData);
    $escapedFields = array_map(function($f) { return "`$f`"; }, $fields);
    $placeholders  = array_map(function($f) { return ":$f"; }, $fields);

    $sql = "INSERT INTO tb_products (" . implode(", ", $escapedFields) . ") VALUES (" . implode(", ", $placeholders) . ")";
    $stmt = $conn->prepare($sql);
    
    foreach ($insertData as$col => $val) {$stmt->bindValue(":$col", $val);
    }
    $stmt->execute();

    ob_clean();
    echo json_encode([
        'success' => true, 
        'id' => $conn->lastInsertId(),
        'message' => 'บันทึกสำเร็จ'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    http_response_code(200); 
    echo json_encode([
        'success' => false, 
        'message' => 'DB Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
