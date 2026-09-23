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
        // เอาข้อมูลเดิมจากฐานข้อมูลทั้งหมดใส่เข้าไปก่อน หน้าเว็บต้องการคีย์ไหนจะได้มีครบ
        $item = $row; 
        
        $price = 0;
        $stock = 0;
        
        // ค้นหาคอลัมน์ราคาและสต็อกแบบระบุคำเป๊ะๆ (ไม่ใช้การดักคำมั่วๆ แล้ว)
        foreach ($row as $k => $v) {
            $lk = strtolower($k);
            
            if (in_array($lk, ['unitprice', 'price', 'f_price'])) {
                $price = floatval($v);
            }
            if (in_array($lk, ['unitsinstock', 'quantity', 'stock', 'i_unitsinstock'])) {
                $stock = intval($v);
            }
        }

        // ยัดค่าลงใน Key มาตรฐานบังคับ เพื่อให้หน้าเว็บอ่านออก 100%
        $item['Price'] = $price;
        $item['Quantity'] = $stock;
        $item['UnitsInStock'] = $stock;
        $item['f_Price'] = $price;
        
        $result[] = $item;
    }

    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
