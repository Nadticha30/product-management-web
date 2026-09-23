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
        // ปรับเป็นอักษรพิมพ์เล็กเพื่อแมปคีย์ได้ถูกต้องแน่นอน
        $lRow = [];
        foreach ($row as $k => $v) {
            $lRow[strtolower($k)] = $v;
        }

        $id   = $lRow['productid'] ?? $lRow['i_productid'] ?? $lRow['id'] ?? reset($row);
        $name = $lRow['productname'] ?? $lRow['c_productname'] ?? $lRow['name'] ?? '';
        $cat  = $lRow['categoryid'] ?? $lRow['i_categoryid'] ?? $lRow['catid'] ?? 0;
        $sup  = $lRow['supplierid'] ?? $lRow['i_supplierid'] ?? $lRow['supid'] ?? 0;
        $unit = $lRow['quantityperunit'] ?? $lRow['c_unit'] ?? $lRow['unit'] ?? '';

        // ดึงราคา
        $price = 0.0;
        foreach (['unitprice', 'f_unitprice', 'f_price', 'price'] as $pk) {
            if (isset($lRow[$pk]) && $lRow[$pk] !== null && $lRow[$pk] !== '') {
                $price = floatval($lRow[$pk]);
                break;
            }
        }

        // ดึงจำนวนคงเหลือ (แก้บั๊ก UnitsInStock แล้ว)
        $stock = 0;
        foreach (['unitsinstock', 'i_unitsinstock', 'quantity', 'i_quantity', 'stock', 'unitsinorder'] as $sk) {
            if (isset($lRow[$sk]) && $lRow[$sk] !== null && $lRow[$sk] !== '') {
                $stock = intval($lRow[$sk]);
                break;
            }
        }

        $result[] = [
            'i_ProductID'     => $id,
            'ProductID'       => $id,
            'c_ProductName'   => $name,
            'ProductName'     => $name,
            'i_CategoryID'    => $cat,
            'CategoryID'      => $cat,
            'i_SupplierID'    => $sup,
            'SupplierID'      => $sup,
            'c_Unit'          => $unit,
            'QuantityPerUnit' => $unit,
            'Unit'            => $unit,
            'f_Price'         => $price,
            'UnitPrice'       => $price,
            'Price'           => $price,
            'f_UnitPrice'     => $price,
            'i_UnitsInStock'  => $stock,
            'UnitsInStock'    => $stock,
            'Quantity'        => $stock
        ];
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
