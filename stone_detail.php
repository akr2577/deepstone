<?php
// C:\xampp\htdocs\deepstone\stone_detail.php - แสดงรายละเอียดหินและข้อมูลเชื่อมโยงครบถ้วน (พร้อมสัญลักษณ์)

require_once 'db_config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ ไม่พบรหัสหินที่ต้องการ");
}

$stone_id = (int)$_GET['id'];

// **************************************************************************
// 1. ฟังก์ชันสำหรับแปลงชื่อราศีเป็นสัญลักษณ์ Unicode (Western Zodiacs)
// **************************************************************************
function getZodiacSymbol($name) {
    $symbols = [
        'เมษ' => '♈', 'Aries' => '♈',
        'พฤษภ' => '♉', 'Taurus' => '♉',
        'เมถุน' => '♊', 'Gemini' => '♊',
        'กรกฎ' => '♋', 'Cancer' => '♋',
        'สิงห์' => '♌', 'Leo' => '♌',
        'กันย์' => '♍', 'Virgo' => '♍',
        'ตุลย์' => '♎', 'Libra' => '♎',
        'พิจิก' => '♏', 'Scorpio' => '♏',
        'ธนู' => '♐', 'Sagittarius' => '♐',
        'มังกร' => '♑', 'Capricorn' => '♑',
        'กุมภ์' => '♒', 'Aquarius' => '♒',
        'มีน' => '♓', 'Pisces' => '♓',
    ];
    // ใช้ชื่อภาษาไทยหรืออังกฤษเพื่อหา Symbol
    return $symbols[$name] ?? '';
}

// **************************************************************************
// 2. ฟังก์ชันสำหรับแปลงชื่อปีนักษัตรเป็นสัญลักษณ์ Unicode (Thai Zodiacs)
// **************************************************************************
function getTzodiacSymbol($name) {
    $symbols = [
        'ชวด' => '🐭', 'Rat' => '🐭',
        'ฉลู' => '🐮', 'Ox' => '🐮',
        'ขาล' => '🐯', 'Tiger' => '🐯',
        'เถาะ' => '🐰', 'Rabbit' => '🐰',
        'มะโรง' => '🐲', 'Dragon' => '🐲',
        'มะเส็ง' => '🐍', 'Snake' => '🐍',
        'มะเมีย' => '🐴', 'Horse' => '🐴',
        'มะแม' => '🐐', 'Goat' => '🐐',
        'วอก' => '🐒', 'Monkey' => '🐒',
        'ระกา' => '🐔', 'Rooster' => '🐔',
        'จอ' => '🐕', 'Dog' => '🐕',
        'กุน' => '🐖', 'Pig' => '🐖',
    ];
    // ใช้ชื่อภาษาไทยหรืออังกฤษเพื่อหา Symbol
    return $symbols[$name] ?? '';
}


try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. ดึงข้อมูลหลัก (Stones)
    $stmt_main = $conn->prepare("SELECT * FROM stones WHERE id = ?");
    $stmt_main->execute([$stone_id]);
    $stone = $stmt_main->fetch(PDO::FETCH_ASSOC);

    if (!$stone) {
        die("❌ ไม่พบข้อมูลหิน ID: " . $stone_id);
    }
    
    // **************************************************************************
    // 3. ดึงข้อมูลเชื่อมโยง (ครบถ้วน)
    // **************************************************************************

    // A. Cleansing
    $stmt_cleansing = $conn->prepare("
        SELECT lc.name_th, lc.auspice_detail_th, lc.description
        FROM stone_map_cleansing smc JOIN lookup_cleansing lc ON smc.cleansing_id = lc.id
        WHERE smc.stone_id = ?
    ");
    $stmt_cleansing->execute([$stone_id]);
    $cleansing_methods = $stmt_cleansing->fetchAll(PDO::FETCH_ASSOC);
    
    // B. Chakra
    $stmt_chakra = $conn->prepare("
        SELECT lc.name_th, lc.color, lc.location, lc.auspice_detail_th, lc.logo
        FROM stone_map_chakra smc JOIN lookup_chakra lc ON smc.chakra_id = lc.id
        WHERE smc.stone_id = ?
    ");
    $stmt_chakra->execute([$stone_id]);
    $chakras = $stmt_chakra->fetchAll(PDO::FETCH_ASSOC);
    
    // C. Days
    $stmt_days = $conn->prepare("
        SELECT ld.name, ld.lucky_color, ld.unlucky_color
        FROM stone_map_days smd JOIN lookup_days ld ON smd.day_id = ld.id
        WHERE smd.stone_id = ?
    ");
    $stmt_days->execute([$stone_id]);
    $days = $stmt_days->fetchAll(PDO::FETCH_ASSOC);
    
    // D. Months
    $stmt_months = $conn->prepare("
        SELECT lm.name
        FROM stone_map_months smm JOIN lookup_months lm ON smm.month_id = lm.id
        WHERE smm.stone_id = ?
    ");
    $stmt_months->execute([$stone_id]);
    $months = $stmt_months->fetchAll(PDO::FETCH_COLUMN);

    // E. Thai Zodiacs
    $stmt_tzodiacs = $conn->prepare("SELECT lt.name FROM stone_map_tzodiacs smt JOIN lookup_tzodiacs lt ON smt.tzodiac_id = lt.id WHERE smt.stone_id = ?");
    $stmt_tzodiacs->execute([$stone_id]);
    $tzodiacs = $stmt_tzodiacs->fetchAll(PDO::FETCH_COLUMN);

    // F. Western Zodiacs
    $stmt_ezodiacs = $conn->prepare("SELECT le.name FROM stone_map_ezodiacs sme JOIN lookup_ezodiacs le ON sme.ezodiac_id = le.id WHERE sme.stone_id = ?");
    $stmt_ezodiacs->execute([$stone_id]);
    $ezodiacs = $stmt_ezodiacs->fetchAll(PDO::FETCH_COLUMN);
    
    // G. Colors and Element 
    $stmt_colors = $conn->prepare("
        SELECT lc.name, lc.hex_code 
        FROM stone_map_colors smc JOIN lookup_colors lc ON smc.color_id = lc.id
        WHERE smc.stone_id = ?
    ");
    $stmt_colors->execute([$stone_id]);
    $colors_data = $stmt_colors->fetchAll(PDO::FETCH_ASSOC);

    $element_data = ['name_th' => '', 'description' => ''];
    if ($stone['element_id'] > 0) {
        $stmt_element = $conn->prepare("SELECT name_th, description FROM lookup_element WHERE id = ?");
        $stmt_element->execute([$stone['element_id']]);
        $element_data = $stmt_element->fetch(PDO::FETCH_ASSOC) ?: $element_data;
    }
    
    // H. Groups 
    $stmt_groups = $conn->prepare("
        SELECT lg.name, lg.description
        FROM stone_map_groups smg JOIN lookup_groups lg ON smg.group_id = lg.id
        WHERE smg.stone_id = ?
    ");
    $stmt_groups->execute([$stone_id]);
    $groups_data = $stmt_groups->fetchAll(PDO::FETCH_ASSOC);

    // I. Rarity, J. Price Range, L. Usage
    $stmt_rarity = $conn->prepare("SELECT name FROM lookup_rarity WHERE id = ?");
    $stmt_rarity->execute([$stone['rarity']]);
    $rarity_name = $stmt_rarity->fetchColumn() ?: '- ไม่พบข้อมูล -';

    $stmt_price = $conn->prepare("SELECT name FROM lookup_price_range WHERE id = ?");
    $stmt_price->execute([$stone['price_range']]);
    $price_name = $stmt_price->fetchColumn() ?: '- ไม่พบข้อมูล -';
    
    $stmt_usage = $conn->prepare("
        SELECT lu.name_th, lu.auspice_detail_th
        FROM stone_map_usage smu JOIN lookup_usage lu ON smu.usage_id = lu.id
        WHERE smu.stone_id = ?
    ");
    $stmt_usage->execute([$stone_id]);
    $usages_data = $stmt_usage->fetchAll(PDO::FETCH_ASSOC);
    
    // **************************************************************************
    // 4. แสดงผลในรูปแบบ HTML สำหรับ Pop-up
    // **************************************************************************
    
    function getColorSwatch($hex) {
        return "<span style='display: inline-block; width: 15px; height: 15px; margin-right: 5px; background-color: " . htmlspecialchars($hex) . "; border: 1px solid #ccc; vertical-align: middle;'></span>";
    }

    echo "<h3>" . htmlspecialchars($stone['thai_name']) . " (" . htmlspecialchars($stone['english_name']) . ")</h3>";
    
    // ปุ่ม Export เฉพาะรายการนี้ (TXT, PDF)
    echo '<div class="modal-export-buttons">';
    echo '<label style="font-size: 14px;">ส่งออกข้อมูลหินนี้:</label>';
    echo ' <button class="export-btn-modal text-export" data-id="' . $stone_id . '" data-format="txt">TXT</button>';
    echo ' <button class="export-btn-modal pdf-export" data-id="' . $stone_id . '" data-format="pdf">PDF</button>';
    echo '</div>';
    
    echo "<hr>";
    
    // *********** ส่วนรายละเอียดทั่วไป ***********
    echo "<h4>รายละเอียดทั่วไปและคุณสมบัติ:</h4>";
    echo "<ul>";
    echo "<li><strong>ชื่ออื่นๆ:</strong> " . htmlspecialchars($stone['other_name']) . "</li>";
    echo "<li><strong>กลุ่มมงคล:</strong> " . htmlspecialchars(implode(', ', array_column($groups_data, 'name'))) . "</li>";
    
    // ธาตุ (พร้อมคำอธิบาย)
    echo "<li><strong>ธาตุ:</strong> " . htmlspecialchars($element_data['name_th']);
    if (!empty($element_data['description'])) {
        echo " <em>(" . htmlspecialchars($element_data['description']) . ")</em>";
    }
    echo "</li>";
    
    // สี (พร้อม Hex Code)
    if (!empty($colors_data)) {
        echo "<li><strong>สี:</strong> ";
        $color_list = [];
        foreach ($colors_data as $color) {
            $color_list[] = getColorSwatch($color['hex_code']) . htmlspecialchars($color['name']);
        }
        echo implode(', ', $color_list) . "</li>";
    }
    
    // เลขมงคล (พร้อมรายละเอียด)
    echo "<li><strong>เลขมงคล (Numerology):</strong> " . htmlspecialchars($stone['numerology']);
    $numerology_values = explode(',', $stone['numerology']);
    $clean_num_values = array_map('trim', $numerology_values);
    $clean_num_values = array_filter($clean_num_values, 'is_numeric'); 
    
    if (!empty($clean_num_values)) {
        $num_list_str = implode(',', $clean_num_values);
        $stmt_num = $conn->query("SELECT number_value, auspice_detail_th FROM lookup_numerology WHERE number_value IN ({$num_list_str})");
        $numerology_details = $stmt_num->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($numerology_details)) {
             echo "<ul>";
             foreach($numerology_details as $num) {
                 echo "<li>* [เลข " . htmlspecialchars($num['number_value']) . "]: " . htmlspecialchars($num['auspice_detail_th']) . "</li>";
             }
             echo "</ul>";
        }
    }
    echo "</li>";
    echo "</ul>";


    // *********** ส่วนคุณสมบัติกายภาพ/ตลาด ***********
    echo "<h4>คุณสมบัติทางกายภาพและตลาด:</h4>";
    echo "<ul>";
    echo "<li><strong>ความแข็ง (Shardness):</strong> " . htmlspecialchars($stone['shardness']) . "/5</li>";
    echo "<li><strong>พลังงานโดยรวม:</strong> " . htmlspecialchars($stone['spower']) . "/4</li>";
    echo "<li><strong>ระดับความหายาก:</strong> " . htmlspecialchars($rarity_name) . "</li>"; 
    echo "<li><strong>ระดับราคา:</strong> " . htmlspecialchars($price_name) . "</li>";      
    echo "<li><strong>ต้นกำเนิด (พบครั้งแรก):</strong> " . htmlspecialchars($stone['sborn']) . "</li>"; 
    echo "<li><strong>แหล่งผลิตปัจจุบัน:</strong> " . htmlspecialchars($stone['snowmake']) . "</li>"; 
    echo "</ul>";


    // *********** ส่วนความเหมาะสม (วัน/เดือน/ราศี/นักษัตร) ***********
    echo "<h4>ความเหมาะสมตามวันและดวงชะตา:</h4>";
    echo "<ul>";
    
    // วันที่เหมาะ (พร้อมสีมงคล/อัปมงคล)
    if (!empty($days)) {
        echo "<li><strong>วันที่เหมาะสม:</strong> ";
        $day_list = [];
        foreach ($days as $day) {
             $day_list[] = "<strong>" . htmlspecialchars($day['name']) . "</strong> (มงคล: " . htmlspecialchars($day['lucky_color']) . " | อัปมงคล: " . htmlspecialchars($day['unlucky_color']) . ")";
        }
        echo implode('; ', $day_list) . "</li>";
    } else {
         echo "<li><strong>วันที่เหมาะสม:</strong> - ไม่พบข้อมูล -</li>";
    }

    // เดือนที่เหมาะ
    echo "<li><strong>เดือนที่เหมาะ:</strong> " . (empty($months) ? '- ไม่พบข้อมูล -' : htmlspecialchars(implode(', ', $months))) . "</li>";
    
    // ราศี (พร้อมสัญลักษณ์ Unicode)
    if (!empty($ezodiacs)) {
        echo "<li><strong>ราศีสากลที่เหมาะ:</strong> ";
        $zodiac_list = [];
        foreach ($ezodiacs as $zodiac) {
            $zodiac_list[] = getZodiacSymbol($zodiac) . " " . htmlspecialchars($zodiac);
        }
        echo implode(', ', $zodiac_list) . "</li>";
    } else {
        echo "<li><strong>ราศีสากลที่เหมาะ:</strong> - ไม่พบข้อมูล -</li>";
    }
    
    // ปีนักษัตร (พร้อมสัญลักษณ์ Unicode)
    if (!empty($tzodiacs)) {
        echo "<li><strong>ปีนักษัตรที่เหมาะ:</strong> ";
        $zodiac_list = [];
        foreach ($tzodiacs as $zodiac) {
            $zodiac_list[] = getTzodiacSymbol($zodiac) . " " . htmlspecialchars($zodiac);
        }
        echo implode(', ', $zodiac_list) . "</li>";
    } else {
        echo "<li><strong>ปีนักษัตรที่เหมาะ:</strong> - ไม่พบข้อมูล -</li>";
    }
    
    echo "</ul>";

    // *********** ส่วนกลุ่มมงคล ***********
    if (!empty($groups_data)) {
        echo "<h4>กลุ่มมงคลที่สนับสนุน:</h4>";
        echo "<ul>";
        foreach ($groups_data as $group) {
            echo "<li><strong>" . htmlspecialchars($group['name']) . "</strong>: " . htmlspecialchars($group['description']) . "</li>";
        }
        echo "</ul>";
    }
    
    // *********** ส่วนรายละเอียดคำอธิบาย ***********
    echo "<h4>คำอธิบายโดยละเอียด:</h4>";
    echo "<p>" . nl2br(htmlspecialchars($stone['description'])) . "</p>";

    // การใช้งาน
    if (!empty($usages_data)) {
        echo "<h4>วิธีการใช้งานหลัก:</h4>";
        echo "<ul>";
        foreach ($usages_data as $usage) {
            echo "<li><strong>" . htmlspecialchars($usage['name_th']) . "</strong>: " . htmlspecialchars($usage['auspice_detail_th']) . "</li>";
        }
        echo "</ul>";
    }
    
    // ประวัติศาสตร์
    if (!empty($stone['shistory'])) {
        echo "<h4>ประวัติ:</h4>";
        echo "<p>" . nl2br(htmlspecialchars($stone['shistory'])) . "</p>";
    }
    
    // จักระ
    if (!empty($chakras)) {
        echo "<h4>จักระที่เกี่ยวข้อง:</h4>";
        echo "<ul>";
        foreach ($chakras as $chakra) {
            echo "<li><span style='color: " . htmlspecialchars($chakra['color']) . "; font-weight: bold;'>&#x25CF;</span> <strong>" . htmlspecialchars($chakra['name_th']) . "</strong> (ตำแหน่ง: " . htmlspecialchars($chakra['location']) . ")<br> &nbsp; &nbsp; - มงคลด้าน: " . htmlspecialchars($chakra['auspice_detail_th']) . "</li>";
        }
        echo "</ul>";
    }
    
    // วิธีการล้าง
    if (!empty($cleansing_methods)) {
        echo "<h4>วิธีการล้างและชาร์จพลังงาน:</h4>";
        echo "<ul>";
        foreach ($cleansing_methods as $method) {
            echo "<li><strong>" . htmlspecialchars($method['name_th']) . "</strong>: " . htmlspecialchars($method['auspice_detail_th']) . "</li>";
        }
        echo "</ul>";
    }

    // วิธีสังเกต
    if (!empty($stone['sobserv'])) {
        echo "<h4>วิธีสังเกตของแท้/ปลอม:</h4>";
        echo "<p>" . nl2br(htmlspecialchars($stone['sobserv'])) . "</p>";
    }

} catch(PDOException $e) {
    // แสดงข้อความผิดพลาด SQL เพื่อ Debug ใน Pop-up
    die("❌ Error in stone_detail.php: " . $e->getMessage());
}
?>