<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\TokenSessionModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Daftar endpoint yang tidak memerlukan token
        $excludedRoutes = ['auth/login', 'auth/logout']; // Logout tidak perlu token

        // Ambil URI yang sedang diakses
        $currentRoute = $request->getUri()->getPath();

        // Cek apakah route saat ini ada di daftar yang dikecualikan
        foreach ($excludedRoutes as $route) {
            if (strpos($currentRoute, $route) !== false) {
                return; // Skip pengecekan token jika route ada dalam daftar
            }
        }

        // Lanjutkan dengan pengecekan token hanya untuk route yang lain
        $authHeader = $request->getServer('HTTP_AUTHORIZATION');

        if (!$authHeader) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Token tidak ditemukan, harap login terlebih dahulu'
                ]);
        }

        // Pisahkan Bearer dari token
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Format token tidak valid'
                ]);
        }

        // Cek token di tabel token_session
        $tokenModel = new TokenSessionModel();
        $tokenData = $tokenModel->where('token', $token)
            ->where('status', 1)  // Cek token aktif
            ->first();

        if (!$tokenData) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Token tidak valid atau sudah tidak aktif'
                ]);
        }

        // Cek apakah token masih berlaku (belum kedaluwarsa)
        $currentTime = time();
        if (strtotime($tokenData['valid_until']) < $currentTime) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Token telah kedaluwarsa'
                ]);
        }

        // Lanjutkan request jika token valid
        return;
    }



    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu diproses di sini
    }

    // Helper function untuk membuat response unauthorized
    private function unauthorizedResponse($message)
    {
        return \Config\Services::response()
            ->setStatusCode(401)
            ->setJSON([
                'status' => 'Gagal',
                'message' => $message
            ]);
    }
}
