<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    // Create a new user
    public function create()
    {
        $validation = \Config\Services::validation();

        // Set validation rules for first_name and last_name
        $validation->setRules([
            'first_name' => [
                'label' => 'First Name',
                'rules' => 'required|capitalFirst',
                'errors' => [
                    'capitalFirst' => 'First name harus diawali dengan huruf kapital.'
                ]
            ],
            'last_name' => [
                'label' => 'Last Name',
                'rules' => 'required|capitalFirst',
                'errors' => [
                    'capitalFirst' => 'Last name harus diawali dengan huruf kapital.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => 'Gagal',
                'message' => 'Gagal Membuat Akun!',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name')
        ];

        if ($this->model->insert($data)) {
            $user = $this->model->find($this->model->getInsertID());
            return $this->respondCreated([
                'status'  => 'Sukses',
                'message' => 'Akun Anda sudah dibuat!',
                'data'    => [
                    'user_id'    => $user['id'],
                    'username'   => $user['username'],
                    'email'      => $user['email'],
                    'fullname'   => $user['first_name'] . ' ' . $user['last_name'],
                    'created_at' => $user['created_at'],
                ]
            ]);
        }

        return $this->respond([
            'status'  => 'Gagal',
            'message' => 'Validasi gagal, data tidak valid!',
            'errors'  => $this->model->errors()
        ], 400);
    }

    // Show a user or list of users based on filters
    public function show($id = null)
    {
        if ($id !== null) {
            $user = $this->model->find($id);
            return $user
                ? $this->respond([
                    'status'  => 'Sukses',
                    'message' => 'Data pengguna ditemukan!',
                    'data'    => [
                        'user_id'    => $user['id'],
                        'fullname'   => $user['first_name'] . ' ' . $user['last_name'],
                        'username'   => $user['username'],
                        'email'      => $user['email'],
                        'created_at' => $user['created_at'],
                    ]
                ])
                : $this->respond(['status' => 'Gagal', 'message' => 'User tidak ditemukan!'], 404);
        }
    }

    public function filterUsers()
    {
        $filters = [
            'email'    => $this->request->getGet('email'),
            'username' => $this->request->getGet('username'),
            'fullname' => $this->request->getGet('fullname')
        ];

        // Query berdasarkan filter
        if ($filters['email']) {
            $users = $this->model->like('email', $filters['email'], 'both')->findAll();
        } elseif ($filters['username']) {
            $users = $this->model->like('username', $filters['username'], 'both')->findAll();
        } elseif ($filters['fullname']) {
            $users = $this->model->like('first_name', $filters['fullname'], 'both')
                ->orLike('last_name', $filters['fullname'], 'both')
                ->findAll();
        } else {
            return $this->respond(['status' => 'Gagal', 'message' => 'Parameter pencarian tidak valid!'], 400);
        }

        if ($users) {
            $data = array_map(function ($user) {
                return [
                    'user_id' => $user['id'],
                    'fullname'   => $user['first_name'] . ' ' . $user['last_name'],
                    'username'   => $user['username'],
                    'email'      => $user['email'],
                    'created_at' => $user['created_at'],
                ];
            }, $users);

            return $this->respond([
                'status'  => 'Sukses',
                'message' => 'Data pengguna ditemukan!',
                'data'    => $data
            ]);
        }

        return $this->respond(['status' => 'Gagal', 'message' => 'Data Pengguna tidak ditemukan!'], 404);
    }
}
