<?php
// C:\xampp\htdocs\deepstone\export.php - จัดการการส่งออกเป็น TXT/PDF (รวมข้อมูลทั้งหมด)
require_once 'db_config.php';

if (!isset($_GET['format'])) {
    die("❌ รูปแบบการส่งออกไม่ถูกต้อง");
}

$format = strtolower($_GET['format']);
// ในเวอร์ชันนี้ $stone_id เป็น Null สำหรับปุ่ม "All" 
$stone_id = isset($_GET['id']) && !empty($_GET['id']) ? (int)$_GET['id'] : null;

try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ******************************************************************
    // 1. ดึงข้อมูลหลัก (รายการเดียว หรือทั้งหมด)
    // ******************************************************************
    $sql_base = "SELECT * FROM stones";
    $params = [];
    $filename_suffix = "All_Stones";

    if ($stone_id) {
        $sql_base .= " WHERE id = ?";
        $params[] = $stone_id;
        
        // ดึงชื่อหินมาตั้งชื่อไฟล์
        $stmt_name = $conn->prepare("SELECT english_name FROM stones WHERE id = ?");
        $stmt_name->execute([$stone_id]);
        $stone_name = $stmt_name->fetchColumn();
        $filename_suffix = str_replace(' ', '_', $stone_name) . "_ID{$stone_id}";
    }

    $stmt_base = $conn->prepare($sql_base);
    $stmt_base->execute($params);
    $stones_to_export = $stmt_base->fetchAll(PDO::FETCH_ASSOC);

    if (!$stones_to_export) {
        die("❌ ไม่พบข้อมูลหินที่ต้องการส่งออก");
    }

    $filename = str_replace(' ', '_', $filename_suffix);

    if ($format === 'txt') {
        // ******************************************************************
        // 2. ส่งออกเป็น TEXT FILE (รวมข้อมูลเชื่อมโยงทั้งหมด)
        // ******************************************************************
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
        
        $output = "===== DeepStone Catalog Export ({$filename_suffix}) =====\n\n";

        foreach ($stones_to_export as $stone) {
            $current_stone_id = $stone['id'];
            
            // --- ดึงข้อมูลเสริม (เหมือนใน Pop-up) ---
            
            // Element
            $element_name = '';
            if ($stone['element_id'] > 0) {
                $stmt_element = $conn->prepare("SELECT name_th FROM lookup_element WHERE id = ?");
                $stmt_element->execute([$stone['element_id']]);
                $element_name = $stmt_element->fetchColumn();
            }

            // Colors
            $stmt_colors = $conn->prepare("SELECT lc.name FROM stone_map_colors smc JOIN lookup_colors lc ON smc.color_id = lc.id WHERE smc.stone_id = ?");
            $stmt_colors->execute([$current_stone_id]);
            $colors = $stmt_colors->fetchAll(PDO::FETCH_COLUMN);
            
            // Days (วันเกิด - สีมงคล/อัปมงคล)
            $stmt_days = $conn->prepare("SELECT ld.name, ld.lucky_color, ld.unlucky_color FROM stone_map_days smd JOIN lookup_days ld ON smd.day_id = ld.id WHERE smd.stone_id = ?");
            $stmt_days->execute([$current_stone_id]);
            $days = $stmt_days->fetchAll(PDO::FETCH_ASSOC);

            // Months
            $stmt_months = $conn->prepare("SELECT lm.name FROM stone_map_months smm JOIN lookup_months lm ON smm.month_id = lm.id WHERE smm.stone_id = ?");
            $stmt_months->execute([$current_stone_id]);
            $months = $stmt_months->fetchAll(PDO::FETCH_COLUMN);

            // Thai Zodiacs
            $stmt_tzodiacs = $conn->prepare("SELECT lt.name FROM stone_map_tzodiacs smt JOIN lookup_tzodiacs lt ON smt.tzodiac_id = lt.id WHERE smt.stone_id = ?");
            $stmt_tzodiacs->execute([$current_stone_id]);
            $tzodiacs = $stmt_tzodiacs->fetchAll(PDO::FETCH_COLUMN);

            // Western Zodiacs
            $stmt_ezodiacs = $conn->prepare("SELECT le.name FROM stone_map_ezodiacs sme JOIN lookup_ezodiacs le ON sme.ezodiac_id = le.id WHERE sme.stone_id = ?");
            $stmt_ezodiacs->execute([$current_stone_id]);
            $ezodiacs = $stmt_ezodiacs->fetchAll(PDO::FETCH_COLUMN);

            // Chakra
            $stmt_chakra = $conn->prepare("SELECT lc.name_th, lc.color FROM stone_map_chakra smc JOIN lookup_chakra lc ON smc.chakra_id = lc.id WHERE smc.stone_id = ?");
            $stmt_chakra->execute([$current_stone_id]);
            $chakras = $stmt_chakra->fetchAll(PDO::FETCH_ASSOC);

            // Cleansing
            $stmt_cleansing = $conn->prepare("SELECT lc.name_th FROM stone_map_cleansing smc JOIN lookup_cleansing lc ON smc.cleansing_id = lc.id WHERE smc.stone_id = ?");
            $stmt_cleansing->execute([$current_stone_id]);
            $cleansing_methods = $stmt_cleansing->fetchAll(PDO::FETCH_COLUMN);
            
            // Groups
            $stmt_groups = $conn->prepare("SELECT lg.name FROM stone_map_groups smg JOIN lookup_groups lg ON smg.group_id = lg.id WHERE smg.stone_id = ?");
            $stmt_groups->execute([$current_stone_id]);
            $groups = $stmt_groups->fetchAll(PDO::FETCH_COLUMN);

            // Rarity Name
            $stmt_rarity = $conn->prepare("SELECT name FROM lookup_rarity WHERE id = ?");
            $stmt_rarity->execute([$stone['rarity']]);
            $rarity_name = $stmt_rarity->fetchColumn() ?: '- ไม่พบข้อมูล -';

            // Price Name
            $stmt_price = $conn->prepare("SELECT name FROM lookup_price_range WHERE id = ?");
            $stmt_price->execute([$stone['price_range']]);
            $price_name = $stmt_price->fetchColumn() ?: '- ไม่พบข้อมูล -';

            // --- จัดรูปแบบ Output ---
            $output .= "รหัส: " . $stone['id'] . "\n";
            $output .= "ชื่อ (TH): " . $stone['thai_name'] . "\n";
            $output .= "ชื่อ (EN): " . $stone['english_name'] . "\n";
            $output .= "ชื่ออื่นๆ: " . $stone['other_name'] . "\n";
            $output .= "กลุ่มมงคล: " . implode(', ', $groups) . "\n";
            $output .= "ธาตุ: " . $element_name . "\n";
            $output .= "สี: " . implode(', ', $colors) . "\n";
            $output .= "เลขมงคล: " . $stone['numerology'] . "\n";
            
            if (!empty($days)) {
                $day_list = [];
                foreach ($days as $day) {
                     $day_list[] = "{$day['name']} (มงคล: {$day['lucky_color']} | อัปมงคล: {$day['unlucky_color']})";
                }
                $output .= "วันที่เหมาะสม: " . implode('; ', $day_list) . "\n";
            }
            $output .= "เดือนที่เหมาะ: " . implode(', ', $months) . "\n";
            $output .= "ราศีสากล: " . implode(', ', $ezodiacs) . "\n";
            $output .= "ปีนักษัตร: " . implode(', ', $tzodiacs) . "\n";
            
            $output .= "ความแข็ง (Shardness): " . $stone['shardness'] . "/5\n";
            $output .= "พลังงานโดยรวม: " . $stone['spower'] . "/4\n";
            $output .= "ความหายาก: " . $rarity_name . "\n";
            $output .= "ระดับราคา: " . $price_name . "\n";
            $output .= "ต้นกำเนิด: " . $stone['sborn'] . "\n";
            $output .= "แหล่งผลิตปัจจุบัน: " . $stone['snowmake'] . "\n";

            if (!empty($chakras)) {
                $chakra_list = [];
                foreach ($chakras as $chakra) {
                     $chakra_list[] = "{$chakra['name_th']} (สี: {$chakra['color']})";
                }
                $output .= "จักระที่เกี่ยวข้อง: " . implode('; ', $chakra_list) . "\n";
            }

            $output .= "วิธีการล้าง: " . implode(', ', $cleansing_methods) . "\n";
            
            $output .= "คำอธิบาย: " . strip_tags($stone['description']) . "\n";
            $output .= "ประวัติ: " . strip_tags($stone['shistory']) . "\n";
            $output .= "ข้อควรระวัง: " . $stone['scareful'] . "\n";
            $output .= "วิธีสังเกต: " . strip_tags($stone['sobserv']) . "\n";
            $output .= "-------------------------------------------------------\n\n";
        }
        
        echo $output;
        exit;

    } elseif ($format === 'pdf') {
        // ******************************************************************
        // 3. ส่งออกเป็น PDF FILE (แจ้งเตือน)
        // ******************************************************************
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        echo "PDF File generation for {$filename_suffix} is not implemented. Please install a PHP PDF library (e.g., FPDF, TCPDF) and integrate the code to enable PDF export.";
        exit;
    }

} catch(PDOException $e) {
    die("Error Processing Export: " . $e->getMessage());
}
?>