<?php

namespace App\Controllers;

use App\Models\CommentModel;
use CodeIgniter\RESTful\ResourceController;

class CommentController extends ResourceController
{
    protected $modelName = 'App\Models\CommentModel';
    protected $format    = 'json';

    // [POST] /tasks: Create
    public function create()
    {
        $data = [
            'task_id' => $this->request->getPost('task_id'),
            'comment' => $this->request->getPost('comment'),
        ];

        if ($this->model->insert($data)) {
            return $this->respondCreated($data);
        } else {
            return $this->failValidationErrors($this->model->errors());
        }
    }

    // [GET] /tasks: Show
    public function show($task_id = null)
    {
        $comments = $this->model->where('task_id', $task_id)->findAll();
        if ($comments) {
            return $this->respond($comments);
        } else {
            return $this->failNotFound('No comments found for this task');
        }
    }
}
