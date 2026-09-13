<?php
function setFlash(string $key, string $message, string $type = 'success'): void
{
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlash(string $key): ?array
{
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

function displayFlash(string $key): void
{
    $flash = getFlash($key);
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        $message = $flash['message'];

        // Gọi View partial để render HTML thay vì echo trực tiếp
        require APPROOT . '/Views/inc/flash_message.php';
    }
}

if (!function_exists('flash')) {
    function flash(?string $key = null): void
    {
        if ($key) {
            displayFlash($key);
        } else {
            displayFlash('success');
            displayFlash('error');
            displayFlash('info');
            displayFlash('warning');
        }
    }
}

if (!function_exists('session')) {
    function session()
    {
        static $sessionInstance = null;
        if ($sessionInstance === null) {
            $sessionInstance = new class {
                private array $cached = [];

                public function getFlashdata(?string $key = null)
                {
                    if ($key === null) {
                        $all = [];
                        if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
                            foreach ($_SESSION['flash'] as $k => $item) {
                                $all[$k] = is_array($item) ? ($item['message'] ?? $item) : $item;
                            }
                            unset($_SESSION['flash']);
                        }
                        return $all;
                    }

                    if (array_key_exists($key, $this->cached)) {
                        return $this->cached[$key];
                    }

                    if (isset($_SESSION['flash'][$key])) {
                        $flash = $_SESSION['flash'][$key];
                        unset($_SESSION['flash'][$key]);
                        $val = is_array($flash) ? ($flash['message'] ?? $flash) : $flash;
                        $this->cached[$key] = $val;
                        return $val;
                    }

                    if (isset($_SESSION[$key])) {
                        $val = $_SESSION[$key];
                        unset($_SESSION[$key]);
                        $this->cached[$key] = $val;
                        return $val;
                    }

                    return null;
                }

                public function setFlashdata(string $key, $value): void
                {
                    $_SESSION['flash'][$key] = [
                        'message' => $value,
                        'type'    => $key
                    ];
                }

                public function get(string $key)
                {
                    return $_SESSION[$key] ?? null;
                }

                public function has(string $key): bool
                {
                    return isset($_SESSION[$key]);
                }
            };
        }
        return $sessionInstance;
    }
}

