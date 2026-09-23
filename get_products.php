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
        $item = $row; 
        
        $price = 0.0;
        $stock = 0;
        $name = '';
        $unit = '';
        
        // 1. หา "คงเหลือ" (ค้นหาแบบกว้าง แต่ถ้าเจอแล้วล็อคค่าทันที)
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            
            // ข้ามคอลัมน์ที่เกี่ยวกับหน่วยนับ (ป้องกันการมอง QuantityPerUnit เป็นสต็อก)
            if (strpos($lk, 'perunit') !== false || $lk === 'unit' || $lk === 'c_unit') {
                continue;
            }
            
            // ถ้าคอลัมน์มีคำว่า stock, quant, qty และค่าไม่ใช่ค่าว่าง
            if (strpos($lk, 'stock') !== false || strpos($lk, 'quant') !== false || strpos($lk, 'qty') !== false) {
                if (is_numeric($v)) {
                    $stock = (int)$v;
                    break; // เจอตัวเลขสต็อกแล้ว หยุดหาคอลัมน์อื่นทันที
                }
            }
        }

        // 2. หา "ราคา"
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            if (strpos($lk, 'price') !== false && is_numeric($v)) {
                $price = (float)$v;
                break;
            }
        }

        // 3. หา "ชื่อสินค้า" และ "หน่วยนับ"
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            if (strpos($lk, 'name') !== false) {
                $name = $v;
            }
            if (strpos($lk, 'unit') !== false && strpos($lk, 'price') === false && strpos($lk, 'stock') === false) {
                $unit = $v;
            }
        }

        // 4. แมปค่าส่งกลับให้หน้าเว็บ
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
