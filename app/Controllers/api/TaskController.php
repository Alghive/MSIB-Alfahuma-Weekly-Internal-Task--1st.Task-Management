<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TaskModel;
use App\Models\CommentModel;

class TaskController extends ResourceController
{
    protected $taskModel;
    protected $commentModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->commentModel = new CommentModel();
    }

    public function show($id = null)
    {

        $task = $this->model->find($id);

        if ($task) {

            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($task['user_id']);


            $response = [
                'username'    => $user['username'],
                'email'       => $task['email'],
                'title'       => $task['title'],
                'description' => $task['description'],
                'status'      => $task['status'],
                'waktu dibuat' => $task['created_at'],
            ];


            return $this->respond([
                'status' => 'Sukses',
                'message' => "Komentar untuk task '{$task['title']}' berhasil ditemukan!",
                'data' => $response
            ]);
        } else {

            return $this->failNotFound('Task tidak ditemukan');
        }
    }



    public function delete($id = null)
    {

        $task = $this->taskModel->find($id);

        if (!$task) {
            return $this->failNotFound('Task tidak berhasil ditemukan.');
        }

        $this->commentModel->where('task_id', $id)->delete();


        if ($this->taskModel->delete($id)) {
            return $this->respond([
                'status' => 'Sukses',
                'message' => "Task dan komen {$task['title']} berhasil dihapus"
            ]);
        } else {
            return $this->fail('Task gagal dihapus.');
        }
    }
}
