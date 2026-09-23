<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "ConnDB.php";
    
    // เผื่อไว้กรณีตารางชื่อ products แทน tb_products
    try {
        $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    } catch (Throwable $e) {
        $stmt = $conn->query("SELECT * FROM products ORDER BY 1 DESC");
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];

    foreach ($products as $row) {
        $item = $row; // 1. ดึงข้อมูลจาก DB มาเก็บไว้แบบ 100% (ไม่แก้ไข ไม่เขียนทับ)
        
        $stock = 0;
        $price = 0;

        // 2. พยายามสแกนหาคอลัมน์ "คงเหลือ" และ "ราคา" แบบเงียบๆ
        foreach ($row as $k => $v) {
            $lk = strtolower(trim($k));
            
            // หาคงเหลือ
            if (in_array($lk, ['unitsinstock', 'quantity', 'stock', 'amount', 'qty']) || strpos($lk, 'stock') !== false) {
                if (is_numeric($v)) $stock = (int)$v;
            }
            // หาราคา
            if (strpos($lk, 'price') !== false && is_numeric($v)) {
                $price = (float)$v;
            }
        }

        // 3. เติม Key ให้หน้าเว็บ "เฉพาะกรณีที่มันยังไม่มี" เท่านั้น
        // จะได้ไม่ไปทับค่าจริงที่ดึงมาจากฐานข้อมูล
        
        if (!isset($item['Quantity']))     $item['Quantity'] = $stock;
        if (!isset($item['UnitsInStock'])) $item['UnitsInStock'] = $stock;
        if (!isset($item['Stock']))        $item['Stock'] = $stock;
        if (!isset($item['qty']))          $item['qty'] = $stock;

        if (!isset($item['Price']))        $item['Price'] = $price;
        if (!isset($item['UnitPrice']))    $item['UnitPrice'] = $price;

        $result[] = $item;
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
