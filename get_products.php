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
        $id = 0; $name = ''; $cat = 0; $sup = 0; $unit = ''; $price = 0.0; $stock = 0;

        foreach ($row as $k => $v) {
            $lk = strtolower($k);

            // 1. ดึงราคา (รองรับ UnitPrice, f_UnitPrice, f_Price, Price)
            if (strpos($lk, 'price') !== false) {
                if ($v !== null && $v !== '') $price = floatval($v);
            }
            // 2. ดึงจำนวนคงเหลือ (รองรับ UnitsInStock, i_UnitsInStock, Quantity, Stock)
            elseif (strpos($lk, 'stock') !== false || strpos($lk, 'quantity') !== false || $lk === 'stock') {
                if (strpos($lk, 'perunit') === false && strpos($lk, 'c_unit') === false && $lk !== 'unit') {
                    if ($v !== null && $v !== '' && is_numeric($v)) {
                        $stock = intval($v);
                    }
                }
            }
            // 3. ดึงหน่วยนับ (รองรับ QuantityPerUnit, c_Unit, Unit)
            elseif (strpos($lk, 'unit') !== false || strpos($lk, 'perunit') !== false) {
                if (strpos($lk, 'price') === false && strpos($lk, 'stock') === false) {
                    if ($v !== null) $unit = (string)$v;
                }
            }
            // 4. ดึงชื่อสินค้า
            elseif (strpos($lk, 'name') !== false) {
                if ($v !== null) $name = (string)$v;
            }
            // 5. ดึงหมวดหมู่
            elseif (strpos($lk, 'cat') !== false) {
                if ($v !== null) $cat = $v;
            }
            // 6. ดึงผู้จัดจำหน่าย
            elseif (strpos($lk, 'supplier') !== false || strpos($lk, 'sup') !== false) {
                if ($v !== null) $sup = $v;
            }
            // 7. ดึง รหัสสินค้า
            elseif (strpos($lk, 'id') !== false || $lk === 'id') {
                if ($v !== null) $id = $v;
            }
        }

        if ($id === 0 && !empty($row)) {
            $id = reset($row);
        }

        // ส่งออก JSON ครบทุกรูปแบบคีย์
        $result[] = [
            'i_ProductID'     => $id,
            'ProductID'       => $id,
            'id'              => $id,
            'c_ProductName'   => $name,
            'ProductName'     => $name,
            'name'            => $name,
            'i_CategoryID'    => $cat,
            'CategoryID'      => $cat,
            'CatID'           => $cat,
            'i_SupplierID'    => $sup,
            'SupplierID'      => $sup,
            'SupID'           => $sup,
            'c_Unit'          => $unit,
            'QuantityPerUnit' => $unit,
            'Unit'            => $unit,
            'f_Price'         => $price,
            'UnitPrice'       => $price,
            'Price'           => $price,
            'f_UnitPrice'     => $price,
            'i_UnitsInStock'  => $stock,
            'UnitsInStock'    => $stock,
            'Quantity'        => $stock,
            'Stock'           => $stock
        ];
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
