<?php
// show_stones.php
require_once 'db_config.php';

try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. ดึงข้อมูลหิน 10 รายการแรก
    $stmt = $conn->query("SELECT id, thai_name, english_name, description FROM stones LIMIT 10");
    $stones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แสดงผลข้อมูลหิน</title>
</head>
<body>
    <h1>รายการหิน 10 ชนิดแรกที่นำเข้าสำเร็จ</h1>
    <table border="1" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>ID</th>
                <th>ชื่อไทย (Thai Name)</th>
                <th>ชื่ออังกฤษ (English Name)</th>
                <th>คำอธิบาย (Description)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stones as $stone): ?>
            <tr>
                <td><?php echo htmlspecialchars($stone['id']); ?></td>
                <td><?php echo htmlspecialchars($stone['thai_name']); ?></td>
                <td><?php echo htmlspecialchars($stone['english_name']); ?></td>
                <td><?php echo htmlspecialchars(mb_substr($stone['description'], 0, 100, 'UTF-8') . '...'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>