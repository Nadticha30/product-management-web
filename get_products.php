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
        $id = 0; $name = ''; $cat = 0; $sup = 0; $unit = ''; $price = 0.0; $stock = 0;

        // ค้นหาข้อมูลตามคอลัมน์จริงด้วย Keyword
        foreach ($row as $k => $v) {
            $lk = strtolower($k);

            if (strpos($lk, 'id') !== false && (strpos($lk, 'product') !== false || $lk === 'id')) {
                $id = $v;
            } elseif (strpos($lk, 'name') !== false) {
                $name = $v;
            } elseif (strpos($lk, 'cat') !== false) {
                $cat = $v;
            } elseif (strpos($lk, 'supplier') !== false) {
                $sup = $v;
            } elseif (strpos($lk, 'price') !== false && $v !== null && $v !== '') {
                $price = floatval($v);
            } elseif ((strpos($lk, 'stock') !== false || strpos($lk, 'quantity') !== false) && $v !== null && $v !== '' && strpos($lk, 'unit') === false) {
                $stock = intval($v);
            } elseif ((strpos($lk, 'unit') !== false || strpos($lk, 'quantityperunit') !== false) && strpos($lk, 'price') === false && strpos($lk, 'stock') === false) {
                if ($v !== null) $unit = strval($v);
            }
        }

        // ส่งออก JSON ครบทุกรูปแบบชื่อ Key ไม่ว่า JS จะเรียกใช้ชื่อไหนก็อ่านค่าได้ถูกต้อง
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
