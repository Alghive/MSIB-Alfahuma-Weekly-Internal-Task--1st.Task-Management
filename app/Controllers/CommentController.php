<?php

namespace App\Controllers;

use App\Models\CommentModel;
use CodeIgniter\RESTful\ResourceController;

// new CommentModel();
class CommentController extends ResourceController
{
    protected $modelName = 'App\Models\CommentModel';
    protected $format    = 'json';

    // [POST] /tasks: Create
    public function create()
    {

        $task_id = $this->request->getPost('task_id');

        $data = [
            'task_id'  => $task_id,
            'user_id'  => $this->request->getPost('user_id'),
            'comment'  => $this->request->getPost('comment'),
        ];

        if ($this->model->insert($data)) {
            $taskModel = new \App\Models\TaskModel();
            $task = $taskModel->find($task_id);

            $commentId = $this->model->getInsertID();
            $comment = $this->model->find($commentId);

            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($data['user_id']);

            $response = [
                'task_id'    => $comment['task_id'],
                'judul task' => $task['title'],
                'username'   => $user['username'],
                'comment'    => $comment['comment'],
                'waktu dibuat' => $comment['created_at'],
            ];

            return $this->respondCreated([
                'status' => 'Sukses',
                'message' => "Hi {$user['username']}, komentar Anda berhasil ditambahkan!",
                'data comment' => $response
            ]);
        } else {
            return $this->respondCreated([
                'status' => 'Gagal',
                'message' => $this->model->errors()
            ]);
        }
    }



    // [GET] /tasks: Show
    public function show($task_id = null)
    {
        $taskModel = new \App\Models\TaskModel();
        $task = $taskModel->find($task_id);

        if (!$task) {
            return $this->failNotFound('Task tidak ditemukan!');
        }

        $comments = $this->model->where('task_id', $task_id)->findAll();

        if ($comments) {
            $response = [];

            foreach ($comments as $comment) {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($comment['user_id']);

                $response[] = [
                    'username'    => $user['username'],
                    'judul task' => $task['title'],
                    'comment'     => $comment['comment'],
                    'waktu dibuat' => $comment['created_at'],
                ];
            }

            return $this->respond([
                'status' => 'Sukses',
                'message' => "Komentar untuk task '{$task['title']}' berhasil ditemukan!",
                'data comments' => $response
            ]);
        } else {
            return $this->failNotFound("Komentar untuk task '{$task['title']}' tidak ditemukan!");
        }
    }
}
