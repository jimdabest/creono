<?php
class Wallets extends Controller
{
    private Wallet $walletModel;

    public function __construct()
    {
        // Trạm gác: Chặn khách vãng lai, yêu cầu đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT . '/users/login');
            exit();
        }
        $this->walletModel = $this->model('Wallet');
    }

    // Hiển thị trang Ví của tôi
    public function index()
    {
        // Lấy thông tin ví của user đang đăng nhập
        $wallet = $this->walletModel->getWalletByUserId($_SESSION['user_id']);

        // Lấy lịch sử giao dịch
        $transactions = [];
        if ($wallet) {
            $transactions = $this->walletModel->getTransactions($wallet->id);
        }

        $data = [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'amount_err' => '',
            'bank_err' => ''
        ];

        $this->view('wallets/index', $data);
    }

    // Xử lý Form yêu cầu rút tiền
    public function withdraw()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $wallet = $this->walletModel->getWalletByUserId($_SESSION['user_id']);

            $data = [
                'wallet_id' => $wallet->id,
                'amount' => trim($_POST['amount']),
                'bank_name' => trim($_POST['bank_name']),
                'bank_acc_num' => trim($_POST['bank_acc_num']),
                'bank_acc_name' => trim($_POST['bank_acc_name']),
                'amount_err' => '',
                'bank_err' => ''
            ];

            // Validate số tiền
            if (empty($data['amount']) || $data['amount'] <= 0) {
                $data['amount_err'] = 'Vui lòng nhập số tiền hợp lệ lớn hơn 0.';
            } elseif ($data['amount'] > $wallet->balance) {
                $data['amount_err'] = 'Số dư khả dụng không đủ để rút.';
            }

            // Validate ngân hàng
            if (empty($data['bank_name']) || empty($data['bank_acc_num']) || empty($data['bank_acc_name'])) {
                $data['bank_err'] = 'Vui lòng điền đầy đủ thông tin ngân hàng.';
            }

            // Nếu không có lỗi, tiến hành gọi Model
            if (empty($data['amount_err']) && empty($data['bank_err'])) {
                if ($this->walletModel->requestWithdrawal($data)) {
                    // Thành công, reload lại trang ví
                    header('location: ' . URLROOT . '/wallets/index');
                    exit();
                } else {
                    if (empty($data['amount_err']) && empty($data['bank_err'])) {
                        if ($this->walletModel->requestWithdrawal($data)) {
                            setFlash('success', 'Yêu cầu rút tiền đang được xử lý!');
                            header('location: ' . URLROOT . '/wallets/index');
                            exit();
                        } else {
                            // Đã thay thế lệnh die()
                            setFlash('error', 'Có lỗi xảy ra trong quá trình xử lý giao dịch. Vui lòng kiểm tra lại số dư.');
                            header('location: ' . URLROOT . '/wallets/index');
                            exit();
                        }
                    }
                }
            } else {
                // Có lỗi, load lại view hiển thị lỗi
                $data['wallet'] = $wallet;
                $data['transactions'] = $this->walletModel->getTransactions($wallet->id);
                $this->view('wallets/index', $data);
            }
        } else {
            header('location: ' . URLROOT . '/wallets/index');
            exit();
        }
    }

    public function deposit()
    {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Giả lập nạp tiền – thực tế sẽ gọi API cổng thanh toán
            $amount = (float) $_POST['amount'] ?? 0;
            if ($amount <= 0) {
                setFlash('error', 'Số tiền nạp phải lớn hơn 0.');
                header('location: ' . URLROOT . '/wallets/deposit');
                exit();
            }

            $wallet = $this->walletModel->getWalletByUserId($_SESSION['user_id']);
            if (!$wallet) {
                setFlash('error', 'Không tìm thấy ví.');
                header('location: ' . URLROOT . '/wallets/deposit');
                exit();
            }

            // Gọi thẳng hàm deposit của Model, mọi logic DB đã được xử lý kín bên trong
            if ($this->walletModel->deposit((int)$wallet->id, $amount)) {
                setFlash('success', 'Nạp tiền thành công!');
            } else {
                setFlash('error', 'Nạp tiền thất bại, vui lòng thử lại.');
            }

            header('location: ' . URLROOT . '/wallets/index');
            exit();
        } else {
            $data = [
                'title' => 'Nạp tiền vào ví',
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('wallets/deposit', $data);
        }
    }
}
