<?php
// C:\xampp\htdocs\deepstone\index.php - หน้าหลักสำหรับแสดงแคตตาล็อกหินมงคล

require_once 'db_config.php';

// **************************************************************************
// 1. การตั้งค่าและการจัดการ Pagination (น้อยสุด 10 รายการต่อหน้า)
// **************************************************************************
$records_per_page_options = [10, 20, 50, 100]; 

$records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$records_per_page = in_array($records_per_page, $records_per_page_options) ? $records_per_page : 20;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = $current_page < 1 ? 1 : $current_page; 

$start_from = ($current_page - 1) * $records_per_page;

// **************************************************************************
// 2. การเชื่อมต่อฐานข้อมูลและดึงข้อมูล
// **************************************************************************
try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $total_records_stmt = $conn->query("SELECT COUNT(*) FROM stones");
    $total_records = $total_records_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);
    
    $current_page = $current_page > $total_pages ? $total_pages : $current_page;
    $start_from = ($current_page - 1) * $records_per_page;

    $stmt = $conn->prepare("SELECT id, thai_name, english_name, description FROM stones LIMIT :start_from, :records_per_page");
    $stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
    $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $stones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แคตตาล็อกหินมงคล DeepStone</title>
    <link rel="stylesheet" href="style.css"> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</head>
<body>
    <div id="container">
        <header>
            <h1>💎 แคตตาล็อกหินมงคล DeepStone 💎</h1>
            <p>แหล่งรวมข้อมูลหินมงคล <?php echo $total_records; ?> ชนิด</p>
        </header>

        <section id="search-area">
            <h2>🔍 ค้นหาหินตามเงื่อนไข</h2>
            <p>เว้นพื้นที่สำหรับฟังก์ชันการค้นหาและกรองข้อมูล</p>
        </section>

        <hr>

        <section id="stone-table-area">
            <h2>📜 รายการหินมงคล</h2>
            
            <div class="controls">
                <label for="per_page">รายการต่อหน้า:</label>
                <select id="per_page" onchange="window.location.href='index.php?page=1&per_page=' + this.value;">
                    <option value="10" <?php echo ($records_per_page == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="20" <?php echo ($records_per_page == 20) ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo ($records_per_page == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo ($records_per_page == 100) ? 'selected' : ''; ?>>100</option>
                </select>
                
                <span style="margin-left: 20px;">
                    <label>ส่งออกข้อมูล (ทั้งหมด):</label>
                    <button class="export-all-btn text-export" data-format="txt">TXT All</button>
                    <button class="export-all-btn pdf-export" data-format="pdf">PDF All</button>
                </span>
            </div>

            <table id="stone-list">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">ชื่อไทย</th>
                        <th width="20%">ชื่ออังกฤษ</th>
                        <th width="35%">คำอธิบายย่อ</th>
                        <th width="20%">ข้อมูลเพิ่มเติม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stones as $stone): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stone['id']); ?></td>
                        <td><?php echo htmlspecialchars($stone['thai_name']); ?></td>
                        <td><?php echo htmlspecialchars($stone['english_name']); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr($stone['description'], 0, 100, 'UTF-8') . '...'); ?></td>
                        <td class="action-buttons">
                            <button class="detail-btn" data-id="<?php echo $stone['id']; ?>">รายละเอียด</button>
                            
                            <a class="action-link g-btn" 
                               href="https://www.google.com/search?q=สร้อยข้อมูลหินมงคล+<?php echo urlencode($stone['english_name']); ?>&tbm=isch" 
                               target="_blank" title="ค้นหารูปภาพใน Google">G</a>
                            
                            <a class="action-link e-btn" 
                               href="https://www.etsy.com/search?q=<?php echo urlencode($stone['english_name']); ?>" 
                               target="_blank" title="ค้นหาสินค้าหินใน Etsy">E</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php 
            $max_links = 5; 
            echo '<div class="pagination">';

            if ($current_page > 1) {
                echo '<a class="nav-arrow" href="index.php?page=' . ($current_page - 1) . '&per_page=' . $records_per_page . '"><span class="arrow">«</span> หน้าก่อนหน้า</a>';
            }

            $start = max(1, $current_page - floor($max_links / 2));
            $end = min($total_pages, $start + $max_links - 1);

            if ($end == $total_pages) {
                $start = max(1, $total_pages - $max_links + 1);
            }
            
            if ($start > 1) {
                echo '<a href="index.php?page=1&per_page=' . $records_per_page . '">1</a>';
                if ($start > 2) {
                    echo '<span class="dots">...</span>';
                }
            }

            for ($i = $start; $i <= $end; $i++) {
                $active = ($i == $current_page) ? 'active' : '';
                echo '<a class="' . $active . '" href="index.php?page=' . $i . '&per_page=' . $records_per_page . '">' . $i . '</a>';
            }

            if ($end < $total_pages) {
                if ($end < $total_pages - 1) {
                    echo '<span class="dots">...</span>';
                }
                echo '<a href="index.php?page=' . $total_pages . '&per_page=' . $records_per_page . '">' . $total_pages . '</a>';
            }

            if ($current_page < $total_pages) {
                echo '<a class="nav-arrow" href="index.php?page=' . ($current_page + 1) . '&per_page=' . $records_per_page . '">หน้าถัดไป <span class="arrow">»</span></a>';
            }

            echo '</div>';
            ?>
        </section>

    </div>
    
    <div id="stone-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="modal-body-content">
                กำลังโหลด...
            </div>
        </div>
    </div>

    <script src="main.js"></script>
</body>
</html>