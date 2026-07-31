<?php
// Yêu cầu file cấu hình
require_once '../config/config.php';
require_once '../app/Helpers/session_helper.php';

// Tự động load các file Core
require_once '../app/Core/Database.php';
require_once '../app/Core/BaseModel.php';
require_once '../app/Core/Controller.php';
require_once '../app/Core/App.php';

// Khởi tạo router
$init = new App();