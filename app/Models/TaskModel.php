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

    protected $validationRules = [
        'user_id' => 'required',
        'title' => 'required|min_length[3]',
        'description' => 'required|min_length[5]'
    ];
    protected $validationMessages = [
        'user_id' => [
            'required' => 'Field user_id diperlukan.'
        ],
        'title' => [
            'required' => 'Judul diperlukan.',
            'min_length' => 'Judul harus memiliki minimal 3 karakter.'
        ],
        'description' => [
            'required' => 'Deskripsi task diperlukan.',
            'min_length' => 'Deskripsi task harus memiliki minimal 5 karakter.'
        ]
    ];
}
