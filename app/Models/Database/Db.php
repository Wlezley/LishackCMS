<?php

namespace Models;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class Db
{
    private Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function select(string $table, array $columns = ['*']): array
    {
        /** @var Selection $query */
        $query = $this->database->table($table)->select($columns);
        return $query->fetchAll();
    }

    public function update(string $table, array $data, array $where): int
    {
        $affectedRows = $this->database->table($table)->where($where)->update($data);
        return $affectedRows;
    }
}





// use \Nette\Database\Explorer;

// class Database 
// {
//     /** @var Explorer @inject */
//     public $db;


//     public function __construct()
//     {
//         //
//     }

//     // #################################################

//     public function connect()
//     {
//         # code...
//         // $this->db->connect();
//     }

//     // #################################################

//     public function query()
//     {
//         # code...
//     }

//     public function single()
//     {
//         # code...
//     }

//     public function row()
//     {
//         # code...
//     }

//     // #################################################

//     public function update()
//     {
//         # code...
//     }

//     public function insert()
//     {
//         # code...
//     }
// }