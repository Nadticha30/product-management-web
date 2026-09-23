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

    // แปลงรหัสข้อความ SUP001 / CAT01 ให้กลายเป็นตัวเลข ID 
    $supInt = intval(preg_replace('/[^0-9]/', '',$rawSupplier));
    $catInt = intval(preg_replace('/[^0-9]/', '',$rawCat));

    $supVal   = ($supInt > 0) ?$supInt : 1;
    $catVal   = ($catInt > 0) ?$catInt : 1;
    $priceVal = floatval($price);
    $stockVal = intval($quantity);

    // ดึงรายชื่อคอลัมน์จริงแบบปลอดภัย
    $tableName = 'tb_products';$cols = [];

    try {
        $stmt =$conn->query("SELECT * FROM tb_products LIMIT 1");
    } catch (Throwable $e) {$tableName = 'products';
        $stmt =$conn->query("SELECT * FROM products LIMIT 1");
    }

    if ($stmt) {
        $colCount =$stmt->columnCount();
        for ($i = 0; $i <$colCount; $i++) {$meta = $stmt->getColumnMeta($i);
            if ($meta && isset($meta['name'])) {
                $cols[] =$meta['name'];
            }
        }
    }

    $findCol = function($candidates) use ($cols) {
        foreach ($candidates as$cand) {
            foreach ($cols as$col) {
                if (strtolower($col) === strtolower($cand)) return$col;
            }
        }
        foreach ($candidates as$cand) {
            foreach ($cols as$col) {
                if (strpos(strtolower($col), strtolower($cand)) !== false) return$col;
            }
        }
        return null;
    };

    $cName  =$findCol(['ProductName', 'c_ProductName', 'Name']) ?? 'ProductName';
    $cSup   =$findCol(['SupplierID', 'i_SupplierID', 'Supplier']) ?? 'SupplierID';
    $cCat   =$findCol(['CategoryID', 'i_CategoryID', 'Category', 'CatID']) ?? 'CategoryID';
    $cUnit  =$findCol(['QuantityPerUnit', 'c_Unit', 'Unit']) ?? 'QuantityPerUnit';
    $cPrice =$findCol(['UnitPrice', 'f_UnitPrice', 'f_Price', 'Price']) ?? 'UnitPrice';
    $cStock =$findCol(['UnitsInStock', 'i_UnitsInStock', 'Quantity', 'Stock']) ?? 'UnitsInStock';

    $insertData = [
        $cName  =>$productName,
        $cSup   =>$supVal,
        $cCat   =>$catVal,
        $cUnit  =>$unit,
        $cPrice =>$priceVal,
        $cStock =>$stockVal
    ];

    $fields = array_keys($insertData);
    $escapedFields = array_map(function($f) { return "`$f`"; }, $fields);
    $placeholders  = array_map(function($f) { return ":$f"; }, $fields);

    $sql = "INSERT INTO {$tableName} (" . implode(", ", $escapedFields) . ") VALUES (" . implode(", ", $placeholders) . ")";
    $stmt = $conn->prepare($sql);

    foreach ($insertData as$col => $val) {$stmt->bindValue(":$col", $val);
    }

    $stmt->execute();
    $lastId =$conn->lastInsertId();

    ob_clean();
    echo json_encode([
        'success' => true,
        'status'  => 'success',
        'id'      => $lastId,
        'message' => 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'status'  => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
