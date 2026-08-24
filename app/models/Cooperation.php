<?php
namespace App\Models;

use App\Core\Model;

class Cooperation extends Model {
    protected string $table = 'cooperations';

    public function allWithDaysRemaining(): array {
        $sql = "SELECT c.*, DATEDIFF(c.end_date, CURDATE()) as days_remaining 
                FROM cooperations c 
                ORDER BY c.end_date ASC";
        return $this->rawFetch($sql);
    }
}
