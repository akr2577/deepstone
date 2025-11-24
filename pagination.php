<?php 
// C:\xampp\htdocs\deepstone\pagination.php
// ใช้ตัวแปร $current_page, $total_pages, $records_per_page จาก index.php

$max_links = 5; // จำนวนลิงก์ตัวเลขสูงสุดที่ต้องการแสดง

echo '<div class="pagination">';

// 1. ปุ่ม "หน้าก่อนหน้า"
if ($current_page > 1) {
    echo '<a class="nav-arrow" href="index.php?page=' . ($current_page - 1) . '&per_page=' . $records_per_page . '"><span class="arrow">«</span> หน้าก่อนหน้า</a>';
}

// 2. แสดงลิงก์หน้า
// กำหนดจุดเริ่มต้นและจุดสิ้นสุดของลิงก์ตัวเลข
$start = max(1, $current_page - floor($max_links / 2));
$end = min($total_pages, $start + $max_links - 1);

// ปรับ start ใหม่ถ้า end ไปติดขอบขวา
if ($end == $total_pages) {
    $start = max(1, $total_pages - $max_links + 1);
}

// แสดงหน้าแรก (ถ้าไม่อยู่ใกล้)
if ($start > 1) {
    echo '<a href="index.php?page=1&per_page=' . $records_per_page . '">1</a>';
    if ($start > 2) {
        echo '<span class="dots">...</span>';
    }
}

// แสดงลิงก์หลัก
for ($i = $start; $i <= $end; $i++) {
    $active = ($i == $current_page) ? 'active' : '';
    echo '<a class="' . $active . '" href="index.php?page=' . $i . '&per_page=' . $records_per_page . '">' . $i . '</a>';
}

// แสดงหน้าสุดท้าย (ถ้าไม่อยู่ใกล้)
if ($end < $total_pages) {
    if ($end < $total_pages - 1) {
        echo '<span class="dots">...</span>';
    }
    echo '<a href="index.php?page=' . $total_pages . '&per_page=' . $records_per_page . '">' . $total_pages . '</a>';
}


// 3. ปุ่ม "หน้าถัดไป"
if ($current_page < $total_pages) {
    echo '<a class="nav-arrow" href="index.php?page=' . ($current_page + 1) . '&per_page=' . $records_per_page . '">หน้าถัดไป <span class="arrow">»</span></a>';
}

echo '</div>';
?>