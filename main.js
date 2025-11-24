// C:\xampp\htdocs\deepstone\main.js

$(document).ready(function() {
    var modal = $('#stone-modal');
    var span = $('.close-btn');

    // 1. จัดการปุ่ม "รายละเอียด" (Pop-up)
    // ใช้ on('click') เพื่อรองรับการโหลดไดนามิก
    $(document).on('click', '.detail-btn', function() {
        var stoneId = $(this).data('id');
        
        $.ajax({
            url: 'stone_detail.php', 
            type: 'GET',
            data: { id: stoneId },
            beforeSend: function() {
                $('#modal-body-content').html('<div style="text-align:center;">กำลังโหลดข้อมูล...</div>');
            },
            success: function(data) {
                $('#modal-body-content').html(data);
                modal.css('display', 'block');
            },
            error: function(xhr, status, error) {
                $('#modal-body-content').html('<div style="color:red; text-align:center;">เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error + '</div>');
                modal.css('display', 'block');
            }
        });
    });

    // 2. จัดการปุ่ม Export (TXT/PDF) - สำหรับปุ่มทั้งหมดและปุ่มใน Pop-up
    $(document).on('click', '.export-btn-modal, .export-all-btn', function() {
        var stoneId = $(this).data('id') || null; 
        var format = $(this).data('format');

        var exportUrl = 'export.php?format=' + format;

        if (stoneId) {
            // Export รายการเดียว (จาก Pop-up)
            exportUrl += '&id=' + stoneId;
        } 

        if (format === 'pdf') {
            alert('PDF Export: ฟังก์ชันนี้จะต้องมีการติดตั้งไลบรารีเพิ่มเติมเพื่อสร้างไฟล์ PDF ที่ถูกต้อง (ปัจจุบันยังไม่สามารถใช้งานได้)');
            return;
        }

        window.location.href = exportUrl;
    });

    // 3. จัดการการปิด Pop-up
    // เมื่อคลิกปุ่มปิด (x)
    span.on('click', function() {
        modal.css('display', 'none');
    });

    // เมื่อคลิกนอก Pop-up
    $(window).on('click', function(event) {
        if (event.target == modal[0]) {
            modal.css('display', 'none');
        }
    });
});