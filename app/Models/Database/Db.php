<?php

namespace Models;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class Db
{
    public function __construct(private Explorer $database)
    {
        $this->database = $database;
    }

    public function select(string $table, array $columns = ['*']): array
    {
        /** @var Selection $query */
        $query = $this->database->table($table)->select($columns);
        return $query->fetchAll();
    }

    public function row(string $table, array $columns = ['*']): array
    {
        // $this->database->query();

        /** @var Selection $query */
        $query = $this->database->table($table)->select($columns)->limit(1);
        return $query->fetchAll();
    }

    public function update(string $table, array $data, array $where): int
    {
        $affectedRows = $this->database->table($table)->where($where)->update($data);
        return $affectedRows;
    }
}
