<?php

namespace App\Models;

use App\Models\Db;

class User
{
    public const TABLE = 'user';

    private Db $db;
    public int $user_id;
    private array $data;


    public function __construct(Db $db, int $user_id = null)
    {
        $this->db = $db;

        if ($user_id) {
            $this->load($user_id);
        }
    }

    public function load(int $user_id = null): void
    {
        if ($user_id) {
            $this->user_id = $user_id;
        }

        $data = $this->db->query("SELECT FROM " . self::TABLE . " WHERE `id` = " . $this->user_id);

        if ($data) {
            $this->data = $data;
        } else {
            // TODO: "NOT-FOUND" Exception?
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getSession(): array
    {
        return $this->data['session_id'];
    }
}
