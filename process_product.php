<?php
header('Content-Type: application/json; charset=utf-8');

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

    // อ่านโครงสร้างคอลัมน์จริงจากฐานข้อมูล MySQL Direct
    $stmtCols =$conn->query("DESCRIBE tb_products");
    $rawCols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);$colMap = [];
    foreach ($rawCols as $col) {$colMap[strtolower($col)] =$col;
    }

    $findRealCol = function($aliases) use ($colMap) {
        foreach ($aliases as$alias) {
            $low = strtolower($alias);
            if (isset($colMap[$low])) return $colMap[$low];
        }
        return null;
    };

    // จับคู่คอลัมน์จริงใน DB
    $cName  =$findRealCol(['ProductName', 'c_ProductName', 'Name']);
    $cSup   =$findRealCol(['SupplierID', 'i_SupplierID']);
    $cCat   =$findRealCol(['CategoryID', 'i_CategoryID']);
    $cUnit  =$findRealCol(['QuantityPerUnit', 'c_Unit', 'Unit']);
    $cPrice =$findRealCol(['UnitPrice', 'f_UnitPrice', 'f_Price', 'Price']);
    $cStock =$findRealCol(['UnitsInStock', 'i_UnitsInStock', 'Quantity', 'Stock']);

    $insertData = [];
    if ($cName)$insertData[$cName]  =$productName;
    if ($cSup)$insertData[$cSup]   =$supplierId;
    if ($cCat)$insertData[$cCat]   =$catId;
    if ($cUnit)$insertData[$cUnit]  =$unit;
    if ($cPrice)$insertData[$cPrice] =$price;
    if ($cStock)$insertData[$cStock] =$quantity;

    $colsList = implode("`, `", array_keys($insertData));
    $placeholders = ":" . implode(", :", array_keys($insertData));

    $sql = "INSERT INTO tb_products (`{$colsList}`) VALUES ({$placeholders})";
    $stmt = $conn->prepare($sql);

    foreach ($insertData as$colKey => $val) {$stmt->bindValue(":" . $colKey,$val);
    }

    $stmt->execute();
    $lastId =$conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
