<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'task_id', 'comment', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $validationRules = [
        'user_id'  => 'required|integer',
        'task_id'  => 'required|integer',
        'comment'  => 'required'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID diperlukan.',
            'integer'  => 'User ID harus berupa angka.'
        ],
        'task_id' => [
            'required' => 'Task ID diperlukan.',
            'integer'  => 'Task ID harus berupa angka.'
        ],
        'comment' => [
            'required'   => 'Komentar diperlukan.'
        ]
    ];
}
