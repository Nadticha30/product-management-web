<?php
header('Content-Type: application/json; charset=utf-8');
require_once "ConnDB.php";

try {
    $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($products as $row) {
        $id   = $row['i_ProductID'] ?? $row['ProductID'] ?? $row['id'] ?? 0;
        $name = $row['c_ProductName'] ?? $row['ProductName'] ?? $row['name'] ?? '';
        $cat  = $row['i_CategoryID'] ?? $row['CategoryID'] ?? 0;
        $sup  = $row['i_SupplierID'] ?? $row['SupplierID'] ?? 0;
        $unit = $row['c_Unit'] ?? $row['QuantityPerUnit'] ?? $row['Unit'] ?? '';
        
        // ค้นหาราคาจากชื่อคอลัมน์ที่เป็นไปได้ทั้งหมด
        $price = 0;
        foreach (['f_UnitPrice', 'UnitPrice', 'f_Price', 'Price', 'f_unitprice', 'unitprice', 'price'] as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                $price = floatval($row[$key]);
                break;
            }
        }

        // ค้นหาจำนวนคงเหลือจากชื่อคอลัมน์ที่เป็นไปได้ทั้งหมด
        $stock = 0;
        foreach (['i_UnitsInStock', 'UnitsInStock', 'i_Quantity', 'Quantity', 'unitsinstock', 'stock'] as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                $stock = intval($row[$key]);
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
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
