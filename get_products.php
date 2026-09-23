<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "ConnDB.php";

    try {
        $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    } catch (Throwable $e) {
        $stmt = $conn->query("SELECT * FROM products ORDER BY 1 DESC");
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];

    foreach ($products as $row) {
        $item = $row; // เก็บข้อมูลดั้งเดิมไว้ทั้งหมด
        
        $stock = 0;
        $price = 0.0;

        // 1. ค้นหาค่าสต็อกจริงจากคอลัมน์ใน DB
        foreach ($row as $k => $v) {
            $lk = strtolower(trim($k));

            // ข้ามคอลัมน์ที่เป็นข้อความหน่วยนับ (ป้องกันการเอา QuantityPerUnit มานับเป็นสต็อก)
            if (strpos($lk, 'perunit') !== false || $lk === 'unit' || $lk === 'c_unit' || strpos($lk, 'unitname') !== false) {
                continue;
            }

            // เช็กคอลัมน์ที่เป็นสต็อก/จำนวน
            if (in_array($lk, ['unitsinstock', 'i_unitsinstock', 'quantity', 'i_quantity', 'stock', 'i_stock', 'qty']) ||
                strpos($lk, 'stock') !== false || strpos($lk, 'quant') !== false) {
                
                if ($v !== null && $v !== '' && is_numeric($v)) {
                    $stock = intval($v);
                    break;
                }
            }
        }

        // 2. ค้นหาราคา
        foreach ($row as $k => $v) {
            $lk = strtolower(trim($k));
            if (strpos($lk, 'price') !== false && $v !== null && $v !== '' && is_numeric($v)) {
                $price = floatval($v);
                break;
            }
        }

        // 3. ปูพรมยัด Key สต็อกทุกรูปแบบภาษา JS (กัน JS หน้าเว็บอ่านไม่เจอ 100%)
        $item['UnitsInStock']   = $stock;
        $item['unitsInStock']   = $stock;
        $item['unitsinstock']   = $stock;
        $item['i_UnitsInStock'] = $stock;
        $item['i_unitsinstock'] = $stock;
        
        $item['Quantity']       = $stock;
        $item['quantity']       = $stock;
        $item['i_Quantity']     = $stock;
        
        $item['Stock']          = $stock;
        $item['stock']          = $stock;
        $item['i_Stock']        = $stock;
        
        $item['Qty']            = $stock;
        $item['qty']            = $stock;

        // 4. ปูพรมยัด Key ราคา
        $item['UnitPrice']      = $price;
        $item['unitPrice']      = $price;
        $item['f_UnitPrice']    = $price;
        $item['Price']          = $price;
        $item['price']          = $price;
        $item['f_Price']        = $price;

        $result[] = $item;
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
