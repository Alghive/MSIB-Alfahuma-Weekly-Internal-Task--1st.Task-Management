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
        // Cek apakah task ada
        $task = $this->taskModel->find($id);

        if (!$task) {
            return $this->failNotFound('Task not found');
        }

        // Hapus comments yang terkait dengan task
        $this->commentModel->where('task_id', $id)->delete();

        // Hapus task
        if ($this->taskModel->delete($id)) {
            return $this->respondDeleted(['message' => 'Task and related comments deleted successfully']);
        } else {
            return $this->fail('Failed to delete task');
        }
    }
}
