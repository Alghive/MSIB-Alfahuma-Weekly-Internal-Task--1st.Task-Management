<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'title', 'description', 'status', 'created_at', 'updated_at'];
    protected $returnType = 'array';
    protected $useTimestamps = true;
}
