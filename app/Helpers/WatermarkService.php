<?php

declare(strict_types=1);

// Nạp FPDF và FPDI nếu tồn tại
if (file_exists(dirname(__DIR__, 2) . '/libs/fpdf/fpdf.php')) {
    require_once dirname(__DIR__, 2) . '/libs/fpdf/fpdf.php';
}
if (file_exists(dirname(__DIR__, 2) . '/libs/fpdi/src/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/libs/fpdi/src/autoload.php';
}

/**
 * Lớp con hỗ trợ xoay chữ Watermark trên từng trang PDF (kế thừa FPDI hoặc FPDF)
 */
if (!class_exists('CreonoWatermarkPdf')) {
    if (class_exists('setasign\Fpdi\Fpdi')) {
        class CreonoWatermarkPdf extends \setasign\Fpdi\Fpdi
        {
            public function rotatedText(float $x, float $y, string $txt, float $angle): void
            {
                $rad = deg2rad($angle);
                $c = cos($rad);
                $s = sin($rad);
                $cx = $x * $this->k;
                $cy = ($this->h - $y) * $this->k;
                $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                $this->Text($x, $y, $txt);
                $this->_out('Q');
            }
        }
    } elseif (class_exists('FPDF')) {
        class CreonoWatermarkPdf extends \FPDF
        {
            public function rotatedText(float $x, float $y, string $txt, float $angle): void
            {
                $rad = deg2rad($angle);
                $c = cos($rad);
                $s = sin($rad);
                $cx = $x * $this->k;
                $cy = ($this->h - $y) * $this->k;
                $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                $this->Text($x, $y, $txt);
                $this->_out('Q');
            }
        }
    }
}

/**
 * Service xử lý đóng dấu Watermark cho Ảnh và File PDF trên nền tảng Creono
 */
class WatermarkService
{
    /**
     * Kiểm tra thư viện FPDI (đọc & sửa đổi file PDF có sẵn)
     */
    public static function hasFpdi(): bool
    {
        return class_exists('setasign\Fpdi\Fpdi') && class_exists('CreonoWatermarkPdf') && is_subclass_of('CreonoWatermarkPdf', 'setasign\Fpdi\Fpdi');
    }

    /**
     * Kiểm tra thư viện FPDF (sinh file PDF mới)
     */
    public static function hasFpdf(): bool
    {
        return class_exists('FPDF') || class_exists('CreonoWatermarkPdf');
    }

    /**
     * Kiểm tra thư viện đồ họa PHP GD
     */
    public static function hasGd(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }
    /**
     * Đường dẫn font mặc định trên hệ điều hành Windows / Linux
     */
    private static function getFontPath(): ?string
    {
        $fontCandidates = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            'C:/Windows/Fonts/tahoma.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            __DIR__ . '/fonts/arial.ttf'
        ];

        foreach ($fontCandidates as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }
        return null;
    }

    /**
     * Loại bỏ dấu tiếng Việt để xuất văn bản an toàn không lỗi font trên FPDF
     */
    public static function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];

        foreach ($accents as $nonAccent => $accentPattern) {
            $str = preg_replace("/($accentPattern)/u", $nonAccent, $str) ?? $str;
        }

        return $str;
    }

    /**
     * Đóng dấu Watermark cho Ảnh (JPEG, PNG, GIF, WebP)
     *
     * @param string $sourcePath Đường dẫn file ảnh gốc
     * @param string $destPath   Đường dẫn lưu file ảnh đã đóng dấu
     * @param string $text       Văn bản watermark chính (Ví dụ: "CREONO.VN")
     * @param array  $options    Các tùy chọn (type, subText, opacity, fontSize, angle, color)
     * @return bool
     */
    public static function applyImageWatermark(
        string $sourcePath,
        string $destPath,
        string $text = 'CRENO.VN SHOP',
        array $options = []
    ): bool {
        if (!file_exists($sourcePath) || !extension_loaded('gd')) {
            return false;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'] ?? '';
        $srcImage = null;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $srcImage = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null;
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$srcImage) {
            return false;
        }

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        // Tạo ảnh đích hỗ trợ kênh Alpha
        $destImage = imagecreatetruecolor($width, $height);
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);

        // Copy ảnh gốc sang ảnh đích
        imagecopy($destImage, $srcImage, 0, 0, 0, 0, $width, $height);
        imagealphablending($destImage, true);

        // Cấu hình tham số
        $type      = $options['type'] ?? 'diagonal_repeat'; // 'diagonal_repeat', 'center', 'bottom_right'
        $subText   = $options['subText'] ?? '';
        $opacity   = max(5, min(100, (int)($options['opacity'] ?? ($type === 'diagonal_repeat' ? 28 : 70))));
        $fontSize  = (int)($options['fontSize'] ?? max(14, (int)($width / 28)));
        $angle     = (int)($options['angle'] ?? ($type === 'diagonal_repeat' ? 32 : 0));
        $fontPath  = self::getFontPath();

        // Chuyển đổi % opacity thành GD alpha (0: opaque, 127: transparent)
        $gdAlpha       = (int)round(127 - ($opacity / 100 * 127));
        $gdAlphaShadow = (int)round(127 - (($opacity * 0.6) / 100 * 127));

        // Màu chữ (Trắng có viền/đổ bóng xám nhẹ để nổi trên mọi nền)
        $textColor   = imagecolorallocatealpha($destImage, 255, 255, 255, $gdAlpha);
        $shadowColor = imagecolorallocatealpha($destImage, 30, 30, 30, $gdAlphaShadow);

        $displayText = $subText !== '' ? "{$text} • {$subText}" : $text;

        if ($type === 'diagonal_repeat' && $fontPath) {
            // Lặp watermark chéo dạng lưới phủ khắp ảnh (Chống screenshot / copy trộm)
            $stepX = max(180, (int)($fontSize * 14));
            $stepY = max(120, (int)($fontSize * 7));

            for ($x = -$width; $x < $width * 2; $x += $stepX) {
                for ($y = -$height; $y < $height * 2; $y += $stepY) {
                    // Đổ bóng chữ
                    imagettftext($destImage, $fontSize, $angle, $x + 2, $y + 2, $shadowColor, $fontPath, $displayText);
                    // Chữ chính
                    imagettftext($destImage, $fontSize, $angle, $x, $y, $textColor, $fontPath, $displayText);
                }
            }
        } elseif ($type === 'center' && $fontPath) {
            $lines = [];
            $lines[] = ['text' => $text, 'size' => $fontSize];
            if ($subText !== '') {
                $lines[] = ['text' => $subText, 'size' => max(12, (int)($fontSize * 0.7))];
            }

            // Calculate total height of all lines
            $lineSpacing = (int)($fontSize * 1.4);
            $totalHeight = count($lines) * $lineSpacing;
            $startY = (int)(($height - $totalHeight) / 2) + $fontSize;

            foreach ($lines as $idx => $lineInfo) {
                $currentSize = $lineInfo['size'];
                $currentText = $lineInfo['text'];
                $bbox = imagettfbbox($currentSize, $angle, $fontPath, $currentText);
                $textWidth = abs($bbox[4] - $bbox[0]);
                // If text is wider than 80% image width, scale size down
                if ($textWidth > $width * 0.8 && $textWidth > 0) {
                    $scale = ($width * 0.8) / $textWidth;
                    $currentSize = max(10, (int)($currentSize * $scale));
                    $bbox = imagettfbbox($currentSize, $angle, $fontPath, $currentText);
                    $textWidth = abs($bbox[4] - $bbox[0]);
                }
                $x = (int)(($width - $textWidth) / 2);
                $y = $startY + ($idx * $lineSpacing);

                imagettftext($destImage, $currentSize, $angle, $x + 2, $y + 2, $shadowColor, $fontPath, $currentText);
                imagettftext($destImage, $currentSize, $angle, $x, $y, $textColor, $fontPath, $currentText);
            }
        } elseif ($type === 'bottom_right' && $fontPath) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $displayText);
            $textWidth = abs($bbox[4] - $bbox[0]);
            $x = max(10, $width - $textWidth - 20);
            $y = max(20, $height - 20);

            // Vẽ dải nền tối mờ phía sau để nổi bật
            $bgBadge = imagecolorallocatealpha($destImage, 0, 0, 0, (int)round(127 - (50 / 100 * 127)));
            imagefilledrectangle($destImage, $x - 10, $y - $fontSize - 6, $width - 10, $y + 8, $bgBadge);

            imagettftext($destImage, $fontSize, 0, $x, $y, $textColor, $fontPath, $displayText);
        } else {
            // Fallback khi không có TTF font (sử dụng font hệ thống GD)
            $gdFont = 5;
            $gdAlphaSimple = (int)round(127 - ($opacity / 100 * 127));
            $c = imagecolorallocatealpha($destImage, 255, 255, 255, $gdAlphaSimple);
            $s = imagecolorallocatealpha($destImage, 0, 0, 0, (int)round(127 - ($opacity * 0.5 / 100 * 127)));

            $simpleText = self::removeVietnameseAccents($displayText);
            for ($x = 20; $x < $width; $x += 240) {
                for ($y = 40; $y < $height; $y += 160) {
                    imagestring($destImage, $gdFont, $x + 1, $y + 1, $simpleText, $s);
                    imagestring($destImage, $gdFont, $x, $y, $simpleText, $c);
                }
            }
        }

        // Tạo thư mục đích nếu chưa có
        $targetDir = dirname($destPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Lưu file ảnh với định dạng tương ứng
        $saveSuccess = false;
        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $saveSuccess = imagejpeg($destImage, $destPath, 92);
                break;
            case 'png':
                $saveSuccess = imagepng($destImage, $destPath, 8);
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    $saveSuccess = imagewebp($destImage, $destPath, 90);
                } else {
                    $saveSuccess = imagejpeg($destImage, $destPath, 90);
                }
                break;
            case 'gif':
                $saveSuccess = imagegif($destImage, $destPath);
                break;
            default:
                $saveSuccess = imagejpeg($destImage, $destPath, 92);
                break;
        }

        imagedestroy($srcImage);
        imagedestroy($destImage);

        return $saveSuccess;
    }

    /**
     * Đóng dấu Watermark cho tài liệu PDF
     *
     * @param string $sourcePath Đường dẫn file PDF gốc
     * @param string $destPath   Đường dẫn lưu file PDF sau khi đóng dấu
     * @param string $text       Văn bản watermark chính
     * @param array  $options    Các tùy chọn (subText, fontSize, angle, footerText)
     * @return bool
     */
    public static function applyPdfWatermark(
        string $sourcePath,
        string $destPath,
        string $text = 'CRENO.VN SHOP',
        array $options = []
    ): bool {
        if (!file_exists($sourcePath)) {
            return false;
        }

        if (!self::hasFpdi()) {
            return false;
        }

        try {
            $pdf = new CreonoWatermarkPdf();
            $pageCount = $pdf->setSourceFile($sourcePath);

            $safeText = strtoupper(self::removeVietnameseAccents($text));
            $subText  = isset($options['subText']) ? strtoupper(self::removeVietnameseAccents((string)$options['subText'])) : '';
            $fullWatermark = $subText !== '' ? "{$safeText} • {$subText}" : $safeText;

            $footerNotice = $options['footerText'] ?? 'Tai lieu duoc bao ve ban quyen tai Creono.vn - Chi dung cho muc dich xem truoc.';
            $safeFooter = self::removeVietnameseAccents((string)$footerNotice);

            $fontSize = (int)($options['fontSize'] ?? 30);
            $angle    = (float)($options['angle'] ?? 45.0);

            $maxPages = isset($options['maxPages']) ? (int)$options['maxPages'] : 0;
            $pagesToProcess = ($maxPages > 0) ? min($pageCount, $maxPages) : $pageCount;

            for ($pageNo = 1; $pageNo <= $pagesToProcess; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Tạo trang mới trùng khớp hướng và kích thước trang gốc
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Thiết lập màu sắc và font chữ Watermark (Màu xám sáng thanh lịch)
                $pdf->SetFont('Arial', 'B', $fontSize);
                $pdf->SetTextColor(210, 210, 210);

                // Tính toán tọa độ trung tâm cho Watermark chéo
                $centerX = max(15.0, (float)($size['width'] * 0.12));
                $centerY = min((float)($size['height'] - 30.0), (float)($size['height'] * 0.72));
                $pdf->rotatedText($centerX, $centerY, $fullWatermark, $angle);

                // Thêm Watermark phụ nếu có cấu hình secondaryText
                if (!empty($options['secondaryText'])) {
                    $secondary = strtoupper(self::removeVietnameseAccents((string)$options['secondaryText']));
                    $pdf->SetFont('Arial', 'B', (int)($fontSize * 0.7));
                    $pdf->SetTextColor(225, 225, 225);
                    $pdf->rotatedText((float)($centerX + 15), (float)($centerY - 80), $secondary, $angle);
                }

                // Thêm dòng chân trang (Footer Notice)
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(140, 140, 140);
                $pdf->SetXY(10, $size['height'] - 12);
                $pdf->Cell(0, 8, $safeFooter, 0, 0, 'C');
            }

            // Nếu tài liệu gốc có nhiều hơn số trang cho phép xem trước, thêm trang khóa nội dung
            if ($maxPages > 0 && $pageCount > $pagesToProcess) {
                $pdf->AddPage('P', [210, 297]); // A4 Portrait
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetTextColor(185, 28, 28);
                $pdf->SetXY(20, 85);
                $pdf->Cell(170, 12, self::removeVietnameseAccents("NOI DUNG TIEP THEO DA BI GIOI HAN XEM TRUOC"), 0, 1, 'C');

                $pdf->SetFont('Arial', '', 12);
                $pdf->SetTextColor(90, 90, 90);
                $pdf->SetXY(20, 105);
                $noticeMsg = "Ban da xem het {$pagesToProcess} tren tong so {$pageCount} trang cua tai lieu.\n\n"
                           . "Cac trang tiep theo da duoc bao ve ban quyen tai Creono.vn.\n\n"
                           . "Vui long mua tai lieu day du tren he thong de mo khoa toan bo noi dung va tai file goc!";
                $pdf->MultiCell(170, 8, self::removeVietnameseAccents($noticeMsg), 0, 'C');

                // Watermark trên trang khóa
                $pdf->SetFont('Arial', 'B', 24);
                $pdf->SetTextColor(220, 220, 220);
                $pdf->rotatedText(25.0, 200.0, $fullWatermark, $angle);

                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(140, 140, 140);
                $pdf->SetXY(10, 285);
                $pdf->Cell(0, 8, $safeFooter, 0, 0, 'C');
            }

            // Đảm bảo thư mục đích tồn tại
            $targetDir = dirname($destPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $pdf->Output('F', $destPath);
            return file_exists($destPath);
        } catch (\Throwable $e) {
            if (function_exists('logError')) {
                logError('Lỗi khi đóng dấu watermark PDF: ' . $e->getMessage(), ['source' => $sourcePath]);
            }
            return false;
        }
    }

    /**
     * Tạo tập hợp hình ảnh bản xem trước 2 trang đầu + trang khóa bằng GD Library
     * Dùng làm giải pháp fallback nhẹ khi hệ thống không có FPDF/FPDI hoặc file PDF có định dạng nén nâng cao
     *
     * @param string $cacheDir Thư mục cache
     * @param string $filePrefix Tiền tố tên file
     * @param string $title Tiêu đề tài liệu
     * @param string $storeName Tên gian hàng
     * @param int $maxPages Số trang nội dung (mặc định 2)
     * @return array Danh sách đường dẫn file ảnh ['p1' => string, 'p2' => string, 'locked' => string]
     */
    public static function generateGdPreviewPages(
        string $cacheDir,
        string $filePrefix,
        string $title = 'Tai lieu',
        string $storeName = 'Creono',
        int $maxPages = 2
    ): array {
        if (!self::hasGd()) {
            return [];
        }

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $fontPath = self::getFontPath();
        $hasTtf = ($fontPath !== null && file_exists($fontPath));
        $pages = [];

        $cleanTitle = self::removeVietnameseAccents($title);
        $cleanStore = self::removeVietnameseAccents($storeName);

        for ($p = 1; $p <= $maxPages; $p++) {
            $im = imagecreatetruecolor(800, 1130);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagefill($im, 0, 0, $white);

            // Header bar
            $headerBg = imagecolorallocate($im, 248, 250, 252);
            imagefilledrectangle($im, 0, 0, 800, 100, $headerBg);
            $border = imagecolorallocate($im, 226, 232, 240);
            imageline($im, 0, 100, 800, 100, $border);

            $dark = imagecolorallocate($im, 30, 41, 59);
            $gray = imagecolorallocate($im, 100, 116, 139);
            $primary = imagecolorallocate($im, 0, 113, 227);

            if ($hasTtf) {
                $shortTitle = mb_strlen($cleanTitle) > 45 ? mb_substr($cleanTitle, 0, 42) . '...' : $cleanTitle;
                imagettftext($im, 15, 0, 40, 45, $dark, $fontPath, $shortTitle);
                imagettftext($im, 10, 0, 40, 75, $gray, $fontPath, "Gian hang: {$cleanStore} | Ban xem truoc truc tuyen");

                // Badge trang
                $badgeBg = imagecolorallocate($im, 238, 242, 255);
                imagefilledrectangle($im, 40, 130, 280, 165, $badgeBg);
                imagettftext($im, 10, 0, 52, 153, $primary, $fontPath, "TRANG {$p} / {$maxPages} - BAN XEM TRUOC");

                // Tiêu đề chương
                imagettftext($im, 13, 0, 40, 210, $dark, $fontPath, "CHUONG {$p}: NOI DUNG TONG QUAN TAI LIEU");

                $lines = [
                    "Tai lieu nay duoc phan phoi va bao ho ban quyen tai he thong Creono.",
                    "He thong tu dong trich xuat {$maxPages} trang dau de quy khach xem truoc noi dung.",
                    "1. Co so ly thuyet tong quan va muc tieu nghien cuu cua de tai.",
                    "2. Phuong phap khao sat, thu thap so lieu va quy trinh danh gia thuc te.",
                    "3. So do kien truc tong the, mo hinh he thong va thiet ke co so du lieu.",
                    "4. Ket qua thuc nghiem, danh gia chi so hieu nang va huong phat trien.",
                    "",
                    "Quy dinh xem truoc tai lieu:",
                    "- Day la trich doan xem thu da duoc nhung Watermark bao ve ban quyen.",
                    "- Nguoi mua khong the sao chep, in an hoac trich xuat file goc o che do nay.",
                    "- Sau khi thanh toan thanh cong, he thong se mo khoa toan bo noi dung.",
                    "- Ban se nhan duoc file goc dinh dang day du khong co Watermark xem truoc."
                ];

                $y = 250;
                foreach ($lines as $line) {
                    if ($line !== '') {
                        imagettftext($im, 11, 0, 40, $y, $gray, $fontPath, $line);
                    }
                    $y += 32;
                }

                // Watermark chéo
                $wmColor = imagecolorallocatealpha($im, 190, 195, 202, 65);
                imagettftext($im, 34, 45, 110, 750, $wmColor, $fontPath, "CRENO.VN SHOP");

                // Footer
                imageline($im, 40, 1070, 760, 1070, $border);
                imagettftext($im, 9, 0, 40, 1100, $gray, $fontPath, "Trang {$p} | Ban quyen thuoc ve Creono.vn & {$cleanStore} - Chi dung xem truoc");
            } else {
                imagestring($im, 5, 40, 25, $cleanTitle, $dark);
                imagestring($im, 3, 40, 55, "Gian hang: {$cleanStore} | Ban xem truoc", $gray);
                imagestring($im, 4, 40, 130, "TRANG {$p} / {$maxPages} - BAN XEM TRUOC", $primary);
                imagestring($im, 5, 120, 600, "CRENO.VN SHOP", $gray);
            }

            $dest = "{$cacheDir}/{$filePrefix}_p{$p}.png";
            imagepng($im, $dest);
            imagedestroy($im);
            $pages["p{$p}"] = $dest;
        }

        // Trang thứ 3: Trang khóa nội dung (Locked Page)
        $im = imagecreatetruecolor(800, 1130);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $white);

        $redBg = imagecolorallocate($im, 254, 242, 242);
        imagefilledrectangle($im, 40, 220, 760, 560, $redBg);
        $redBorder = imagecolorallocate($im, 254, 202, 202);
        imagerectangle($im, 40, 220, 760, 560, $redBorder);

        $redText = imagecolorallocate($im, 185, 28, 28);
        $dark = imagecolorallocate($im, 30, 41, 59);
        $gray = imagecolorallocate($im, 100, 116, 139);

        if ($hasTtf) {
            imagettftext($im, 18, 0, 100, 290, $redText, $fontPath, "NOI DUNG TIEP THEO DA BI KHOA AN TOAN");
            imagettftext($im, 12, 0, 100, 350, $dark, $fontPath, "Ban da xem het {$maxPages} trang xem truoc cua tai lieu nay.");
            imagettftext($im, 11, 0, 100, 400, $gray, $fontPath, "Cac trang tiep theo da duoc bao ve ban quyen tai Creono.vn.");
            imagettftext($im, 11, 0, 100, 440, $gray, $fontPath, "Vui long mua tai lieu day du de mo khoa toan bo noi dung va tai file goc ve may.");

            $wmColor = imagecolorallocatealpha($im, 190, 195, 202, 65);
            imagettftext($im, 34, 45, 110, 850, $wmColor, $fontPath, "CRENO.VN SHOP");
        } else {
            imagestring($im, 5, 100, 300, "NOI DUNG TIEP THEO DA BI KHOA", $redText);
            imagestring($im, 4, 100, 350, "Vui long mua tai lieu de xem day du noi dung!", $dark);
        }

        $destLocked = "{$cacheDir}/{$filePrefix}_locked.png";
        imagepng($im, $destLocked);
        imagedestroy($im);
        $pages['locked'] = $destLocked;

        return $pages;
    }

    /**
     * Tự động nhận diện loại file và thực hiện đóng dấu Watermark trực tiếp
     *
     * @param string $physicalFilePath Đường dẫn vật lý trên server (vd: C:/xampp/htdocs/creono/public/uploads/...)
     * @param string $storeName         Tên gian hàng người bán
     * @param array  $options           Các tùy chọn nâng cao
     * @return bool
     */
    public static function processUpload(
        string $physicalFilePath,
        string $storeName = 'Creono',
        array $options = []
    ): bool {
        if (!file_exists($physicalFilePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($physicalFilePath, PATHINFO_EXTENSION));
        $brandText = $options['text'] ?? 'CRENO.VN SHOP';

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($extension, $imageExtensions, true)) {
            $imageOptions = array_merge([
                'type' => 'diagonal_repeat',
                'subText' => '',
                'opacity' => 28,
            ], $options);

            // Ghi đè file ảnh sau khi đóng dấu
            $tempPath = $physicalFilePath . '.tmp.' . $extension;
            if (self::applyImageWatermark($physicalFilePath, $tempPath, $brandText, $imageOptions)) {
                if (file_exists($tempPath)) {
                    @unlink($physicalFilePath);
                    rename($tempPath, $physicalFilePath);
                    return true;
                }
            }
            return false;
        }

        if ($extension === 'pdf') {
            $pdfOptions = array_merge([
                'subText' => '',
                'fontSize' => 28,
                'angle' => 45.0
            ], $options);

            // Ghi đè file PDF sau khi đóng dấu
            $tempPath = $physicalFilePath . '.tmp.pdf';
            if (self::applyPdfWatermark($physicalFilePath, $tempPath, 'CRENO.VN SHOP', $pdfOptions)) {
                if (file_exists($tempPath)) {
                    @unlink($physicalFilePath);
                    rename($tempPath, $physicalFilePath);
                    return true;
                }
            }
            return false;
        }

        // Với định dạng khác (ZIP, RAR), giữ nguyên không can thiệp
        return true;
    }

    /**
     * Tạo một file PDF mẫu xem trước có đóng dấu Watermark khi tài liệu gốc chưa có file vật lý
     *
     * @param object $product Đối tượng sản phẩm
     * @param string $destPath Đường dẫn lưu file PDF xem trước
     * @param int    $maxPages Số trang tạo
     * @return bool
     */
    public static function generateSamplePreviewPdf(object $product, string $destPath, int $maxPages = 2): bool
    {
        if (!self::hasFpdf()) {
            return false;
        }

        try {
            $pdf = new CreonoWatermarkPdf();
            $title = self::removeVietnameseAccents((string)($product->title ?? 'Tai lieu'));
            $storeName = self::removeVietnameseAccents((string)($product->store_name ?? 'Creono'));
            $price = number_format((float)($product->price ?? 0), 0, ',', '.') . ' VND';

            $targetDir = dirname($destPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            for ($p = 1; $p <= $maxPages; $p++) {
                $pdf->AddPage('P', [210, 297]); // A4
                $pdf->SetMargins(20, 20, 20);

                // Header
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetTextColor(30, 41, 59);
                $pdf->Cell(0, 10, $title, 0, 1, 'C');

                $pdf->SetFont('Arial', 'I', 10);
                $pdf->SetTextColor(100, 116, 139);
                $pdf->Cell(0, 6, "Gian hang: {$storeName} | Gia niem yet: {$price}", 0, 1, 'C');
                $pdf->Ln(10);

                // Divider
                $pdf->SetDrawColor(226, 232, 240);
                $pdf->Line(20, 38, 190, 38);
                $pdf->Ln(5);

                // Sample Content
                $pdf->SetFont('Arial', '', 11);
                $pdf->SetTextColor(51, 65, 85);

                $sampleText = "TRANG {$p} / BAN XEM TRUOC TAI LIEU (PREVIEW)\n\n"
                            . "Day la ban xem truoc tai lieu tu dong cua he thong Creono duoc nhung Watermark chong sao chep.\n\n"
                            . "Noi dung mau gom cac chuong muc, tieu de va noi dung tom tat cua tai lieu:\n"
                            . "1. Gioi thieu tong quan ve chuyen de nghien cuu.\n"
                            . "2. Co so ly thuyet va cac phuong phap ap dung trong thuc te.\n"
                            . "3. So do kien truc tong the, mo hinh xu ly du lieu va cac bien so.\n"
                            . "4. Phan tich ket qua khao sat va danh gia hieu qua hoat dong.\n\n"
                            . "Luu y danh cho nguoi mua:\n"
                            . "- Ban dang xem ban trich doan xem truoc co gioi han.\n"
                            . "- He thong ngan chan cac hanh vi in an, sao chep va tai xuong trai phep.\n"
                            . "- Sau khi thanh toan thanh cong, ban se duoc cap quyen tai file goc day du voi day du chat luong va dinh dang.";

                $pdf->MultiCell(170, 7, $sampleText, 0, 'L');

                // Watermark trên trang
                $pdf->SetFont('Arial', 'B', 28);
                $pdf->SetTextColor(210, 210, 210);
                $pdf->rotatedText(25.0, 190.0, "CRENO.VN SHOP", 45.0);

                // Footer
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(140, 140, 140);
                $pdf->SetXY(20, 280);
                $pdf->Cell(170, 8, "Trang {$p} | Ban quyen thuoc ve Creono.vn & {$storeName} - Chi xem truoc", 0, 0, 'C');
            }

            // Thêm trang cảnh báo giới hạn (Lock page)
            $pdf->AddPage('P', [210, 297]);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor(185, 28, 28);
            $pdf->SetXY(20, 90);
            $pdf->Cell(170, 12, self::removeVietnameseAccents("NOI DUNG TIEP THEO DA BI GIOI HAN XEM TRUOC"), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 12);
            $pdf->SetTextColor(90, 90, 90);
            $pdf->SetXY(20, 110);
            $lockMsg = "Ban da xem het {$maxPages} trang xem truoc cua tai lieu nay.\n\n"
                     . "Cac phan noi dung con lai da duoc bao ve va tam an.\n\n"
                     . "Vui long mua tai lieu de xem toan bo noi dung va tai file goc!";
            $pdf->MultiCell(170, 8, self::removeVietnameseAccents($lockMsg), 0, 'C');

            $pdf->SetFont('Arial', 'B', 24);
            $pdf->SetTextColor(225, 225, 225);
            $pdf->rotatedText(25.0, 210.0, "CRENO.VN SHOP", 45.0);

            $pdf->Output('F', $destPath);
            return file_exists($destPath);
        } catch (\Throwable $e) {
            if (function_exists('logError')) {
                logError('Lỗi khi tạo PDF mẫu xem trước: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Lấy hoặc sinh file xem trước (PDF/Ảnh) có Watermark cho sản phẩm
     *
     * @param int $productId
     * @param object $product
     * @param object|null $document
     * @param int $maxPages
     * @return array|null ['type' => 'pdf'|'image', 'path' => string, 'mime' => string]
     */
    public static function getOrGeneratePreview(
        int $productId,
        object $product,
        ?object $document = null,
        int $maxPages = 2
    ): ?array {
        $cacheDir = (defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . '/public/') . 'uploads/cache/previews';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $baseDir = defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . '/public/';
        $storeName = (string)($product->store_name ?? 'Creono');

        // 1. Kiểm tra nếu có file document gốc dạng PDF
        if ($document && !empty($document->file_url)) {
            $docPath = $baseDir . ltrim($document->file_url, '/');
            if (file_exists($docPath)) {
                $ext = strtolower(pathinfo($docPath, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $cachePdfPath = "{$cacheDir}/preview_prod_{$productId}.pdf";
                    if (file_exists($cachePdfPath) && filemtime($cachePdfPath) >= filemtime($docPath)) {
                        return ['type' => 'pdf', 'path' => $cachePdfPath, 'mime' => 'application/pdf'];
                    }

                    $success = self::applyPdfWatermark(
                        $docPath,
                        $cachePdfPath,
                        'CRENO.VN SHOP',
                        [
                            'subText' => '',
                            'maxPages' => $maxPages,
                            'fontSize' => 26,
                            'angle' => 45.0,
                        ]
                    );

                    if ($success && file_exists($cachePdfPath)) {
                        return ['type' => 'pdf', 'path' => $cachePdfPath, 'mime' => 'application/pdf'];
                    }

                    // Fallback: nếu FPDI không xử lý được file PDF này (do PDF phiên bản cao hoặc thiếu FPDI),
                    // trả về file PDF gốc kèm cờ fallback để viewer phía client (PDF.js) render 2 trang + watermark canvas
                    return [
                        'type' => 'pdf',
                        'path' => $docPath,
                        'mime' => 'application/pdf',
                        'is_fallback' => true,
                        'watermark' => 'CRENO.VN SHOP',
                        'store_name' => $storeName
                    ];
                }
            }
        }

        // 2. Nếu có ảnh preview_url của sản phẩm
        if (!empty($product->preview_url)) {
            $imgPath = $baseDir . ltrim($product->preview_url, '/');
            if (file_exists($imgPath)) {
                $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
                $cacheImgPath = "{$cacheDir}/preview_prod_{$productId}.{$ext}";

                if (file_exists($cacheImgPath) && filemtime($cacheImgPath) >= filemtime($imgPath)) {
                    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
                    return ['type' => 'image', 'path' => $cacheImgPath, 'mime' => $mime];
                }

                $imgOptions = [
                    'type' => 'diagonal_repeat',
                    'subText' => '',
                    'opacity' => 28,
                ];

                if (self::applyImageWatermark($imgPath, $cacheImgPath, 'CRENO.VN SHOP', $imgOptions)) {
                    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
                    return ['type' => 'image', 'path' => $cacheImgPath, 'mime' => $mime];
                }
            }
        }

        // 3. Fallback PDF mẫu: Dành cho sản phẩm test hoặc chưa có file
        $samplePdfPath = "{$cacheDir}/preview_prod_{$productId}_sample.pdf";
        if (file_exists($samplePdfPath)) {
            return ['type' => 'pdf', 'path' => $samplePdfPath, 'mime' => 'application/pdf'];
        }

        if (self::generateSamplePreviewPdf($product, $samplePdfPath, $maxPages)) {
            return ['type' => 'pdf', 'path' => $samplePdfPath, 'mime' => 'application/pdf'];
        }

        // 4. Fallback tối thượng: Tạo ảnh xem trước qua GD Library
        $gdSample = self::generateGdPreviewPages(
            $cacheDir,
            "preview_prod_{$productId}_sample_gd",
            (string)($product->title ?? 'Tai lieu'),
            $storeName,
            $maxPages
        );
        if (!empty($gdSample['p1']) && file_exists($gdSample['p1'])) {
            return ['type' => 'image', 'path' => $gdSample['p1'], 'mime' => 'image/png', 'all_pages' => $gdSample];
        }

        return null;
    }
}
