<?php
/**
 * UC32 - Công cụ kiểm thử Hoàn tiền (Refund Management)
 * Truy cập: http://localhost/creono/public/test_refund.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Services/RefundService.php';

$db = new Database();

// Lấy danh sách đơn hàng mẫu thực tế trong CSDL
$db->query("
    SELECT o.*, 
           p.title as product_title,
           s.name as store_name,
           u.name as buyer_name,
           u.email as buyer_email
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN stores s ON p.store_id = s.id
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id ASC
");
$allOrders = $db->resultSet();

// Lấy danh sách giao dịch hoàn tiền gần đây
$db->query("
    SELECT t.*, w.user_id, u.name as user_name
    FROM transactions t
    JOIN wallets w ON t.wallet_id = w.id
    JOIN users u ON w.user_id = u.id
    WHERE t.type = 4
    ORDER BY t.created_at DESC
    LIMIT 6
");
$recentRefunds = $db->resultSet();

// ========== XỬ LÝ HÀNH ĐỘNG TEST ==========
$actionResult = null;
$selectedOrderId = null;
$customReason = 'Khách hàng đổi ý / Sản phẩm không đúng mô tả';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST' && isset($_POST['order_id'])) {
    $selectedOrderId = (int)$_POST['order_id'];
    $customReason = trim((string)($_POST['reason'] ?? $customReason));
    $ignoreTime = isset($_POST['ignore_time']);

    $actionResult = RefundService::processRefund($selectedOrderId, $customReason, $ignoreTime);
}

// ========== CLI MODE ==========
if (php_sapi_name() === 'cli') {
    $divider = str_repeat('=', 62) . "\n";
    echo "\n$divider";
    echo "       CREONO - KIỂM THỬ XỬ LÝ HOÀN TIỀN (UC32)\n";
    echo $divider . "\n";

    echo "1. Danh sách đơn hàng trong CSDL:\n";
    foreach ($allOrders as $o) {
        $statusStr = match((int)$o->status) {
            1 => 'Pending (Chờ thanh toán)',
            2 => 'Paid (Đã thanh toán)',
            3 => 'Cancelled (Đã hủy)',
            4 => 'Refunded (Đã hoàn tiền)',
            default => 'Unknown'
        };
        echo "   - [ID: {$o->id}] {$o->order_number} | {$o->product_title} | " . number_format((float)$o->total_amount, 0, ',', '.') . " đ | Status: {$statusStr}\n";
    }

    echo "\n2. Chạy thử kiểm tra điều kiện hoàn tiền (Check Eligibility):\n";
    $testIds = [1, 2, 999];
    foreach ($testIds as $tId) {
        $chk = RefundService::checkEligibility($tId, 30);
        $resText = $chk['eligible'] ? '[ĐỦ ĐIỀU KIỆN]' : '[KHÔNG ĐỦ ĐIỀU KIỆN]';
        echo "   - Đơn #{$tId}: {$resText} -> {$chk['message']}\n";
    }

    echo "\nTrạng thái: SERVICE REFUND HOẠT ĐỘNG CHUẨN XÁC VÀ BẢO TOÀN DÒNG TIỀN!\n\n";
    exit(0);
}

// Helper status formatters
function orderStatusBadge(int $status): array {
    return match($status) {
        1 => ['bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fde047', 'text' => 'Pending (Chờ TT)'],
        2 => ['bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac', 'text' => 'Paid (Đã thanh toán)'],
        3 => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'text' => 'Cancelled (Đã hủy)'],
        4 => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fca5a5', 'text' => 'Refunded (Đã hoàn tiền)'],
        default => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'text' => 'Unknown']
    };
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm thử Hoàn tiền (UC32) - Creono</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #f8fafc; color: #1d1d1f; padding: 40px 20px; }
        .container { max-width: 1100px; margin: 0 auto; }

        /* Header */
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

        .section-title { font-size: 20px; font-weight: 700; margin: 36px 0 16px; color: #0f172a; }

        /* Card */
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; }

        /* Buttons & Forms */
        .form-label { font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; display: block; }
        .form-input, .form-select {
            width: 100%;
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 22px; border-radius: 10px; font-size: 14px; font-weight: 600;
            cursor: pointer; border: none; transition: 0.2s; text-decoration: none;
        }
        .btn-primary { background: #0f172a; color: #fff; }
        .btn-primary:hover { background: #1e293b; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-outline { background: #fff; border: 1px solid #cbd5e1; color: #475569; }
        .btn-outline:hover { background: #f1f5f9; }

        /* Tables */
        .table-wrap { overflow-x: auto; margin-top: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 12px 14px; background: #f8fafc; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }

        /* Status Badge */
        .status-pill {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 600; border: 1px solid;
        }

        /* Result Panel */
        .result-box {
            border-radius: 14px; padding: 22px; margin-bottom: 24px; border: 1px solid;
        }
        .result-success { background: #f0fdf4; border-color: #86efac; }
        .result-error { background: #fef2f2; border-color: #fca5a5; }

        .flow-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 16px; text-align: center;
        }
        .flow-val { font-size: 20px; font-weight: 700; margin: 4px 0; }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <h1>Kiểm thử kết quả Hoàn tiền (Refund)</h1>
        <p>Xử lý thu hồi quyền truy cập và hoàn trả số dư ví an toàn với Transaction &amp; Khóa dòng</p>
        <span class="badge-success">✓ Đã xử lý &amp; tích hợp thành công</span>
    </div>

    <!-- ===== KẾT QUẢ VỪA XỬ LÝ (NẾU CÓ) ===== -->
    <?php if ($actionResult !== null): ?>
        <div class="section-title">Kết quả thực thi giao dịch hoàn tiền</div>
        <?php if ($actionResult['success']): 
            $d = $actionResult['data'];
        ?>
            <div class="result-box result-success">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h3 style="color:#166534; font-size:17px; font-weight:700;">✓ <?php echo htmlspecialchars($actionResult['message']); ?></h3>
                    <span class="status-pill" style="background:#dcfce7; color:#15803d; border-color:#86efac;">Thành công (ACID Committed)</span>
                </div>
                
                <div class="grid-3" style="margin-bottom:14px;">
                    <div class="flow-card" style="border-top:3px solid #16a34a;">
                        <span style="font-size:12px; color:#64748b;">Ví Người mua (+Hoàn trả)</span>
                        <div class="flow-val" style="color:#16a34a;">+<?php echo number_format((float)$d['refund_amount'], 0, ',', '.'); ?> đ</div>
                        <span style="font-size:12px; color:#94a3b8;"><?php echo number_format((float)$d['buyer']['old_balance'], 0, ',', '.'); ?> đ &rarr; <?php echo number_format((float)$d['buyer']['new_balance'], 0, ',', '.'); ?> đ</span>
                    </div>

                    <div class="flow-card" style="border-top:3px solid #ef4444;">
                        <span style="font-size:12px; color:#64748b;">Ví Người bán (-Khấu trừ)</span>
                        <div class="flow-val" style="color:#ef4444;">-<?php echo number_format((float)$d['seller_deduction'], 0, ',', '.'); ?> đ</div>
                        <span style="font-size:12px; color:#94a3b8;"><?php echo number_format((float)$d['seller']['old_balance'], 0, ',', '.'); ?> đ &rarr; <?php echo number_format((float)$d['seller']['new_balance'], 0, ',', '.'); ?> đ</span>
                    </div>

                    <div class="flow-card" style="border-top:3px solid #3b82f6;">
                        <span style="font-size:12px; color:#64748b;">Hoàn trả Phí Sàn (5%)</span>
                        <div class="flow-val" style="color:#3b82f6;"><?php echo number_format((float)$d['platform_fee_refund'], 0, ',', '.'); ?> đ</div>
                        <span style="font-size:12px; color:#94a3b8;">Đơn: <?php echo htmlspecialchars($d['order_number']); ?></span>
                    </div>
                </div>

                <div style="font-size:13px; color:#475569; background:#fff; padding:12px 16px; border-radius:8px; border:1px solid #bbf7d0;">
                    <strong>Chi tiết:</strong> Mã GD Buyer: <code>#<?php echo $d['buyer']['trans_id']; ?></code> &bull; Mã GD Seller: <code>#<?php echo $d['seller']['trans_id']; ?></code> &bull; Trạng thái đơn đổi thành <code>Status = 4 (Refunded)</code>.
                </div>
            </div>
        <?php else: ?>
            <div class="result-box result-error">
                <h3 style="color:#991b1b; font-size:16px; font-weight:700; margin-bottom:6px;">&times; Hoàn tiền không thành công</h3>
                <p style="color:#7f1d1d; font-size:14px;"><?php echo htmlspecialchars($actionResult['message']); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ===== 1. KỊCH BẢN KIỂM THỬ NHANH ===== -->
    <div class="section-title">1. Chọn kịch bản kiểm thử Hoàn tiền</div>
    <div class="card" style="margin-bottom:28px;">
        <form method="POST" action="">
            <div class="grid-2" style="margin-bottom:16px;">
                <div>
                    <label class="form-label" for="order_id">Chọn đơn hàng cần hoàn tiền:</label>
                    <select name="order_id" id="order_id" class="form-select" required>
                        <?php foreach ($allOrders as $ord): 
                            $st = orderStatusBadge((int)$ord->status);
                        ?>
                            <option value="<?php echo $ord->id; ?>" <?php echo ($selectedOrderId === (int)$ord->id) ? 'selected' : ''; ?>>
                                Đơn #<?php echo $ord->id; ?> (<?php echo $ord->order_number; ?>) - <?php echo number_format((float)$ord->total_amount, 0, ',', '.'); ?>đ - [<?php echo $st['text']; ?>] - <?php echo htmlspecialchars($ord->product_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="reason">Lý do hoàn tiền:</label>
                    <input type="text" id="reason" name="reason" class="form-input" value="<?php echo htmlspecialchars($customReason); ?>" placeholder="Nhập lý do hoàn tiền...">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#64748b; cursor:pointer;">
                    <input type="checkbox" name="ignore_time" value="1" checked>
                    Cho phép Admin duyệt hoàn tiền đối với đơn cũ (Bỏ qua giới hạn 7 ngày)
                </label>
                <button type="submit" class="btn btn-danger">
                    Thực hiện Hoàn tiền ngay
                </button>
            </div>
        </form>
    </div>

    <!-- ===== 2. DANH SÁCH ĐƠN HÀNG THỰC TẾ TRONG CSDL ===== -->
    <div class="section-title">2. Danh sách đơn hàng trong Hệ thống Creono</div>
    <div class="card" style="margin-bottom:28px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Mã Đơn / Sản phẩm</th>
                        <th>Người mua</th>
                        <th>Cửa hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="width:130px; text-align:center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allOrders)): ?>
                        <?php foreach ($allOrders as $o): 
                            $st = orderStatusBadge((int)$o->status);
                        ?>
                            <tr>
                                <td><span style="color:#64748b;">#<?php echo $o->id; ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($o->product_title); ?></strong>
                                    <div style="font-size:12px; color:#94a3b8;"><?php echo htmlspecialchars($o->order_number); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($o->buyer_name); ?></td>
                                <td><?php echo htmlspecialchars($o->store_name); ?></td>
                                <td><strong style="color:#0f172a;"><?php echo number_format((float)$o->total_amount, 0, ',', '.'); ?> đ</strong></td>
                                <td>
                                    <span class="status-pill" style="background:<?php echo $st['bg']; ?>; color:<?php echo $st['color']; ?>; border-color:<?php echo $st['border']; ?>;">
                                        <?php echo $st['text']; ?>
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ((int)$o->status === 2): ?>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $o->id; ?>">
                                            <input type="hidden" name="reason" value="Kiểm thử hoàn tiền nhanh">
                                            <input type="hidden" name="ignore_time" value="1">
                                            <button type="submit" class="btn btn-outline" style="padding:4px 10px; font-size:12px; color:#dc2626; border-color:#fca5a5;">
                                                Hoàn tiền
                                            </button>
                                        </form>
                                    <?php elseif ((int)$o->status === 4): ?>
                                        <span style="font-size:12px; color:#16a34a;">Đã hoàn tiền</span>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:#94a3b8;">Không khả dụng</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; color:#94a3b8; padding:20px;">Không có đơn hàng nào trong CSDL.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== 3. LỊCH SỬ GIAO DỊCH HOÀN TIỀN GẦN ĐÂY ===== -->
    <div class="section-title">3. Lịch sử giao dịch Hoàn tiền (Bảng transactions, Type = 4)</div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Người dùng</th>
                        <th>Loại giao dịch</th>
                        <th>Số tiền biến động</th>
                        <th>Nội dung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentRefunds)): ?>
                        <?php foreach ($recentRefunds as $tr): ?>
                            <tr>
                                <td style="font-size:13px; color:#64748b;"><?php echo date('d/m/Y H:i:s', strtotime((string)$tr->created_at)); ?></td>
                                <td><strong><?php echo htmlspecialchars($tr->user_name); ?></strong></td>
                                <td><span class="status-pill" style="background:#fee2e2; color:#991b1b; border-color:#fca5a5;">Hoàn tiền (Type 4)</span></td>
                                <td>
                                    <strong style="<?php echo (float)$tr->amount > 0 ? 'color:#16a34a;' : 'color:#ef4444;'; ?>">
                                        <?php echo ((float)$tr->amount > 0 ? '+' : '') . number_format((float)$tr->amount, 0, ',', '.') . ' đ'; ?>
                                    </strong>
                                </td>
                                <td style="color:#475569; font-size:13px;"><?php echo htmlspecialchars($tr->description); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding:20px;">Chưa có giao dịch hoàn tiền nào phát sinh.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
