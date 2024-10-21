<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TokenSessionModel;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;

class AuthController extends ResourceController
{
    private $secretKey = 'your-secret-key';

    public function login()
    {
        $userModel = new UserModel();
        $tokenModel = new TokenSessionModel();

        $login = $this->request->getPost('username/email');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $login)->orWhere('email', $login)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Username atau password salah');
        }

        session()->set('user_id', $user['id']);

        $tokenModel->where('user_id', $user['id'])->where('status', 1)
            ->set('status', 0)
            ->update();

        $payload = [
            'user_id' => $user['id'],
            'exp' => time() + 3600
        ];

        try {
            $token = JWT::encode($payload, $this->secretKey, 'HS256');
        } catch (\Exception $e) {
            log_message('error', 'Error encoding JWT: ' . $e->getMessage());
            return $this->failServerError('Gagal membuat token: ' . $e->getMessage());
        }

        $insertData = [
            'token'      => $token,
            'user_id'    => $user['id'],
            'status'     => 1,
            'valid_until' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!$tokenModel->insert($insertData)) {
            log_message('error', 'Error inserting token: ' . json_encode($tokenModel->errors()));
            return $this->failServerError('Gagal menyimpan token');
        }

        return $this->respond([
            'status' => 'Sukses',
            'message' => 'Login berhasil',
            'data' => [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'token' => $token,
                'status' => 'valid',
                'masa aktif token' => '1 Jam',
                'valid_until' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }


    public function logout()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            return $this->failUnauthorized('User tidak ditemukan atau tidak aktif.');
        }

        $tokenModel = new TokenSessionModel();
        $tokenModel->where('user_id', $userId)
            ->set('status', 0)
            ->update();

        session()->destroy();

        return $this->respond([
            'status' => 'Sukses',
            'message' => 'Logout berhasil'
        ]);
    }
}
