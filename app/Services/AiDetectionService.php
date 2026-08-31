<?php

declare(strict_types=1);

/**
 * Service phân tích và phát hiện nội dung do Trí tuệ nhân tạo (AI) tạo ra (UC25)
 * Hỗ trợ phân tích văn bản Tiếng Việt và Tiếng Anh với thuật toán NLP đa tầng
 */
class AiDetectionService
{
    /**
     * Danh sách cụm từ & mẫu câu sáo rỗng đặc trưng của LLMs (ChatGPT, Claude, Gemini) bằng Tiếng Việt
     */
    private const VI_AI_PATTERNS = [
        'trong bối cảnh' => 3.5,
        'đóng vai trò quan trọng' => 4.0,
        'đóng vai trò then chốt' => 4.0,
        'không thể không nhắc đến' => 4.5,
        'không thể phủ nhận' => 3.5,
        'đáng chú ý là' => 3.0,
        'tóm lại,' => 3.5,
        'tóm gọn lại,' => 4.0,
        'tổng kết lại,' => 4.0,
        'nhìn chung,' => 2.5,
        'mang lại giải pháp tối ưu' => 4.0,
        'hãy cùng khám phá' => 4.5,
        'hãy cùng tìm hiểu' => 4.0,
        'hãy cùng điểm qua' => 4.0,
        'một trong những yếu tố then chốt' => 4.0,
        'như chúng ta đã biết' => 3.0,
        'được thiết kế nhằm' => 3.0,
        'nâng cao trải nghiệm' => 3.5,
        'tối ưu hóa quy trình' => 3.5,
        'nền tảng vững chắc' => 3.0,
        'hứa hẹn mang lại' => 3.5,
        'bước đột phá' => 3.5,
        'toàn diện và chuyên sâu' => 3.5,
        'đa dạng và phong phú' => 3.0,
        'sự kết hợp hoàn hảo' => 4.0,
        'phù hợp với mọi đối tượng' => 3.5,
        'giải pháp toàn diện' => 3.5,
        'không chỉ... mà còn' => 2.5,
        'vô cùng quan trọng' => 2.5,
        'dễ dàng và nhanh chóng' => 2.5
    ];

    /**
     * Danh sách cụm từ đặc trưng của LLMs bằng Tiếng Anh
     */
    private const EN_AI_PATTERNS = [
        'in conclusion' => 4.0,
        'it is important to note' => 4.5,
        'delve into' => 5.0,
        'tapestry of' => 5.0,
        'testament to' => 5.0,
        'furthermore' => 3.0,
        'moreover' => 3.0,
        'seamlessly integrates' => 4.5,
        'in today\'s fast-paced world' => 5.0,
        'plays a crucial role' => 4.0,
        'plays a pivotal role' => 4.5,
        'revolutionize the way' => 4.0,
        'beacon of' => 4.5,
        'navigating the complexities' => 4.5,
        'a plethora of' => 4.0,
        'in summary' => 3.5,
        'game changer' => 3.5,
        'cutting-edge' => 3.0
    ];

    /**
     * Phân tích văn bản và phát hiện tỷ lệ AI
     *
     * @param string $text  Nội dung mô tả hoặc văn bản cần quét
     * @param string $title Tiêu đề sản phẩm (tùy chọn)
     * @return array Kết quả chuẩn hóa { ai_score, is_ai_generated, ai_label_id, label, details }
     */
    public static function detect(string $text, string $title = ''): array
    {
        $fullText = trim(($title !== '' ? $title . "\n\n" : '') . $text);

        if (mb_strlen($fullText) < 20) {
            return [
                'ai_score'        => 0.00,
                'is_ai_generated' => false,
                'ai_label_id'     => 1, // Human Written
                'label'           => 'Human Written',
                'confidence'      => 0.50,
                'details'         => [
                    'word_count'     => mb_strlen($fullText) > 0 ? count(preg_split('/\s+/u', $fullText)) : 0,
                    'sentence_count' => 1,
                    'patterns_found' => [],
                    'summary'        => 'Văn bản quá ngắn để phân tích chi tiết (Mặc định: Human Written).'
                ]
            ];
        }

        // 1. Thử gọi External API nếu được cấu hình
        $externalResult = self::callExternalApi($fullText);
        if ($externalResult !== null) {
            return $externalResult;
        }

        // 2. Chạy thuật toán phân tích NLP Heuristics nội bộ
        return self::analyzeWithNLP($fullText);
    }

    /**
     * Thuật toán phân tích nội dung nâng cao (NLP Heuristics Engine)
     */
    public static function analyzeWithNLP(string $text): array
    {
        $cleanText = trim($text);
        $lowerText = mb_strtolower($cleanText, 'UTF-8');

        // Phân tách câu
        $sentences = preg_split('/[.!?\n]+/u', $cleanText, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_values(array_filter(array_map('trim', $sentences), fn($s) => mb_strlen($s) > 3));
        $sentenceCount = max(1, count($sentences));

        // Phân tách từ
        $words = preg_split('/[^\p{L}\p{N}]+/u', $lowerText, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = max(1, count($words));

        $score = 0.0;
        $matchedPatterns = [];

        // -------------------------------------------------------------
        // TIÊU CHÍ 1: Quét cụm từ sáo rỗng / Cliche AI Patterns (Tối đa 50 điểm)
        // -------------------------------------------------------------
        $patternScore = 0.0;
        foreach (self::VI_AI_PATTERNS as $pattern => $weight) {
            if (mb_strpos($lowerText, $pattern) !== false) {
                $count = mb_substr_count($lowerText, $pattern);
                $patternScore += $weight * min($count, 2);
                $matchedPatterns[] = [
                    'phrase' => $pattern,
                    'count'  => $count,
                    'type'   => 'Vietnamese AI Marker'
                ];
            }
        }

        foreach (self::EN_AI_PATTERNS as $pattern => $weight) {
            if (mb_strpos($lowerText, $pattern) !== false) {
                $count = mb_substr_count($lowerText, $pattern);
                $patternScore += $weight * min($count, 2);
                $matchedPatterns[] = [
                    'phrase' => $pattern,
                    'count'  => $count,
                    'type'   => 'English AI Marker'
                ];
            }
        }

        // Chuẩn hóa điểm mẫu câu (tối đa 50)
        $score += min(50.0, $patternScore * 2.8);

        // -------------------------------------------------------------
        // TIÊU CHÍ 2: Tính toán Burstiness (Độ biến thiên độ dài câu) (Tối đa 25 điểm)
        // AI có độ dài câu rất đồng đều (Low Variance), con người viết biến thiên cao.
        // -------------------------------------------------------------
        $sentenceLengths = [];
        foreach ($sentences as $sentence) {
            $sWords = preg_split('/[^\p{L}\p{N}]+/u', $sentence, -1, PREG_SPLIT_NO_EMPTY);
            $sentenceLengths[] = count($sWords);
        }

        $avgLength = array_sum($sentenceLengths) / $sentenceCount;
        $variance = 0.0;
        foreach ($sentenceLengths as $len) {
            $variance += pow($len - $avgLength, 2);
        }
        $stdDev = $sentenceCount > 1 ? sqrt($variance / ($sentenceCount - 1)) : 5.0;
        $cv = $avgLength > 0 ? ($stdDev / $avgLength) : 0.5; // Hệ số biến thiên

        // Nếu câu đồng đều bất thường (cv < 0.28) -> Nghi ngờ cao là AI
        if ($sentenceCount >= 3) {
            if ($cv < 0.22) {
                $score += 25.0; // Rất đồng đều
            } elseif ($cv < 0.32) {
                $score += 18.0;
            } elseif ($cv < 0.42) {
                $score += 10.0;
            }
        }

        // -------------------------------------------------------------
        // TIÊU CHÍ 3: Cấu trúc Markdown & Danh sách liệt kê hoàn hảo (Tối đa 15 điểm)
        // -------------------------------------------------------------
        $bulletCount = preg_match_all('/^[\s]*[-*•\d+.]+\s+\*\*.*?\*\*/m', $cleanText);
        if ($bulletCount >= 3) {
            $score += min(15.0, $bulletCount * 3.5);
            $matchedPatterns[] = [
                'phrase' => 'Cấu trúc danh sách in đậm chuẩn format AI (**Tiêu đề:** Nội dung)',
                'count'  => $bulletCount,
                'type'   => 'Structural Formatting'
            ];
        }

        // -------------------------------------------------------------
        // TIÊU CHÍ 4: Độ phong phú từ vựng (Type-Token Ratio - TTR) (Tối đa 10 điểm)
        // -------------------------------------------------------------
        $uniqueWords = count(array_unique($words));
        $ttr = $uniqueWords / $wordCount;
        if ($wordCount > 40 && $ttr < 0.45) {
            $score += 10.0; // Từ vựng lặp lại nhiều
        }

        // Giới hạn điểm từ 5.0% đến 99.0%
        $aiScore = (float)max(4.5, min(98.5, round($score, 1)));

        // Phân loại nhãn theo CSDL:
        // id 1: Human Written (ai_score <= 40%)
        // id 3: Mixed (40% < ai_score <= 70%)
        // id 2: AI Generated (ai_score > 70%)
        $aiLabelId = 1;
        $label = 'Human Written';

        if ($aiScore > 70.0) {
            $aiLabelId = 2;
            $label = 'AI Generated';
        } elseif ($aiScore > 40.0) {
            $aiLabelId = 3;
            $label = 'Mixed';
        }

        // Tạo tóm tắt phân tích
        $summary = match ($aiLabelId) {
            2 => "Nội dung có xác suất cao do AI tạo ra ({$aiScore}%). Phát hiện nhiều cấu trúc ngữ pháp và từ vựng đặc trưng của mô hình ngôn ngữ lớn.",
            3 => "Nội dung pha trộn giữa người viết và AI ({$aiScore}%). Có dấu hiệu chỉnh sửa hoặc sử dụng AI hỗ trợ viết lại.",
            default => "Nội dung tự nhiên do người viết ({$aiScore}%). Cấu trúc câu phong phú và độ biến thiên từ vựng cao."
        };

        return [
            'ai_score'        => $aiScore,
            'is_ai_generated' => ($aiScore > 70.0),
            'ai_label_id'     => $aiLabelId,
            'label'           => $label,
            'confidence'      => round(min(0.98, 0.65 + (count($matchedPatterns) * 0.08)), 2),
            'details'         => [
                'word_count'        => $wordCount,
                'sentence_count'    => $sentenceCount,
                'avg_sentence_len'  => round($avgLength, 1),
                'burstiness_cv'     => round($cv, 2),
                'patterns_found'    => $matchedPatterns,
                'summary'           => $summary
            ]
        ];
    }

    /**
     * Gọi External API phân tích AI nếu được cấu hình trong config
     */
    public static function callExternalApi(string $text): ?array
    {
        if (!defined('AI_DETECTOR_API_KEY') || empty(AI_DETECTOR_API_KEY)) {
            return null;
        }

        $endpoint = defined('AI_DETECTOR_ENDPOINT') ? AI_DETECTOR_ENDPOINT : 'https://api.zerogpt.com/api/detect/detectText';

        try {
            $ch = curl_init($endpoint);
            $payload = json_encode(['text' => $text]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . AI_DETECTOR_API_KEY
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['ai_score']) || isset($data['fakePercentage'])) {
                    $score = (float)($data['ai_score'] ?? $data['fakePercentage']);
                    $aiLabelId = ($score > 70.0) ? 2 : (($score > 40.0) ? 3 : 1);
                    $label = ($aiLabelId === 2) ? 'AI Generated' : (($aiLabelId === 3) ? 'Mixed' : 'Human Written');

                    return [
                        'ai_score'        => $score,
                        'is_ai_generated' => ($score > 70.0),
                        'ai_label_id'     => $aiLabelId,
                        'label'           => $label,
                        'confidence'      => 0.95,
                        'details'         => [
                            'source'  => 'External AI Detection API',
                            'summary' => "Kết quả phân tích từ External API: {$score}% khả năng do AI sinh ra."
                        ]
                    ];
                }
            }
        } catch (\Throwable $e) {
            if (function_exists('logError')) {
                logError('Lỗi gọi AI Detection API ngoại vi: ' . $e->getMessage());
            }
        }

        return null;
    }
}
