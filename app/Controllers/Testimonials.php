<?php

declare(strict_types=1);

require_once __DIR__ . '/TestimonialController.php';

/**
 * Controller alias cho phép truy cập qua URL clean: /testimonials/...
 * Kế thừa toàn bộ action từ TestimonialController
 */
class Testimonials extends TestimonialController
{
}
