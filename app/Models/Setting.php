<?php
declare(strict_types=1);

class Setting extends BaseModel {
    protected string $table = 'settings';

    /**
     * Lấy giá trị cấu hình theo Key
     */
    public function getSetting(string $key, string $default = ''): string {
        $this->db->query("SELECT setting_value FROM {$this->table} WHERE setting_key = :key");
        $this->db->bind(':key', $key);
        $result = $this->db->single();
        return $result ? $result->setting_value : $default;
    }

    /**
     * Cập nhật giá trị cấu hình
     */
    public function updateSetting(string $key, string $value): bool {
        // Kiểm tra xem key đã tồn tại chưa
        $this->db->query("SELECT id FROM {$this->table} WHERE setting_key = :key");
        $this->db->bind(':key', $key);
        $exists = $this->db->single();

        if ($exists) {
            $this->db->query("UPDATE {$this->table} SET setting_value = :value WHERE setting_key = :key");
        } else {
            $this->db->query("INSERT INTO {$this->table} (setting_key, setting_value) VALUES (:key, :value)");
        }
        
        $this->db->bind(':key', $key);
        $this->db->bind(':value', $value);
        return $this->db->execute();
    }
}