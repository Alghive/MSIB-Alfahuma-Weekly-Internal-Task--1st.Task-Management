<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['task_id', 'comment', 'created_at', 'user_id'];
    protected $returnType = 'array';
}
