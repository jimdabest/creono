<?php
class Validator {
    private array $data;
    private array $errors = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function validate(array $rules): array {
        foreach ($rules as $field => $fieldRules) {
            $rulesArray = explode('|', $fieldRules);
            
            foreach ($rulesArray as $rule) {
                $value = trim($this->data[$field] ?? '');
                
                if ($rule === 'required' && empty($value)) {
                    $this->addError($field, 'Trường này không được để trống');
                }
                
                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Vui lòng nhập email hợp lệ');
                }
                
                if (strpos($rule, 'min:') === 0 && !empty($value)) {
                    $min = (int) explode(':', $rule)[1];
                    if (strlen($value) < $min) {
                        $this->addError($field, "Tối thiểu {$min} ký tự");
                    }
                }

                if (strpos($rule, 'match:') === 0 && !empty($value)) {
                    $targetField = explode(':', $rule)[1];
                    $targetValue = trim($this->data[$targetField] ?? '');
                    if ($value !== $targetValue) {
                        $this->addError($field, 'Dữ liệu không khớp');
                    }
                }
            }
        }
        return $this->errors;
    }

    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field . '_err'])) {
            $this->errors[$field . '_err'] = $message;
        }
    }

    public function passes(): bool {
        return empty($this->errors);
    }
}