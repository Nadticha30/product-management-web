<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "ConnDB.php";
    $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($products as $row) {
        $item = $row; // เอาของเดิมมาให้หมดก่อน
        
        $price = 0.0;
        $stock = 0;
        $name = '';
        $unit = '';
        
        // ค้นหาว่าค่าไหนคือราคา และค่าไหนคือสต็อก
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            
            if (strpos($lk, 'price') !== false) {
                $price = floatval($v);
            } elseif (strpos($lk, 'stock') !== false) {
                $stock = intval($v);
            } elseif ($lk === 'quantity' || $lk === 'i_quantity') {
                $stock = intval($v);
            } elseif (strpos($lk, 'name') !== false) {
                $name = $v;
            } elseif (strpos($lk, 'unit') !== false && strpos($lk, 'price') === false && strpos($lk, 'stock') === false) {
                $unit = $v;
            }
        }

        // ยัดค่าลง Key บังคับ เพื่อให้หน้าเว็บอ่านออก 100% ไม่กลายเป็น 0
        $item['Price'] = $price;
        $item['UnitPrice'] = $price;
        $item['f_Price'] = $price;
        
        $item['Quantity'] = $stock;
        $item['UnitsInStock'] = $stock;
        $item['Stock'] = $stock;
        $item['i_UnitsInStock'] = $stock;

        if (!isset($item['ProductName'])) $item['ProductName'] = $name;
        if (!isset($item['Unit'])) $item['Unit'] = $unit;
        
        $result[] = $item;
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
