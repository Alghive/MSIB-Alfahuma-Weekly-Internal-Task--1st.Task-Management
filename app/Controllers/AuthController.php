<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TokenSessionModel;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;  // Tambahkan library JWT

class AuthController extends ResourceController
{
    private $secretKey = 'your-secret-key'; // Ganti dengan secret key JWT Anda

    public function login()
    {
        $userModel = new UserModel();
        $tokenModel = new TokenSessionModel();

        // Ambil data login dari request
        $login = $this->request->getPost('username/email');
        $password = $this->request->getPost('password');

        // Cari user berdasarkan username atau email
        $user = $userModel->where('username', $login)->orWhere('email', $login)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Username atau password salah');
        }

        // Jika login berhasil, buat token baru
        $payload = [
            'user_id' => $user['id'],
            'exp' => time() + 3600 // Token berlaku selama 1 menit
        ];

        try {
            $token = JWT::encode($payload, $this->secretKey, 'HS256');
        } catch (\Exception $e) {
            log_message('error', 'Error encoding JWT: ' . $e->getMessage());
            return $this->failServerError('Gagal membuat token: ' . $e->getMessage());
        }

        // Siapkan data untuk disimpan ke tabel token_session
        $insertData = [
            'token'      => $token,
            'user_id'    => $user['id'],
            'status'     => 1,  // Token aktif
            'valid_until' => date('Y-m-d H:i:s', strtotime('+1 hour')), // Token berlaku selama 1 menit
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Simpan token ke tabel token_session
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

        // Cari token aktif berdasarkan user_id
        $tokenData = $tokenModel->where('user_id', $userId)
            ->where('status', 1) // Token aktif
            ->first();

        if (!$tokenData) {
            return $this->failNotFound('User atau token tidak ditemukan');
        }

        // Ambil username dari tabel users berdasarkan user_id
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }

        // Update status token menjadi invalid (status = 0)
        try {
            $tokenModel->update($tokenData['id'], ['status' => 0]);

            return $this->respond([
                'status' => 'Sukses',
                'message' => 'Logout berhasil',
                'data' => [
                    'user_id' => $userId,
                    'username' => $user['username'], // Ambil dari tabel users
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
