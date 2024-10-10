<?php

namespace App\Models;

use CodeIgniter\Model;

class TokenSessionModel extends Model
{

    protected $table = "token_session";
    protected $primaryKey = "id";
    protected $allowedFields = ["token", "user_id", "status", "valid_until", "created_at", "updated_at"];
}
