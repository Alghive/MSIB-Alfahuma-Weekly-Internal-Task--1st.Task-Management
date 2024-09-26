<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'title', 'description', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'user_id'     => 'required',
        'title'       => 'required|min_length[3]',
        'description' => 'required|min_length[5]',
        'status'      => 'in_list[done,on progress,pending]', // Batasi status ke nilai tertentu
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID diperlukan.',
        ],
        'title' => [
            'required' => 'Judul task diperlukan.',
            'min_length' => 'Judul task harus memiliki minimal 3 karakter.'
        ],
        'description' => [
            'required' => 'Deskripsi task diperlukan.',
            'min_length' => 'Deskripsi task harus memiliki minimal 5 karakter.'
        ],
        'status' => [
            'in_list' => 'Status hanya boleh diisi dengan salah satu dari: done, on progress, pending.'
        ]
    ];
}
