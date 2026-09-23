<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once "ConnDB.php";

    $stmt = $conn->query("SELECT * FROM tb_products ORDER BY 1 DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($products as $row) {
        $id = 0;
        $name = '';
        $cat = 0;
        $sup = 0;
        $unit = '';
        $price = 0.0;
        $stock = 0;

        // ดึงค่าโดยจับคู่คอลัมน์แบบไม่สนตัวพิมพ์เล็ก-ใหญ่
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            
            if (in_array($lk, ['productid', 'i_productid', 'id'])) {
                $id = $v;
            } elseif (in_array($lk, ['productname', 'c_productname', 'name'])) {
                $name = $v;
            } elseif (in_array($lk, ['categoryid', 'i_categoryid', 'catid'])) {
                $cat = $v;
            } elseif (in_array($lk, ['supplierid', 'i_supplierid'])) {
                $sup = $v;
            } elseif (in_array($lk, ['quantityperunit', 'c_unit', 'unit'])) {
                $unit = $v;
            } elseif (in_array($lk, ['unitprice', 'f_unitprice', 'f_price', 'price'])) {
                if ($v !== null && $v !== '') $price = floatval($v);
            } elseif (in_array($lk, ['unitsinstock', 'i_unitsinstock', 'quantity', 'stock'])) {
                if ($v !== null && $v !== '') $stock = intval($v);
            }
        }

        // ส่งออก JSON ครบทุกรูปแบบที่ JavaScript หน้าเว็บจะเรียกใช้งาน
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
            'i_UnitsInStock'  => $stock,
            'UnitsInStock'    => $stock,
            'Quantity'        => $stock
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
