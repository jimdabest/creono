<?php
// Yêu cầu file cấu hình
require_once '../config/config.php';
require_once '../app/Helpers/session_helper.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/error_helper.php';

if (function_exists('handleException')) {
	set_exception_handler('handleException');
}

// Tự động load các file Core
require_once '../app/Core/Database.php';
require_once '../app/Core/BaseModel.php';
require_once '../app/Core/Controller.php';
require_once '../app/Core/App.php';

// Khởi tạo router
$init = new App();