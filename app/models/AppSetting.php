<?php
namespace App\Models;

use App\Core\Model;

class AppSetting extends Model {
    protected string $table = 'app_settings';

    public function get(string $key, ?string $default = null): ?string {
        $row = $this->whereOne('setting_key', $key);
        return $row ? $row['setting_value'] : $default;
    }

    public function set(string $key, string $value): bool {
        $row = $this->whereOne('setting_key', $key);
        if ($row) {
            return $this->update($row['id'], ['setting_value' => $value]);
        } else {
            $this->create(['setting_key' => $key, 'setting_value' => $value]);
            return true;
        }
    }
}
