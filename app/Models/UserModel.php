<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'email', 'password'];
    protected $validationRules = [
        'username' => 'required|min_length[3]|is_unique[users.username]',
        'email'    => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
    ];
    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must have at least 3 characters',
            'is_unique' => 'This username already exists'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'You must enter a valid email address',
            'is_unique' => 'This email already exists'
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must have at least 6 characters'
        ]
    ];
}
