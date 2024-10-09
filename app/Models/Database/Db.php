<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\Database\ResultSet;

use Nette\Database\Table\Selection;


class Db
{
    public function __construct(private Explorer $db)
    {
        $this->db = $db;
    }

    public function query($query, ...$params): array
    {
        $query = trim($query);
        $query = preg_replace('!\s+!', ' ', $query); // Replace Multiple Spaces with One Space

        // TODO !!!!!!!

        /** @var ResultSet $result */
        $result = $this->db->query($query, $params);
        return $result->fetchAll();
    }

    public function insert(string $table, array $data): string
    {
        $this->db->table($table)->insert($data);
        return $this->db->getInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $affectedRows = $this->db->table($table)->where($where)->update($data);
        return $affectedRows;
    }

    public function select(string $table, array $where, array $columns = ['*'], $limit = null, $offset = null): array
    {
        /** @var Selection $query */
        $query = $this->db->table($table)->where($where)->select($columns)->limit($limit, $offset);
        return $query->fetchAll();
    }

    public function row(string $table, array $where, array $columns = ['*']): array
    {
        return $this->select($table, $where, $columns, 1, 0);
    }
}
