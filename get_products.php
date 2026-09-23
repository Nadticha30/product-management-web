<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once "ConnDB.php";

    $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($products as $row) {
        $lowerRow = [];
        foreach ($row as $k => $v) {
            $lowerRow[strtolower($k)] = $v;
        }

        $id    = $lowerRow['productid'] ?? $lowerRow['i_productid'] ?? $lowerRow['id'] ?? reset($row);
        $name  = $lowerRow['productname'] ?? $lowerRow['c_productname'] ?? $lowerRow['name'] ?? '';
        $cat   = $lowerRow['categoryid'] ?? $lowerRow['i_categoryid'] ?? 0;
        $sup   = $lowerRow['supplierid'] ?? $lowerRow['i_supplierid'] ?? 0;
        $unit  = $lowerRow['quantityperunit'] ?? $lowerRow['c_unit'] ?? $lowerRow['unit'] ?? '';
        
        $price = floatval($lowerRow['unitprice'] ?? $lowerRow['f_unitprice'] ?? $lowerRow['f_price'] ?? $lowerRow['price'] ?? 0);
        $stock = intval($lowerRow['unitsinstock'] ?? $lowerRow['i_unitsinstock'] ?? $lowerRow['quantity'] ?? 0);

        // ส่งออกครอบคลุมทุกชื่อ Attribute ให้ JavaScript หน้าเว็บอ่านได้แน่ๆ
        $result[] = [
            'i_ProductID'    => $id,
            'ProductID'      => $id,
            'c_ProductName'  => $name,
            'ProductName'    => $name,
            'i_CategoryID'   => $cat,
            'CategoryID'     => $cat,
            'i_SupplierID'   => $sup,
            'SupplierID'     => $sup,
            'c_Unit'         => $unit,
            'QuantityPerUnit'=> $unit,
            'Unit'           => $unit,
            'f_Price'        => $price,
            'UnitPrice'      => $price,
            'Price'          => $price,
            'i_UnitsInStock' => $stock,
            'UnitsInStock'   => $stock,
            'Quantity'       => $stock
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
