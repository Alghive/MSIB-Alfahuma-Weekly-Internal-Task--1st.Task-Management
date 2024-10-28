<?php

/**
 * Kode Program khusus menangani alur task pengguna
 * Aksi yang tersedia:
 * - Create
 * - Show
 * - Update
 * - Drop
 */

/**
 * 
 * 
 */

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\RESTful\ResourceController;

class TaskController extends ResourceController
{
    protected $modelName = 'App\Models\TaskModel';
    protected $format    = 'json';

    public function create()
    {
        $deadlineInput = $this->request->getPost('deadline');

        if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $deadlineInput)) {
            $deadlineDate = \DateTime::createFromFormat('d/m/Y', $deadlineInput);


            if ($deadlineDate) {
                $deadline = $deadlineDate->format('Y-m-d 23:59:00');
            } else {
                return $this->respond([
                    'status' => 'Gagal',
                    'message' => 'Format tanggal tidak valid!',
                ], 400);
            }
        } else {
            $intervalMap = [
                'hari' => 'day',
                'minggu' => 'week',
                'bulan' => 'month',
                'tahun' => 'year'
            ];

            $now = time();

            preg_match_all('/(\d+)\s*(hari|minggu|bulan|tahun)/', $deadlineInput, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $jumlah = (int) $match[1];
                $satuan = $intervalMap[$match[2]];

                $now = strtotime("+$jumlah $satuan", $now);
            }

            $deadline = date('Y-m-d 23:59:00', $now);
        }

        $data = [
            'user_id'    => $this->request->getPost('user_id'),
            'title'      => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'     => $this->request->getPost('status'),
            'deadline'   => $deadline,
        ];

        if (!$this->validate($this->model->validationRules)) {
            $errors = $this->validator->getErrors();

            return $this->respond([
                'status'  => 'Gagal',
                'message' => 'Gagal membuat task!',
                'errors'  => $errors
            ], 400);
        }

        if ($this->model->insert($data)) {
            $taskId = $this->model->getInsertID();
            $task = $this->model->find($taskId);

            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($this->request->getPost('user_id'));

            $response = [
                'user_id' => $task['user_id'],
                'task_id' => $task['id'],
                'fullname' => $user['first_name'] . ' ' . $user['last_name'],
                'title'       => $task['title'],
                'description' => $task['description'],
                'status'      => $task['status'],
                'deadline'    => $task['deadline'],
                'created_at'  => $task['created_at'],
                'updated_at' => $task['updated_at']
            ];

            return $this->respondCreated([
                'status'   => 'Sukses',
                'message'  => 'Task berhasil dibuat!',
                'data_task' => $response
            ]);
        } else {
            return $this->respond([
                'status' => 'Gagal',
                'errors' => 'Gagal membuat task!',
            ], 500);
        }
    }

    private function parseDeadline($input)
    {
        $timeUnits = [
            'hari' => 86400,
            'minggu' => 604800,
            'bulan' => 2592000,
            'tahun' => 31536000
        ];

        $input = strtolower($input);
        preg_match_all('/(\d+)\s*(hari|minggu|bulan|tahun)/', $input, $matches, PREG_SET_ORDER);

        $totalSeconds = 0;

        foreach ($matches as $match) {
            $amount = (int) $match[1];
            $unit = $match[2];

            if (isset($timeUnits[$unit])) {
                $totalSeconds += $amount * $timeUnits[$unit];
            } else {
                return false;
            }
        }

        return $totalSeconds;
    }


    public function show($id = null)
    {
        if ($id !== null) {
            $task = $this->model->find($id);

            if ($task) {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($task['user_id']);

                if ($user) {
                    $now = new \DateTime();
                    $deadline = new \DateTime($task['deadline']);

                    if ($now < $deadline) {
                        $deadlineStatus = 'Tepat waktu';
                    } elseif ($now->format('Y-m-d') === $deadline->format('Y-m-d')) {
                        $deadlineStatus = 'Hari deadline';
                    } else {
                        $deadlineStatus = 'Telat';
                    }

                    $response = [
                        'user_id' => $task['user_id'],
                        'task_id' => $task['id'],
                        'fullname' => $user['first_name'] . ' ' . $user['last_name'],
                        'title' => $task['title'],
                        'description' => $task['description'],
                        'task_status' => $task['status'],
                        'deadline' => $task['deadline'],
                        'deadline_status' => $deadlineStatus,
                        'created_at' => $task['created_at'],
                        'updated_at' => $task['updated_at']
                    ];

                    return $this->respond([
                        'status'  => 'Sukses',
                        'message' => "Task '{$task['title']}' berhasil ditemukan!",
                        'data'    => $response
                    ]);
                } else {
                    return $this->failNotFound('Pengguna tidak ditemukan');
                }
            } else {
                return $this->failNotFound('Task tidak ditemukan');
            }
        }
        return $this->respond(
            [
                'status' => 'Gagal',
                'message' => 'ID Task tidak ditemukan!'
            ],
            400
        );
    }

    public function filterTasks()
    {
        $title = $this->request->getGet('title');
        $description = $this->request->getGet('description');
        $createdAt = $this->request->getGet('created_at');
        $status = $this->request->getGet('status');
        $deadlineRange = $this->request->getGet('deadline_range');

        $query = $this->model;

        if ($title) {
            $query = $query->like('title', $title, 'both');
        }
        if ($description) {
            $query = $query->like('description', $description, 'both');
        }
        if ($createdAt) {
            $query = $query->where('DATE(created_at)', $createdAt);
        }
        if ($status) {
            $query = $query->where('status', $status);
        }

        if ($deadlineRange) {
            $dates = $this->parseDeadlineRange($deadlineRange);
            if ($dates) {
                $query = $query->where('deadline >=', $dates['start'])->where('deadline <=', $dates['end']);
            }
        }

        $tasks = $query->findAll();
        if ($tasks) {
            $response = [
                'status'  => 'Sukses',
                'message' => 'Task berhasil ditemukan!',
                'data'    => []
            ];

            foreach ($tasks as $task) {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($task['user_id']);

                $now = new \DateTime();
                $deadline = new \DateTime($task['deadline']);
                $deadlineStatus = ($now < $deadline) ? 'Tepat waktu' : (($now->format('Y-m-d') === $deadline->format('Y-m-d')) ? 'Hari deadline' : 'Telat');

                $response['data'][] = [
                    'user_id' => $user['id'],
                    'task_id' => $task['id'],
                    'fullname ' => $user['first_name'] . ' ' . $user['last_name'],
                    'title'          => $task['title'],
                    'description'    => $task['description'],
                    'task_status'    => $task['status'],
                    'deadline'       => $task['deadline'],
                    'deadline_status' => $deadlineStatus,
                    'created_at'     => $task['created_at'],
                    'updated_at' => $task['updated_at']
                ];
            }

            return $this->respond($response, 200);
        } else {
            return $this->respond(['status' => 'Gagal', 'message' => 'Task tidak ditemukan!'], 404);
        }
    }

    private function parseDeadlineRange($input)
    {
        $totalDays = 0;

        $parts = preg_split('/\s+/', trim($input));

        for ($i = 0; $i < count($parts); $i += 2) {
            $value = (int)$parts[$i];
            $unit = strtolower($parts[$i + 1] ?? '');

            if ($unit === 'hari') {
                $totalDays += $value;
            } elseif ($unit === 'minggu') {
                $totalDays += $value * 7;
            } elseif ($unit === 'bulan') {
                $totalDays += $value * 30;
            } elseif ($unit === 'tahun') {
                $totalDays += $value * 365;
            }
        }

        $now = new \DateTime();
        $startDeadline = $now->modify("+$totalDays days")->format('Y-m-d H:i:s');
        $endDeadline = $now->modify("+$totalDays days")->format('Y-m-d H:i:s');

        return [
            'start' => $startDeadline,
            'end' => $endDeadline,
        ];
    }




    public function getTasksByUser($userId = null)
    {
        $taskModel = new TaskModel();
        $userModel = new \App\Models\UserModel();

        $title = $this->request->getGet('title');
        $description = $this->request->getGet('description');
        $createdAt = $this->request->getGet('created_at');
        $status = $this->request->getGet('status');

        // Query berdasarkan user_id jika ada
        if ($userId) {
            $query = $taskModel->where('user_id', $userId);
        } else {
            // Jika tidak ada userId, ambil semua task
            $query = $taskModel;
        }

        // Tambahkan filter jika ada
        if ($title) {
            $query = $query->like('title', $title, 'both');
        }
        if ($description) {
            $query = $query->like('description', $description, 'both');
        }
        if ($createdAt) {
            $query = $query->where('DATE(created_at)', $createdAt);
        }
        if ($status) {
            $query = $query->where('status', $status);
        }

        // Ambil semua tasks yang sesuai dengan filter
        $tasks = $query->findAll();

        // Cek apakah tasks ditemukan
        if ($tasks) {
            $taskData = [];

            foreach ($tasks as $task) {
                // Ambil data user berdasarkan user_id dari task
                $user = $userModel->find($task['user_id']);
                $fullname = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Tidak Diketahui'; // Menggabungkan nama

                $now = new \DateTime(); // Waktu sekarang
                $deadline = new \DateTime($task['deadline']); // Deadline dari task

                // Tentukan status deadline
                if ($now < $deadline) {
                    $deadlineStatus = 'Tepat waktu';
                } elseif ($now->format('Y-m-d') === $deadline->format('Y-m-d')) {
                    $deadlineStatus = 'Hari deadline';
                } else {
                    $deadlineStatus = 'Telat';
                }

                $taskData[] = [
                    'user_id' => $task['user_id'],
                    'task_id' => $task['id'],
                    'fullname'        => $fullname,
                    'title'           => $task['title'],
                    'description'     => $task['description'],
                    'status'          => $task['status'],
                    'deadline'        => $task['deadline'],
                    'deadline_status' => $deadlineStatus,
                    'created_at'      => $task['created_at'],
                    'updated_at' => $task['updated_at']
                ];
            }

            return $this->respond([
                'status' => 'Sukses',
                'message' => 'Tasks berhasil ditemukan!',
                'data' => $taskData
            ]);
        } else {
            return $this->respond([
                'status' => 'Gagal',
                'message' => 'Tasks tidak ditemukan!'
            ]);
        }
    }





    // Fungsi untuk mem-parsing rentang deadline
    private function parseDeadlineRangeInput($input)
    {
        // Definisikan variabel untuk menghitung total hari
        $totalDays = 0;

        // Pisahkan input berdasarkan spasi
        $parts = preg_split('/\s+/', trim($input));

        // Loop untuk menghitung total hari
        for ($i = 0; $i < count($parts); $i += 2) {
            $value = (int)$parts[$i];
            $unit = strtolower($parts[$i + 1] ?? '');

            if ($unit === 'hari') {
                $totalDays += $value;
            } elseif ($unit === 'minggu') {
                $totalDays += $value * 7; // 1 minggu = 7 hari
            } elseif ($unit === 'bulan') {
                $totalDays += $value * 30; // Asumsi 1 bulan = 30 hari
            } elseif ($unit === 'tahun') {
                $totalDays += $value * 365; // Asumsi 1 tahun = 365 hari
            }
        }

        // Hitung tanggal mulai dan akhir
        $now = new \DateTime();
        $startDeadline = $now->modify("+$totalDays days")->format('Y-m-d H:i:s');
        $endDeadline = $now->modify("+$totalDays days")->format('Y-m-d H:i:s');

        return [
            'start' => $startDeadline,
            'end' => $endDeadline,
        ];
    }


    // [PUT] /tasks: Update
    public function update($id = null)
    {
        $data = $this->request->getPost();

        // Cek apakah ada input deadline
        if ($this->request->getPost('deadline')) {
            $deadlineInput = $this->request->getPost('deadline');

            // Cek apakah input dalam format tanggal/bulan/tahun
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $deadlineInput)) {
                $deadlineDate = \DateTime::createFromFormat('d/m/Y', $deadlineInput);

                if ($deadlineDate) {
                    // Set jam ke 23:59:00
                    $deadline = $deadlineDate->format('Y-m-d 23:59:00');
                } else {
                    return $this->respond([
                        'status' => 'Gagal',
                        'message' => 'Format tanggal tidak valid!',
                    ], 400);
                }
            } else {
                // Jika input menggunakan format deskriptif (hari, minggu, bulan, tahun)
                $intervalMap = [
                    'hari' => 'day',
                    'minggu' => 'week',
                    'bulan' => 'month',
                    'tahun' => 'year'
                ];

                $now = time(); // Waktu sekarang

                // Regex untuk menemukan angka dan satuan waktu (hari, minggu, bulan, tahun)
                preg_match_all('/(\d+)\s*(hari|minggu|bulan|tahun)/', $deadlineInput, $matches, PREG_SET_ORDER);

                // Tambahkan interval satuan waktu sesuai input user
                foreach ($matches as $match) {
                    $jumlah = (int) $match[1]; // Ambil angka
                    $satuan = $intervalMap[$match[2]]; // Ambil satuan waktu

                    // Tambahkan waktu sesuai interval ke waktu saat ini
                    $now = strtotime("+$jumlah $satuan", $now);
                }

                // Set jam deadline ke 23:59:00 secara manual sebelum menyimpan ke database
                $deadline = date('Y-m-d 23:59:00', $now);
            }

            // Tambahkan deadline ke data yang akan diupdate
            $data['deadline'] = $deadline;
        }

        if ($this->model->find($id)) {
            if ($this->model->update($id, $data)) {
                $updatedTask = $this->model->find($id);

                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($updatedTask['user_id']);

                $response = [
                    'status'  => 'Sukses',
                    'message' => 'Task berhasil diupdate!',
                    'data'    => [
                        'user_id' => $updatedTask['user_id'],
                        'task_id' => $updatedTask['id'],
                        'fullname' => $user['first_name'] . ' ' . $user['last_name'],
                        'title'       => $updatedTask['title'],
                        'description' => $updatedTask['description'],
                        'task_status' => $updatedTask['status'],
                        'deadline'    => $updatedTask['deadline'], // Tampilkan deadline yang diupdate
                        'created_at'  => $updatedTask['created_at'],
                        'updated_at'  => $updatedTask['updated_at'],
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
}
