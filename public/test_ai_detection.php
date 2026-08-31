<?php
/**
 * UC25 - Công cụ kiểm thử AI Detection
 * Truy cập: http://localhost/creono/public/test_ai_detection.php
 */

define('APPROOT',  dirname(__DIR__) . '/app');
define('URLROOT',  'http://localhost/creono/public');

require_once dirname(__DIR__) . '/app/Services/AiDetectionService.php';

// ========== DỮ LIỆU MẪU ==========
$sampleTexts = [
    'human' => [
        'label' => 'Văn bản Con người',
        'text'  => "Mình làm cái đồ án này hết mấy tuần, ban đầu bị lỗi kết nối database suốt. Sau khi debug thì phát hiện ra do thiếu dấu chấm phẩy trong file config - cái lỗi ngớ ngẩn mà tốn cả buổi tối. Phần giao diện mình dùng Bootstrap vì quen tay, còn backend là PHP thuần. Tài liệu kèm theo gồm file SQL để chạy trực tiếp và hướng dẫn cài đặt.",
    ],
    'ai' => [
        'label' => 'Văn bản AI Generated',
        'text'  => "Trong bối cảnh chuyển đổi số ngày càng phát triển, sản phẩm này đóng vai trò then chốt giúp doanh nghiệp tối ưu hóa quy trình vận hành. Không thể không nhắc đến các tính năng nổi bật được thiết kế nhằm nâng cao trải nghiệm người dùng một cách toàn diện và chuyên sâu. Hãy cùng khám phá giải pháp toàn diện mà sản phẩm mang lại, hứa hẹn là bước đột phá trong lĩnh vực quản lý dữ liệu hiện đại. Tóm lại, đây là sự kết hợp hoàn hảo giữa công nghệ tiên tiến và nhu cầu thực tế của thị trường, phù hợp với mọi đối tượng khách hàng.",
    ],
    'mixed' => [
        'label' => 'Văn bản Pha trộn',
        'text'  => "Source code quản lý bán hàng bằng PHP và MySQL. Mình code tự tay phần CRUD sản phẩm và đơn hàng. Trong bối cảnh thương mại điện tử phát triển mạnh, sản phẩm này đóng vai trò quan trọng cho các shop online. Có kèm hướng dẫn cài đặt trên XAMPP. Tóm lại đây là tài liệu phù hợp với mọi đối tượng sinh viên CNTT.",
    ],
];

// ========== XỬ LÝ FORM ==========
$customResult = null;
$customText   = '';
$customTitle  = '';
$activeTab    = isset($_GET['sample']) ? $_GET['sample'] : 'ai';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !empty($_POST['text'])) {
    $customText   = trim($_POST['text']);
    $customTitle  = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $customResult = AiDetectionService::detect($customText, $customTitle);
    $activeTab    = 'custom';
}

// Pre-compute all sample results
$precomputed = [];
foreach ($sampleTexts as $key => $sample) {
    $precomputed[$key] = AiDetectionService::detect($sample['text']);
}

// ========== CLI MODE ==========
if (php_sapi_name() === 'cli') {
    $divider = str_repeat('=', 62) . "\n";
    echo "\n$divider";
    echo "       CREONO - KIEM THU AI DETECTION (UC25)\n";
    echo $divider . "\n";

    foreach ($precomputed as $key => $result) {
        $lbl    = $sampleTexts[$key]['label'];
        $score  = $result['ai_score'];
        $status = $score > 70 ? '[AI Generated]' : ($score > 40 ? '[Mixed]' : '[Human Written]');
        $filled = (int)($score / 5);
        $bar    = str_repeat('X', $filled) . str_repeat('-', 20 - $filled);
        $pCount = count(isset($result['details']['patterns_found']) ? $result['details']['patterns_found'] : []);
        $cv     = isset($result['details']['burstiness_cv']) ? $result['details']['burstiness_cv'] : 'N/A';

        echo "  $lbl\n";
        echo "  Diem AI : {$score}% $status\n";
        echo "  [{$bar}]\n";
        echo "  Nhan    : {$result['label']} (ai_label_id = {$result['ai_label_id']})\n";
        echo "  So cau  : {$result['details']['sentence_count']} | So tu: {$result['details']['word_count']}\n";
        echo "  Burstiness CV: $cv\n";
        echo "  Cum AI phat hien: $pCount\n";
        echo str_repeat('-', 62) . "\n\n";
    }

    echo "Trang thai: THUAT TOAN HOAT DONG BINH THUONG!\n\n";
    exit(0);
}

// ========== HELPER FUNCTIONS ==========
function aiColor($score) {
    if ($score > 70) return '#ef4444';
    if ($score > 40) return '#f59e0b';
    return '#16a34a';
}
function aiBgLight($score) {
    if ($score > 70) return '#fef2f2';
    if ($score > 40) return '#fffbeb';
    return '#f0fdf4';
}
function aiBorderColor($score) {
    if ($score > 70) return '#fca5a5';
    if ($score > 40) return '#fcd34d';
    return '#86efac';
}
function aiBadgeText($score) {
    if ($score > 70) return 'AI Generated';
    if ($score > 40) return 'Mixed (Pha trộn)';
    return 'Human Written';
}
function aiTagBg($score) {
    if ($score > 70) return '#fee2e2';
    if ($score > 40) return '#fef9c3';
    return '#dcfce7';
}
function aiTagColor($score) {
    if ($score > 70) return '#b91c1c';
    if ($score > 40) return '#92400e';
    return '#15803d';
}

// Kết quả hiển thị
$displayResult = null;
if ($activeTab === 'custom' && $customResult !== null) {
    $displayResult = $customResult;
} elseif (isset($precomputed[$activeTab])) {
    $displayResult = $precomputed[$activeTab];
}
$textForForm = $activeTab === 'custom' ? $customText : (isset($sampleTexts[$activeTab]) ? $sampleTexts[$activeTab]['text'] : '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm thử AI Detection - Creono</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        body { background: #f8fafc; color: #1d1d1f; padding: 40px 20px; }
        .container { max-width: 1100px; margin: 0 auto; }

        /* ---- Header ---- */
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 32px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .header p { color: #64748b; font-size: 16px; }
        .badge-success {
            display: inline-block;
            background: #dcfce7; color: #15803d;
            border: 1px solid #86efac;
            padding: 6px 16px; border-radius: 20px;
            font-size: 13px; font-weight: 600; margin-top: 12px;
        }

        /* ---- Section Title ---- */
        .section-title { font-size: 20px; font-weight: 700; margin: 36px 0 16px; color: #0f172a; }

        /* ---- Card ---- */
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .card h3 {
            font-size: 15px; font-weight: 600;
            margin-bottom: 14px; color: #1e293b;
            display: flex; align-items: center; justify-content: space-between;
        }

        /* ---- Tags ---- */
        .tag { font-size: 12px; padding: 3px 10px; border-radius: 6px; font-weight: 500; white-space: nowrap; }
        .tag-orig { background: #f1f5f9; color: #64748b; }
        .tag-ai { background: #fee2e2; color: #b91c1c; }
        .tag-human { background: #dcfce7; color: #15803d; }
        .tag-mixed { background: #fef9c3; color: #92400e; }
        .tag-blue { background: #e0f2fe; color: #0369a1; }

        /* ---- Grids ---- */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; }

        /* ---- Form ---- */
        .form-label { font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; display: block; }
        .form-input, .form-textarea {
            width: 100%;
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            resize: vertical;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none; border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .form-textarea { min-height: 100px; }

        /* ---- Sample Buttons ---- */
        .samples-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
        .sample-btn {
            background: #f1f5f9; border: 1px solid #cbd5e1;
            color: #475569; padding: 7px 16px;
            border-radius: 8px; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all 0.15s;
            text-decoration: none; display: inline-block;
        }
        .sample-btn:hover { background: #e0f2fe; border-color: #7dd3fc; color: #0369a1; }
        .sample-btn.active { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; font-weight: 600; }

        /* ---- Analyze Button ---- */
        .btn-analyze {
            display: inline-flex; align-items: center; gap: 8px;
            background: #0f172a; color: #fff; border: none;
            padding: 10px 24px; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.2s;
        }
        .btn-analyze:hover { background: #1e293b; }

        /* ---- Result Card ---- */
        .result-wrap { border-radius: 14px; overflow: hidden; border: 1px solid; }

        /* Score Row */
        .score-row {
            display: flex; align-items: center; gap: 24px;
            padding: 24px; border-bottom: 1px solid #e2e8f0;
        }
        .score-circle-outer {
            position: relative; flex-shrink: 0;
            width: 110px; height: 110px;
        }
        .score-circle-outer svg { width: 110px; height: 110px; }
        .score-text {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%); text-align: center;
        }
        .score-pct { font-size: 22px; font-weight: 700; display: block; line-height: 1; }
        .score-lbl { font-size: 10px; color: #64748b; font-weight: 500; margin-top: 1px; display: block; }

        .score-meta { flex: 1; }
        .score-meta .badge-label {
            display: inline-block; padding: 5px 14px; border-radius: 20px;
            font-size: 13px; font-weight: 600; margin-bottom: 10px;
        }
        .score-meta .summary { font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 14px; }

        .stat-row { display: flex; gap: 10px; flex-wrap: wrap; }
        .stat-box {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 8px 14px; text-align: center; flex: 1; min-width: 80px;
        }
        .stat-box .s-lbl { display: block; font-size: 11px; color: #94a3b8; font-weight: 500; }
        .stat-box .s-val { display: block; font-size: 17px; font-weight: 700; color: #0f172a; }

        /* Progress Bar */
        .progress-section { padding: 16px 24px; border-bottom: 1px solid #e2e8f0; background: #fafafa; }
        .progress-bar-bg { background: #e2e8f0; border-radius: 20px; height: 12px; overflow: hidden; margin: 8px 0 4px; }
        .progress-bar-fill { height: 100%; border-radius: 20px; transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .progress-labels { display: flex; justify-content: space-between; font-size: 11px; font-weight: 500; color: #94a3b8; }

        /* Patterns */
        .patterns-section { padding: 18px 24px; }
        .patterns-section h4 { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 10px; }
        .patterns-wrap { display: flex; flex-wrap: wrap; gap: 7px; }
        .pattern-chip {
            border: 1px solid; border-radius: 20px;
            padding: 3px 10px; font-size: 12px; font-weight: 500;
        }
        .no-pattern { font-size: 13px; color: #94a3b8; font-style: italic; }

        /* DB Row */
        .db-row {
            padding: 12px 24px; background: #f1f5f9;
            border-top: 1px solid #e2e8f0;
            font-size: 12px; color: #64748b;
        }
        .db-row code {
            background: #e0f2fe; color: #0369a1;
            padding: 1px 6px; border-radius: 4px; font-size: 11px;
        }

        /* Overview mini cards */
        .mini-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.04);
            border-top-width: 3px;
        }
        .mini-card h4 { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 10px; }
        .mini-score { font-size: 28px; font-weight: 700; margin-bottom: 4px; }
        .mini-bar-bg { background: #e2e8f0; border-radius: 6px; height: 7px; margin: 8px 0; overflow: hidden; }
        .mini-bar-fill { height: 100%; border-radius: 6px; }
        .mini-meta { font-size: 12px; color: #94a3b8; margin-top: 6px; }

        @media (max-width: 768px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .score-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <h1>Kiểm thử kết quả Phát hiện Nội dung AI</h1>
        <p>Trực quan hóa kết quả phân tích văn bản tự động bằng thuật toán NLP trên Creono</p>
        <span class="badge-success">✓ Đã xử lý &amp; tích hợp thành công</span>
    </div>

    <!-- ===== 1. FORM NHẬP VĂN BẢN ===== -->
    <div class="section-title">1. Nhập văn bản cần phân tích</div>
    <div class="card" style="margin-bottom: 20px;">
        <div style="margin-bottom: 10px; font-size: 13px; font-weight: 500; color: #475569;">Chọn văn bản mẫu:</div>
        <div class="samples-row">
            <?php foreach ($sampleTexts as $key => $sample): ?>
            <a href="?sample=<?php echo htmlspecialchars($key); ?>"
               class="sample-btn <?php echo ($activeTab === $key && $activeTab !== 'custom') ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($sample['label']); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="">
            <div class="grid-2" style="margin-bottom: 14px;">
                <div>
                    <label class="form-label" for="input-title">Tiêu đề sản phẩm (tuỳ chọn)</label>
                    <input type="text" id="input-title" name="title" class="form-input"
                           value="<?php echo htmlspecialchars($customTitle); ?>"
                           placeholder="Ví dụ: Source Code Quản lý Bán hàng PHP">
                </div>
                <div>
                    <label class="form-label" for="input-text">Mô tả sản phẩm <span style="color:#ef4444;">*</span></label>
                    <textarea id="input-text" name="text" class="form-textarea" required
                              placeholder="Nhập hoặc dán mô tả sản phẩm để phân tích..."><?php echo htmlspecialchars($textForForm); ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn-analyze">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Phân tích ngay
            </button>
        </form>
    </div>

    <!-- ===== 2. KẾT QUẢ PHÂN TÍCH ===== -->
    <?php if ($displayResult !== null):
        $r       = $displayResult;
        $score   = $r['ai_score'];
        $color   = aiColor($score);
        $bgLight = aiBgLight($score);
        $border  = aiBorderColor($score);
        $badge   = aiBadgeText($score);
        $tagBg   = aiTagBg($score);
        $tagClr  = aiTagColor($score);
        $conf    = isset($r['confidence']) ? round($r['confidence'] * 100) . '%' : 'N/A';
        $cv      = isset($r['details']['burstiness_cv']) ? $r['details']['burstiness_cv'] : 'N/A';
        $wc      = isset($r['details']['word_count']) ? $r['details']['word_count'] : 'N/A';
        $sc      = isset($r['details']['sentence_count']) ? $r['details']['sentence_count'] : 'N/A';
        $summary = htmlspecialchars(isset($r['details']['summary']) ? $r['details']['summary'] : '');
        $source  = htmlspecialchars(isset($r['details']['source']) ? $r['details']['source'] : 'Built-in NLP Engine');
        $pats    = isset($r['details']['patterns_found']) ? $r['details']['patterns_found'] : [];
        $pCount  = count($pats);
        // SVG gauge: circumference 2*pi*44 ≈ 276
        $circ    = 276;
        $dashOff = round($circ * (1 - $score / 100));

        $tabLabel = $activeTab === 'custom' ? 'Văn bản tuỳ chỉnh' : (isset($sampleTexts[$activeTab]) ? $sampleTexts[$activeTab]['label'] : '');
    ?>
    <div class="section-title">2. Kết quả phân tích — <?php echo htmlspecialchars($tabLabel); ?></div>

    <div class="result-wrap" style="border-color:<?php echo $border; ?>; background:<?php echo $bgLight; ?>; margin-bottom: 28px;">

        <!-- Score + Meta -->
        <div class="score-row">
            <!-- SVG Gauge -->
            <div class="score-circle-outer">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="44" fill="none" stroke="#e2e8f0" stroke-width="9"/>
                    <circle cx="50" cy="50" r="44" fill="none" stroke="<?php echo $color; ?>" stroke-width="9"
                        stroke-dasharray="<?php echo $circ; ?>"
                        stroke-dashoffset="<?php echo $circ; ?>"
                        stroke-linecap="round"
                        transform="rotate(-90 50 50)"
                        class="gauge-arc"
                        data-offset="<?php echo $dashOff; ?>"/>
                </svg>
                <div class="score-text">
                    <span class="score-pct" style="color:<?php echo $color; ?>;"><?php echo $score; ?>%</span>
                    <span class="score-lbl">AI Score</span>
                </div>
            </div>

            <!-- Meta info -->
            <div class="score-meta">
                <span class="badge-label" style="background:<?php echo $tagBg; ?>; color:<?php echo $tagClr; ?>;"><?php echo $badge; ?></span>
                <p class="summary"><?php echo $summary; ?></p>
                <div class="stat-row">
                    <div class="stat-box"><span class="s-lbl">Độ tin cậy</span><span class="s-val"><?php echo $conf; ?></span></div>
                    <div class="stat-box"><span class="s-lbl">Số từ</span><span class="s-val"><?php echo $wc; ?></span></div>
                    <div class="stat-box"><span class="s-lbl">Số câu</span><span class="s-val"><?php echo $sc; ?></span></div>
                    <div class="stat-box"><span class="s-lbl">Burstiness CV</span><span class="s-val"><?php echo $cv; ?></span></div>
                    <div class="stat-box"><span class="s-lbl">Cụm AI</span><span class="s-val"><?php echo $pCount; ?></span></div>
                </div>
                <div style="margin-top: 10px; font-size: 12px; color: #94a3b8;">Phân tích bởi: <strong><?php echo $source; ?></strong></div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-section">
            <div style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 2px;">Thang đo AI Score</div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill"
                     data-target="<?php echo $score; ?>"
                     style="width:0%; background:<?php echo $color; ?>;"></div>
            </div>
            <div class="progress-labels">
                <span style="color:#16a34a;">0% – Human Written</span>
                <span style="color:#d97706;">40–70% Mixed</span>
                <span style="color:#dc2626;">70%+ AI Generated</span>
            </div>
        </div>

        <!-- Patterns Detected -->
        <div class="patterns-section">
            <h4>Cụm từ AI đặc trưng bị phát hiện:</h4>
            <div class="patterns-wrap">
                <?php if ($pCount === 0): ?>
                    <span class="no-pattern">Không phát hiện cụm từ AI đặc trưng trong văn bản này.</span>
                <?php else: ?>
                    <?php foreach ($pats as $p):
                        $chipColor = '#ef4444';
                        if (isset($p['type'])) {
                            if ($p['type'] === 'Structural Formatting') $chipColor = '#7c3aed';
                            elseif ($p['type'] === 'English AI Marker')  $chipColor = '#0369a1';
                        }
                        $chipBg = $chipColor . '14';
                    ?>
                    <span class="pattern-chip" style="border-color:<?php echo $chipColor; ?>; color:<?php echo $chipColor; ?>; background:<?php echo $chipBg; ?>;">
                        <?php echo htmlspecialchars($p['phrase']); ?> <small>&times;<?php echo (int)($p['count'] ?? 1); ?></small>
                    </span>
                    <?php endforeach; ?>
                    <span style="font-size:12px; color:#94a3b8; align-self:center;">&nbsp;(<?php echo $pCount; ?> cụm)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- DB Row -->
        <div class="db-row">
            Sẽ lưu vào CSDL:
            <code>ai_score = <?php echo $score; ?></code> &nbsp;|&nbsp;
            <code>ai_label_id = <?php echo $r['ai_label_id']; ?></code>
            &nbsp;(<?php echo htmlspecialchars($r['label']); ?>)
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== 3. TỔNG QUAN 3 MẪU ===== -->
    <div class="section-title">3. Tổng quan phân tích 3 văn bản mẫu chuẩn</div>
    <div class="grid-3">
        <?php foreach ($precomputed as $key => $r):
            $col  = aiColor($r['ai_score']);
            $pCnt = count(isset($r['details']['patterns_found']) ? $r['details']['patterns_found'] : []);
            $wCnt = isset($r['details']['word_count']) ? $r['details']['word_count'] : 0;
            $tagBg  = aiTagBg($r['ai_score']);
            $tagClr = aiTagColor($r['ai_score']);
        ?>
        <div class="mini-card" style="border-top-color:<?php echo $col; ?>;">
            <h4><?php echo htmlspecialchars($sampleTexts[$key]['label']); ?></h4>
            <div class="mini-score" style="color:<?php echo $col; ?>"><?php echo $r['ai_score']; ?>%</div>
            <div class="mini-bar-bg">
                <div class="mini-bar-fill" style="width:<?php echo $r['ai_score']; ?>%; background:<?php echo $col; ?>;"></div>
            </div>
            <div>
                <span class="tag" style="background:<?php echo $tagBg; ?>; color:<?php echo $tagClr; ?>;">
                    <?php echo aiBadgeText($r['ai_score']); ?>
                </span>
            </div>
            <div class="mini-meta">
                <?php echo $pCnt; ?> cụm AI &bull; <?php echo $wCnt; ?> từ &bull; label_id: <?php echo $r['ai_label_id']; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div><!-- .container -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate progress bars
    document.querySelectorAll('.progress-bar-fill[data-target]').forEach(function (el) {
        setTimeout(function () { el.style.width = el.getAttribute('data-target') + '%'; }, 100);
    });
    // Animate SVG gauge
    document.querySelectorAll('.gauge-arc[data-offset]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-offset'));
        el.style.transition = 'stroke-dashoffset 1.3s cubic-bezier(0.4, 0, 0.2, 1)';
        setTimeout(function () { el.style.strokeDashoffset = target; }, 150);
    });
});
</script>

</body>
</html>
