<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

require_once "ConnDB.php";

try {
    $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($products as $row) {
        $id   = $row['i_ProductID'] ?? $row['ProductID'] ?? $row['id'] ?? reset($row);
        $name = $row['c_ProductName'] ?? $row['ProductName'] ?? '';
        $cat  = $row['i_CategoryID'] ?? $row['CategoryID'] ?? 0;
        $sup  = $row['i_SupplierID'] ?? $row['SupplierID'] ?? 0;
        $unit = $row['c_Unit'] ?? $row['QuantityPerUnit'] ?? $row['Unit'] ?? '';

        // ค้นหาราคาจริงจากทุกชื่อคอลัมน์ที่เป็นไปได้
        $price = 0;
        foreach (['UnitPrice', 'f_UnitPrice', 'f_Price', 'Price'] as $pKey) {
            if (array_key_exists($pKey, $row) && $row[$pKey] !== null && $row[$pKey] !== '') {
                $price = floatval($row[$pKey]);
                break;
            }
        }

        // ค้นหาจำนวนคงเหลือจริงจากทุกชื่อคอลัมน์ที่เป็นไปได้
        $stock = 0;
        foreach (['UnitsInStock', 'i_UnitsInStock', 'Quantity'] as $sKey) {
            if (array_key_exists($sKey, $row) && $row[$sKey] !== null && $row[$sKey] !== '') {
                $stock = intval($row[$sKey]);
                break;
            }
        }

        $result[] = [
            'i_ProductID'    => $id,
            'c_ProductName'  => $name,
            'i_CategoryID'   => $cat,
            'i_SupplierID'   => $sup,
            'c_Unit'         => $unit,
            'f_Price'        => $price,
            'i_UnitsInStock' => $stock
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
