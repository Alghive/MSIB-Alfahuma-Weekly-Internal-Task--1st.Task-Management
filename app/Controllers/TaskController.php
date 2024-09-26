<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\RESTful\ResourceController;

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
            'status'  => 'pending',
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
                'status' => 'sukses',
                'message' => 'Task berhasil dibuat!',
                'username' => $user['username'],
                'data task' => $response
            ]);
        } else {
            return $this->respond([
                'status' => 'gagal',
                'message' => 'Gagal membuat task!',
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
                    'status'  => 'sukses',
                    'message' => 'Task berhasil diupdate!',
                    'username' => $user['username'],
                    'data'    => [
                        'title'       => $updatedTask['title'],
                        'description' => $updatedTask['description'],
                        'status'      => $updatedTask['status'],
                        'tanggal update' => $updatedTask['updated_at'],
                    ]
                ];

                return $this->respond(json_decode(json_encode($response), true), 200);
            } else {
                $response = [
                    'status'  => 'gagal',
                    'message' => 'Validasi gagal, data tidak valid!',
                    'errors'  => $this->model->errors()
                ];

                return $this->failValidationErrors(json_decode(json_encode($response), true));
            }
        } else {
            $response = [
                'status'  => 'gagal',
                'message' => 'Task tidak ditemukan!'
            ];

            return $this->respond(json_decode(json_encode($response), true), 404);
        }
    }



    // [DELETE] /tasks: Drop
    public function delete($id = null)
    {
        $task = $this->model->find($id);

        if ($task) {
            if ($this->model->delete($id)) {
                return $this->respond([
                    'status' => 'sukses',
                    'message' => 'Task berhasil dihapus'
                ]);
            } else {
                return $this->failServerError('Terjadi kesalahan saat menghapus task');
            }
        } else {
            return $this->failNotFound('Task tidak ditemukan');
        }
    }
}
