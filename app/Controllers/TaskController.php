<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\RESTful\ResourceController;


// Revisi weeklytask
class TaskController extends ResourceController
{
    protected $modelName = 'App\Models\TaskModel';
    protected $format    = 'json';

    // [POST] /tasks: Create
    public function create()
    {
        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'title'   => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'  => $this->request->getPost('status'),
        ];

        if (!$this->validate($this->model->validationRules)) {

            $errors = $this->validator->getErrors();

            return $this->respond([
                'status' => 'Gagal',
                'message' => 'Gagal membuat task!',
                'errors' => $errors
            ], 400);
        }

        if ($this->model->insert($data)) {
            $taskId = $this->model->getInsertID();
            $task = $this->model->find($taskId);

            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($this->request->getPost('user_id'));

            $response = [
                'title'       => $task['title'],
                'description' => $task['description'],
                'status'      => $task['status'],
                'waktu dibuat' => $task['created_at'],
            ];

            return $this->respondCreated([
                'status' => 'Sukses',
                'message' => 'Task berhasil dibuat!',
                'username' => $user['username'],
                'data task' => $response
            ]);
        } else {
            return $this->respond([
                'status' => 'Gagal',
                'errors' => 'Gagal membuat task!',
            ], 500);
        }
    }


    // [PUT] /tasks: Update
    public function update($id = null)
    {
        $data = $this->request->getRawInput();

        if ($this->model->find($id)) {
            if ($this->model->update($id, $data)) {
                $updatedTask = $this->model->find($id);

                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($updatedTask['user_id']);

                $response = [
                    'status'  => 'Sukses',
                    'message' => 'Task berhasil diupdate!',
                    'username' => $user['username'],
                    'data'    => [
                        'title'       => $updatedTask['title'],
                        'description' => $updatedTask['description'],
                        'status'      => $updatedTask['status'],
                        'tanggal update' => $updatedTask['updated_at'],
                    ]
                ];

                return $this->respond($response, 200);
            } else {

                $response = [
                    'status'  => 'Gagal',
                    'message' => 'Validasi gagal, data tidak valid!',
                    'errors'  => $this->model->errors()
                ];

                return $this->respond($response, 400);
            }
        } else {
            $response = [
                'status'  => 'Gagal',
                'message' => 'Task tidak ditemukan!'
            ];

            return $this->respond($response, 404);
        }
    }




    // [DELETE] /tasks: Drop
    public function delete($id = null)
    {
        $task = $this->model->find($id);

        if ($task) {
            if ($this->model->delete($id)) {
                return $this->respond([
                    'status' => 'Sukses',
                    'message' => 'Task berhasil dihapus'
                ]);
            } else {
                return $this->failServerError('Terjadi kesalahan saat menghapus task');
            }
        } else {
            return $this->failNotFound('Task tidak ditemukan');
        }
    }

    // [GET] /tasks: Show
    public function show($id = null)
    {
        // Ambil data task berdasarkan ID
        $task = $this->model->find($id);

        if ($task) {
            // Ambil user berdasarkan user_id dari task
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($task['user_id']);

            // Pastikan user ditemukan untuk menghindari error
            if ($user) {
                // Gabungkan data task dengan username dan email dari user
                $response = [
                    'username'    => $user['username'],   // Tampilkan username dari user
                    'email'       => $user['email'],      // Tampilkan email dari user
                    'title'       => $task['title'],
                    'description' => $task['description'],
                    'status'      => $task['status'],
                    'waktu dibuat' => $task['created_at'], // Tampilkan tanggal dibuat
                ];

                // Return JSON response dengan status sukses
                return $this->respond([
                    'status'  => 'Sukses',
                    'message' => "Task '{$task['title']}' berhasil ditemukan!",
                    'data'    => $response
                ]);
            } else {
                // Jika user tidak ditemukan
                return $this->failNotFound('Pengguna tidak ditemukan');
            }
        } else {
            // Jika task tidak ditemukan
            return $this->failNotFound('Task tidak ditemukan');
        }
    }
}
