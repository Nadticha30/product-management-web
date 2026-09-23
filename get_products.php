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
        $item = $row; // เก็บข้อมูลดั้งเดิมไว้ทั้งหมด
        
        $price = 0.0;
        $stock = 0;
        $name = '';
        $unit = '';
        
        // 1. ดึงสต็อก (แก้ไขใหม่: ล็อกเป้าหาเฉพาะคอลัมน์สต็อก ถ้าเจอแล้วหยุดเลยเพื่อป้องกันค่าโดนทับ)
        $stockCols = ['unitsinstock', 'quantity', 'stock', 'i_unitsinstock', 'i_quantity', 'qty', 'amount'];
        $foundStock = false;
        foreach ($stockCols as $sc) {
            foreach ($row as $k => $v) {
                if (strtolower($k) === $sc) {
                    if ($v !== null && $v !== '') {
                        $stock = intval($v);
                        $foundStock = true;
                        break;
                    }
                }
            }
            if ($foundStock) break;
        }

        // 2. ดึงข้อมูลอื่นๆ (คงของเดิมที่ทำงานได้ดีอยู่แล้วไว้)
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            
            if (strpos($lk, 'price') !== false) {
                if ($v !== null && $v !== '') $price = floatval($v);
            } elseif (strpos($lk, 'name') !== false) {
                if ($v !== null) $name = $v;
            } elseif (strpos($lk, 'unit') !== false && strpos($lk, 'price') === false && strpos($lk, 'stock') === false && strpos($lk, 'unitsinstock') === false) {
                if ($v !== null) $unit = $v;
            }
            
            // กรณีชื่อคอลัมน์สต็อกแปลกประหลาดที่หาจากขั้นตอนแรกไม่เจอ
            if (!$foundStock && (strpos($lk, 'stock') !== false || strpos($lk, 'qty') !== false)) {
                if ($v !== null && $v !== '') $stock = intval($v);
            }
        }

        // 3. แมปค่ากลับเข้าไปใน Key มาตรฐานที่หน้าเว็บต้องการ
        $item['Price'] = $price;
        $item['UnitPrice'] = $price;
        $item['f_Price'] = $price;
        
        $item['Quantity'] = $stock;
        $item['UnitsInStock'] = $stock;
        $item['Stock'] = $stock;
        $item['i_UnitsInStock'] = $stock;

        if (!isset($item['ProductName']) && $name !== '') $item['ProductName'] = $name;
        if (!isset($item['Unit']) && $unit !== '') $item['Unit'] = $unit;
        
        $result[] = $item;
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
