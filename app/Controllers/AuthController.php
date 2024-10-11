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
                'valid_until' => date('Y-m-d H:i:s', strtotime('+1 minute')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }







    public function logout($userId)
    {
        $tokenModel = new TokenSessionModel();
        $userModel = new UserModel();

        $tokenData = $tokenModel->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if (!$tokenData) {
            return $this->failNotFound('User atau token tidak ditemukan');
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }

        try {
            $tokenModel->update($tokenData['id'], ['status' => 0]);

            return $this->respond([
                'status' => 'Sukses',
                'message' => 'Logout berhasil',
                'data' => [
                    'user_id' => $userId,
                    'username' => $user['username'],
                    'status_token' => 'invalid',
                    'valid_until' => $tokenData['valid_until'],
                    'created_at' => $tokenData['created_at'],
                    'updated_at' => $tokenData['updated_at']
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error during logout: ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan saat logout.');
        }
    }
}
