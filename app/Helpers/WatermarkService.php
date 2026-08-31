<?php

declare(strict_types=1);

// Nạp FPDF và FPDI nếu tồn tại
if (file_exists(dirname(__DIR__, 2) . '/libs/fpdf/fpdf.php')) {
    require_once dirname(__DIR__, 2) . '/libs/fpdf/fpdf.php';
}
if (file_exists(dirname(__DIR__, 2) . '/libs/fpdi/src/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/libs/fpdi/src/autoload.php';
}

use setasign\Fpdi\Fpdi;

/**
 * Lớp con kế thừa FPDI để hỗ trợ xoay chữ Watermark trên từng trang PDF
 */
if (class_exists('setasign\Fpdi\Fpdi')) {
    class CreonoWatermarkPdf extends Fpdi
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

/**
 * Service xử lý đóng dấu Watermark cho Ảnh và File PDF trên nền tảng Creono
 */
class WatermarkService
{
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
        string $text = 'CREONO.VN • BẢN XEM TRƯỚC',
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
        string $text = 'CREONO.VN - BAN XEM THU',
        array $options = []
    ): bool {
        if (!file_exists($sourcePath)) {
            return false;
        }

        if (!class_exists('CreonoWatermarkPdf')) {
            if (function_exists('logError')) {
                logError('Không tìm thấy thư viện CreonoWatermarkPdf (FPDF/FPDI)');
            }
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

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
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
        $brandText = $options['text'] ?? 'CREONO • BẢN XEM TRƯỚC';

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($extension, $imageExtensions, true)) {
            $imageOptions = array_merge([
                'type' => 'diagonal_repeat',
                'subText' => $storeName,
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
                'subText' => $storeName,
                'fontSize' => 28,
                'angle' => 45.0
            ], $options);

            // Ghi đè file PDF sau khi đóng dấu
            $tempPath = $physicalFilePath . '.tmp.pdf';
            if (self::applyPdfWatermark($physicalFilePath, $tempPath, 'CREONO.VN - BAN XEM THU', $pdfOptions)) {
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
}
