<?php
namespace App\Core;

use PDO;
use PDOException;

abstract class Model {
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getDb(): PDO {
        return $this->db;
    }

    public function getTable(): string {
        return $this->table;
    }

    public function all(string $orderBy = 'id DESC'): array {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY {$orderBy}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find($id): ?array {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function where(string $column, $value, string $operator = '='): array {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['val' => $value]);
        return $stmt->fetchAll();
    }

    public function whereOne(string $column, $value, string $operator = '='): ?array {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['val' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int {
        $columns = array_keys($data);
        $fields = implode('`, `', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO `{$this->table}` (`{$fields}`) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update($id, array $data): bool {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "`{$key}` = :{$key}, ";
        }
        $fields = rtrim($fields, ', ');

        $sql = "UPDATE `{$this->table}` SET {$fields} WHERE `{$this->primaryKey}` = :primary_id";
        $data['primary_id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id): bool {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "`{$col}` = :{$col}";
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }

    public function rawQuery(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function rawFetch(string $sql, array $params = []): array {
        return $this->rawQuery($sql, $params)->fetchAll();
    }

    public function rawFetchOne(string $sql, array $params = []): ?array {
        $res = $this->rawQuery($sql, $params)->fetch();
        return $res ?: null;
    }
}
