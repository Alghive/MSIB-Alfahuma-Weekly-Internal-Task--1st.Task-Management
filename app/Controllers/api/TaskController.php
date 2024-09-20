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

    // Method untuk menghapus task
    public function delete($id = null)
    {

        $task = $this->taskModel->find($id);

        if (!$task) {
            return $this->failNotFound('Task tidak berhasil ditemukan.');
        }

        $this->commentModel->where('task_id', $id)->delete();


        if ($this->taskModel->delete($id)) {
            return $this->respond([
                'status' => 'sukses',
                'message' => 'Task dan komen berhasil dihapus'
            ]);
        } else {
            return $this->fail('Task gagal dihapus.');
        }
    }
}
