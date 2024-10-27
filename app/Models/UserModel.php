<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // protected $table = 'users';
    // protected $primaryKey = 'id';
    // protected $allowedFields = ['username', 'email', 'password', 'created_at'];
    // protected $validationRules = [
    //     'username' => 'required|min_length[3]|is_unique[users.username]',
    //     'email'    => 'required|valid_email|is_unique[users.email]',
    //     'password' => 'required|min_length[6]',
    // ];
    // protected $validationMessages = [
    //     'username' => [
    //         'required' => 'Nama pengguna diperlukan',
    //         'min_length' => 'Nama pengguna harus memiliki minimal 3 karakter',
    //         'is_unique' => 'Upss! Username ini sudah digunakan'
    //     ],
    //     'email' => [
    //         'required' => 'Email pengguna diperlukan',
    //         'valid_email' => 'Anda harus memasukkan alamat email yang valid',
    //         'is_unique' => 'Upss! Email ini sudah digunakan'
    //     ],
    //     'password' => [
    //         'required' => 'Password pengguna diperlukan',
    //         'min_length' => 'Password harus memiliki minimal 6 karakter'
    //     ]
    // ];

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'email', 'password', 'created_at', 'first_name', 'last_name'];
    protected $validationRules = [
        'username' => 'required|min_length[3]|is_unique[users.username]',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
        // 'first_name' => 'required|capitalFirst',
        // 'last_name' => 'required|capitalFirst',
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username harus diisi!',
            'min_length' => 'Username harus memiliki minimal 3 karakter!',
            'is_unique' => 'Maaf, username ini sudah digunakan!'
        ],
        'email' => [
            'required' => 'Email harus diisi!',
            'valid_email' => 'Anda harus memasukkan alamat email yang valid!',
            'is_unique' => 'Maaf, email ini sudah digunakan!'
        ],
        'password' => [
            'required' => 'Password harus diisi!',
            'min_length' => 'Password harus memiliki minimal 6 karakter!'
        ],
        // 'first_name' => [
        //     'required' => 'First name harus diisi!',
        //     'capitalFirst' => 'First name harus diawali huruf kapital!'
        // ],
        // 'last_name' => [
        //     'required' => 'Last name harus diisi!',
        //     'capitalFirst' => 'Last name harus diawali huruf kapital!'
        // ]
    ];

    protected function capitalFirst(string $str): bool
    {
        return ctype_upper($str[0]);
    }
}
