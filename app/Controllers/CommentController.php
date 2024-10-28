<?php

namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\UserModel;
use App\Models\TaskModel;
use CodeIgniter\RESTful\ResourceController;

class CommentController extends ResourceController
{
    protected $modelName = 'App\Models\CommentModel';
    protected $format    = 'json';

    public function create()
    {
        $task_id = $this->request->getPost('task_id');

        $data = [
            'task_id' => $task_id,
            'user_id' => $this->request->getPost('user_id'),
            'comment' => $this->request->getPost('comment'),
        ];

        if ($this->model->insert($data)) {
            $taskModel = new \App\Models\TaskModel();
            $task = $taskModel->find($task_id);
            $deadline = $task['deadline'];

            $commentId = $this->model->getInsertID();
            $comment = $this->model->find($commentId);
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($data['user_id']);

            $response = [
                'user_id' => $comment['user_id'],
                'task_id'    => $comment['task_id'],
                'comment_id' => $comment['id'],
                'fullname' => $user['first_name'] . ' ' . $user['last_name'],
                'task_title' => $task['title'],
                'comment' => $comment['comment'],
                'deadline' => $deadline,
                'created_at' => $comment['created_at'],
                'updated_at' => $comment['updated_at']
            ];

            return $this->respondCreated([
                'status' => 'Sukses',
                'message' => "Hi {$user['first_name']} {$user['last_name']}, komentar Anda berhasil ditambahkan!",
                'data_comment' => $response
            ]);
        } else {
            return $this->respondCreated([
                'status' => 'Gagal',
                'message' => $this->model->errors()
            ]);
        }
    }

    public function show($task_id = null)
    {
        $commentText = $this->request->getGet('comment');
        $username = $this->request->getGet('username');
        $fullname = $this->request->getGet('fullname');

        $commentModel = new CommentModel();
        $userModel = new UserModel();
        $taskModel = new TaskModel();

        $builder = $commentModel->builder()
            ->join('users', 'comments.user_id = users.id', 'left')
            ->join('tasks', 'comments.task_id = tasks.id', 'left');

        if ($task_id !== null) {
            $builder->where('comments.task_id', $task_id);
        }

        if ($commentText) {
            $builder->like('comments.comment', $commentText, 'both');
        }
        if ($username) {
            $builder->like('users.username', $username, 'both');
        }
        if ($fullname) {
            $builder->groupStart()
                ->like('users.first_name', $fullname, 'both')
                ->orLike('users.last_name', $fullname, 'both')
                ->orLike('CONCAT(users.first_name, " ", users.last_name)', $fullname, 'both')
                ->groupEnd();
        }

        $comments = $builder->select('
            comments.user_id,
            comments.task_id,
            comments.id AS comment_id,
            CONCAT(users.first_name, " ", users.last_name) AS fullname, 
            tasks.title AS title, 
            comments.comment, 
            tasks.deadline AS deadline,
            comments.created_at,
            comments.updated_at')
            ->get()
            ->getResultArray();

        if ($comments) {
            $response = [
                'status' => 'Sukses',
                'message' => 'Komentar berhasil ditemukan!',
                'data_comments' => $comments
            ];
            return $this->respond($response, 200);
        } else {
            return $this->respond(['status' => 'Gagal', 'message' => 'Komentar tidak ditemukan!'], 404);
        }
    }
}
