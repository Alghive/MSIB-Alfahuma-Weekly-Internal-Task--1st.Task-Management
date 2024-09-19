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
            'description'   => $this->request->getPost('description'),
            'status'  => 'pending',
        ];

        if ($this->model->insert($data)) {
            return $this->respondCreated($data);
        } else {
            return $this->failValidationErrors($this->model->errors());
        }
    }

    // [GET] /tasks: Show
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('Task not found');
        }
    }

    // [PUT] /tasks: Update
    public function update($id = null)
    {
        $data = $this->request->getRawInput();
        if ($this->model->update($id, $data)) {
            return $this->respond($data);
        } else {
            return $this->failValidationErrors($this->model->errors());
        }
    }

    // [DELETE] /tasks: Drop
    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['id' => $id]);
        } else {
            return $this->failNotFound('Task not found');
        }
    }
}
