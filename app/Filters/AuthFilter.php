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
        $excludedRoutes = ['auth/login', 'auth/logout'];

        $currentRoute = $request->getUri()->getPath();

        foreach ($excludedRoutes as $route) {
            if (strpos($currentRoute, $route) !== false) {
                return;
            }
        }

        $authHeader = $request->getServer('HTTP_AUTHORIZATION');

        if (!$authHeader) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Token tidak ditemukan, harap login terlebih dahulu'
                ]);
        }

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

        $tokenModel = new TokenSessionModel();
        $tokenData = $tokenModel->where('token', $token)
            ->where('status', 1)
            ->first();

        if (!$tokenData) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Token tidak valid atau sudah tidak aktif'
                ]);
        }

        $currentTime = time();
        if (strtotime($tokenData['valid_until']) < $currentTime) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'Gagal',
                    'message' => 'Token telah kedaluwarsa'
                ]);
        }

        return;
    }



    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

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
