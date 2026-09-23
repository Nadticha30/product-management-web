<?php
header('Content-Type: application/json; charset=utf-8');
require_once "ConnDB.php";

try {
    $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ปรับชื่อ Field ส่งออกให้ตรงกับที่ Dashboard ต้องการ ไม่ว่าจะใช้คอลัมน์ชื่อไหนใน DB
    $formatted = array_map(function($row) {
        $id    = $row['i_ProductID'] ?? $row['ProductID'] ?? $row['f_ProductID'] ?? reset($row);
        $name  = $row['c_ProductName'] ?? $row['ProductName'] ?? '';
        $cat   = $row['i_CategoryID'] ?? $row['CategoryID'] ?? 0;
        $sup   = $row['i_SupplierID'] ?? $row['SupplierID'] ?? 0;
        $unit  = $row['c_Unit'] ?? $row['QuantityPerUnit'] ?? $row['Unit'] ?? '';
        $price = floatval($row['f_Price'] ?? $row['UnitPrice'] ?? $row['f_UnitPrice'] ?? $row['Price'] ?? 0);
        $stock = intval($row['i_UnitsInStock'] ?? $row['UnitsInStock'] ?? $row['Quantity'] ?? 0);

        return [
            'i_ProductID'    => $id,
            'c_ProductName'  => $name,
            'i_CategoryID'   => $cat,
            'i_SupplierID'   => $sup,
            'c_Unit'         => $unit,
            'f_Price'        => $price,
            'i_UnitsInStock' => $stock
        ];
    }, $products);

    echo json_encode($formatted, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
