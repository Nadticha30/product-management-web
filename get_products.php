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
        $item = $row; 

        $stock = null;
        $price = null;

        // 1. ค้นหาคอลัมน์ที่มีค่าสต็อกจริงใน DB
        foreach ($row as $key => $val) {
            $lk = strtolower(trim($key));

            // ข้ามคอลัมน์ที่เป็นหน่วยนับ
            if (strpos($lk, 'perunit') !== false || $lk === 'unit' || $lk === 'c_unit') {
                continue;
            }

            // ถ้าเจอคอลัมน์ที่เป็นสต็อก
            if (in_array($lk, ['unitsinstock', 'i_unitsinstock', 'quantity', 'i_quantity', 'stock', 'i_stock', 'qty']) || strpos($lk, 'stock') !== false) {
                if ($val !== null && $val !== '' && is_numeric($val)) {
                    $stock = intval($val);
                    break;
                }
            }
        }

        // 2. ค้นหาคอลัมน์ราคา
        foreach ($row as $key => $val) {
            $lk = strtolower(trim($key));
            if (strpos($lk, 'price') !== false && $val !== null && $val !== '' && is_numeric($val)) {
                $price = floatval($val);
                break;
            }
        }

        // 3. บังคับยัดค่าลง Key ทุกรูปแบบที่ JS อาจจะเรียกใช้ (ทับค่าเดิมทันที)
        if ($stock !== null) {
            $item['UnitsInStock']   = $stock;
            $item['unitsInStock']   = $stock;
            $item['unitsinstock']   = $stock;
            $item['i_UnitsInStock'] = $stock;
            $item['Quantity']       = $stock;
            $item['quantity']       = $stock;
            $item['Stock']          = $stock;
            $item['stock']          = $stock;
        }

        if ($price !== null) {
            $item['UnitPrice']   = $price;
            $item['unitPrice']   = $price;
            $item['f_UnitPrice'] = $price;
            $item['Price']       = $price;
            $item['price']       = $price;
            $item['f_Price']     = $price;
        }

        $result[] = $item;
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
