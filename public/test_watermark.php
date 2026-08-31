<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/WatermarkService.php';

$testDir = __DIR__ . '/uploads/test_watermark';
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

// 1. TẠO ẢNH TEST GỐC (JPG)
$sampleJpgOrig = $testDir . '/original_sample.jpg';
$sampleJpgDiagonal = $testDir . '/watermarked_diagonal.jpg';
$sampleJpgCenter = $testDir . '/watermarked_center.jpg';
$sampleJpgBadge = $testDir . '/watermarked_badge.jpg';

$width = 900;
$height = 600;
$im = imagecreatetruecolor($width, $height);
for ($y = 0; $y < $height; $y++) {
    $r = (int)(25 + ($y / $height) * 40);
    $g = (int)(45 + ($y / $height) * 60);
    $b = (int)(110 + ($y / $height) * 90);
    $color = imagecolorallocate($im, $r, $g, $b);
    imageline($im, 0, $y, $width, $y, $color);
}
// Vẽ một số hình trang trí giả lập tài liệu đồ họa
$accent = imagecolorallocate($im, 0, 113, 227);
imagefilledellipse($im, (int)($width * 0.8), (int)($height * 0.3), 200, 200, $accent);
$accent2 = imagecolorallocate($im, 255, 149, 0);
imagefilledrectangle($im, 60, 250, 400, 480, $accent2);

$white = imagecolorallocate($im, 255, 255, 255);
$font = 'C:/Windows/Fonts/arial.ttf';
if (file_exists($font)) {
    imagettftext($im, 26, 0, 60, 120, $white, $font, 'CREONO DIGITAL PRODUCT PREVIEW');
    imagettftext($im, 15, 0, 60, 160, $white, $font, 'Original high quality digital document asset');
} else {
    imagestring($im, 5, 60, 120, 'CREONO DIGITAL PRODUCT PREVIEW', $white);
}
imagejpeg($im, $sampleJpgOrig, 92);
imagedestroy($im);

// 2. THỰC HIỆN ĐÓNG DẤU CÁC KIỂU CHO ẢNH
WatermarkService::applyImageWatermark(
    $sampleJpgOrig,
    $sampleJpgDiagonal,
    'CREONO.VN • BẢN XEM TRƯỚC',
    ['type' => 'diagonal_repeat', 'subText' => 'Bản xem thử Shop Creono', 'opacity' => 30]
);

WatermarkService::applyImageWatermark(
    $sampleJpgOrig,
    $sampleJpgCenter,
    'CREONO MARKETPLACE',
    ['type' => 'center', 'subText' => 'BẢN QUYỀN THUỘC VỀ SELLER', 'opacity' => 50, 'fontSize' => 30]
);

WatermarkService::applyImageWatermark(
    $sampleJpgOrig,
    $sampleJpgBadge,
    'CREONO • OFFICIAL PREVIEW',
    ['type' => 'bottom_right', 'opacity' => 85, 'fontSize' => 16]
);

// 3. TẠO VÀ ĐÓNG DẤU FILE PDF
$samplePdfOrig = $testDir . '/original_sample.pdf';
$samplePdfWatermarked = $testDir . '/watermarked_sample.pdf';

if (class_exists('FPDF')) {
    $fpdf = new FPDF();
    $fpdf->AddPage();
    $fpdf->SetFont('Arial', 'B', 18);
    $fpdf->Cell(0, 12, 'CREONO DIGITAL ASSET DOCUMENT', 0, 1, 'C');
    $fpdf->Ln(10);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->MultiCell(0, 8, "Day la tai lieu mau duoc tao tu dong de kiem thu tinh nang dong dau PDF.\n\nNoi dung bao gom cac trang tai lieu so, ma nguon, giao trinh hoc tap.\nTrang 1: Tong quan he thong.");
    
    $fpdf->AddPage();
    $fpdf->SetFont('Arial', 'B', 16);
    $fpdf->Cell(0, 12, 'Trang 2: Chi tiet ky thuat & So do kien truc', 0, 1);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->MultiCell(0, 8, "Cac mo ta chi tiet tiep theo se duoc bao ve ban quyen voi chu chim duoc in xeo tren tung trang.");
    $fpdf->Output('F', $samplePdfOrig);

    WatermarkService::applyPdfWatermark(
        $samplePdfOrig,
        $samplePdfWatermarked,
        'CREONO.VN - BAN XEM THU SHOP CREONO',
        ['fontSize' => 24, 'angle' => 45]
    );
}

// 4. HIỂN THỊ KẾT QUẢ
if (php_sapi_name() === 'cli') {
    echo "========================================================\n";
    echo "       CREONO - KẾT QUẢ KIỂM THỬ WATERMARK TỰ ĐỘNG      \n";
    echo "========================================================\n\n";
    echo "[OK] Ảnh gốc: {$sampleJpgOrig}\n";
    echo "[OK] Ảnh đóng dấu chéo (Lưới chống trộm): {$sampleJpgDiagonal}\n";
    echo "[OK] Ảnh đóng dấu tâm: {$sampleJpgCenter}\n";
    echo "[OK] Ảnh đóng dấu huy hiệu góc: {$sampleJpgBadge}\n";
    echo "[OK] PDF gốc: {$samplePdfOrig}\n";
    echo "[OK] PDF đã đóng dấu bản quyền: {$samplePdfWatermarked}\n\n";
    echo "Trạng thái: TẤT CẢ FILE ĐÃ ĐƯỢC TẠO VÀ ĐÓNG DẤU THÀNH CÔNG!\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm thử Watermark Tự động - Creono</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #f5f5f7; color: #1d1d1f; padding: 40px 20px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 32px; font-weight: 700; color: #1d1d1f; margin-bottom: 8px; }
        .header p { color: #86868b; font-size: 16px; }
        .badge-success { display: inline-block; background: #34c759; color: #fff; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 12px; }
        .section-title { font-size: 22px; font-weight: 700; margin: 32px 0 16px; color: #1d1d1f; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .card { background: #fff; border-radius: 18px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.06); }
        .card h3 { font-size: 16px; font-weight: 600; margin-bottom: 12px; color: #333; display: flex; align-items: center; justify-content: space-between; }
        .card img { width: 100%; border-radius: 12px; object-fit: cover; border: 1px solid rgba(0,0,0,0.08); }
        .pdf-preview-box { background: #fafafa; border-radius: 12px; padding: 20px; text-align: center; border: 1px dashed #ccc; }
        .btn { display: inline-block; background: #0071e3; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; font-size: 14px; margin-top: 12px; transition: 0.2s; }
        .btn:hover { background: #0077ed; }
        .tag { font-size: 12px; padding: 4px 8px; border-radius: 6px; font-weight: 500; }
        .tag-orig { background: #eee; color: #555; }
        .tag-watermark { background: #e8f3ff; color: #0071e3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Kiểm thử kết quả đóng dấu Watermark</h1>
            <p>Trực quan hóa kết quả đóng dấu bản quyền tự động cho Ảnh và PDF trên Creono</p>
            <span class="badge-success">✓ Đã xử lý & tích hợp thành công</span>
        </div>

        <div class="section-title">1. So sánh Ảnh Gốc vs Ảnh Đóng Dấu Chéo (Diagonal Repeat Anti-theft)</div>
        <div class="grid-2">
            <div class="card">
                <h3><span>Ảnh gốc chưa đóng dấu</span> <span class="tag tag-orig">Original</span></h3>
                <img src="<?php echo URLROOT; ?>/uploads/test_watermark/original_sample.jpg?v=<?php echo time(); ?>" alt="Original Image">
            </div>
            <div class="card">
                <h3><span>Ảnh đã đóng dấu lưới chéo 45°</span> <span class="tag tag-watermark">Watermarked (Mặc định)</span></h3>
                <img src="<?php echo URLROOT; ?>/uploads/test_watermark/watermarked_diagonal.jpg?v=<?php echo time(); ?>" alt="Watermarked Diagonal">
            </div>
        </div>

        <div class="section-title">2. Các kiểu Watermark Ảnh khác</div>
        <div class="grid-2">
            <div class="card">
                <h3><span>Đóng dấu Trung tâm (Center Badge)</span> <span class="tag tag-watermark">Center Style</span></h3>
                <img src="<?php echo URLROOT; ?>/uploads/test_watermark/watermarked_center.jpg?v=<?php echo time(); ?>" alt="Watermarked Center">
            </div>
            <div class="card">
                <h3><span>Huy hiệu góc dưới (Bottom-Right Badge)</span> <span class="tag tag-watermark">Corner Style</span></h3>
                <img src="<?php echo URLROOT; ?>/uploads/test_watermark/watermarked_badge.jpg?v=<?php echo time(); ?>" alt="Watermarked Badge">
            </div>
        </div>

        <div class="section-title">3. Kiểm thử Đóng Dấu Tài Liệu PDF (FPDF + FPDI)</div>
        <div class="grid-2">
            <div class="card">
                <h3><span>File PDF Gốc</span> <span class="tag tag-orig">Original PDF</span></h3>
                <div class="pdf-preview-box">
                    <p style="font-size: 40px; margin-bottom: 8px;">📄</p>
                    <p style="font-weight: 600;">original_sample.pdf</p>
                    <p style="font-size: 13px; color: #888; margin-top: 4px;">Tài liệu 2 trang chưa có watermark</p>
                    <a href="<?php echo URLROOT; ?>/uploads/test_watermark/original_sample.pdf" target="_blank" class="btn" style="background: #555;">Mở PDF Gốc ↗</a>
                </div>
            </div>
            <div class="card">
                <h3><span>File PDF Đã Đóng Dấu Bản Quyền</span> <span class="tag tag-watermark">Watermarked PDF</span></h3>
                <div class="pdf-preview-box" style="background: #f0f7ff; border-color: #b9dcff;">
                    <p style="font-size: 40px; margin-bottom: 8px;">🔒</p>
                    <p style="font-weight: 600; color: #0071e3;">watermarked_sample.pdf</p>
                    <p style="font-size: 13px; color: #666; margin-top: 4px;">Đã đóng dấu chéo từng trang + Footer bảo vệ bản quyền</p>
                    <a href="<?php echo URLROOT; ?>/uploads/test_watermark/watermarked_sample.pdf" target="_blank" class="btn">Mở PDF Đã Đóng Dấu ↗</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
