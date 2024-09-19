<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    // [POST] /users: Create
    // Endpoint: 
    public function create()
    {
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ];

        if ($this->model->insert($data)) {
            return $this->respondCreated($data);
        } else {
            return $this->failValidationErrors($this->model->errors());
        };
    }


    // [GET] /users: Show
    public function show($id = null)
    {
        $user = $this->model->find($id);
        if ($user) {
            return $this->respond([
                'status' => 'sukses, data ditemukan!',
                'data' => $user
            ]);
        } else {
            return $this->failNotFound('User tidak ditemukan!');
        }
    }
}
