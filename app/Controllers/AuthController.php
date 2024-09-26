<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends ResourceController
{
    public function login()
    {

        $login = $this->request->getPost('username/email');
        $password = $this->request->getPost('password');

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user) {
            return $this->failUnauthorized('User dengan username/email tersebut tidak ditemukan');
        }

        if (!password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Password salah');
        }

        $key = getenv('JWT_SECRET');
        $iat = time();
        $exp = $iat + 1800;

        $payload = [
            'iat' => $iat,
            'exp' => $exp,
            'uid' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->respond([
            'status' => 'sukses',
            'message' => 'Login berhasil',
            'token' => $token,
            'data' => [
                'username' => $user['username'],
                'email' => $user['email'],
                'created_at' => $user['created_at']
            ]
        ]);
    }
}
