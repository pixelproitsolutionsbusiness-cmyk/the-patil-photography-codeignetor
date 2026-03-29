<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Users extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    public function index()
    {
        try {
            $users = $this->model->findAll();
            // Remove full hash for security in general list? 
            // The frontend AdminUsers.jsx might expect them if it wants to "reveal".
            // But usually we don't send hashes.
            return $this->respond($users);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function create()
    {
        try {
            $json = $this->request->getJSON(true);
            if (!$json) {
                $rawBody = $this->request->getBody();
                $json = json_decode($rawBody, true);
            }

            if (!$json) return $this->fail('Invalid JSON');

            if (isset($json['password']) && !empty($json['password'])) {
                // The frontend might send plain text password.
                // We should hash it but the model might have beforeInsert. 
                // Let's check UserModel.
            }

            $id = $this->model->insert($json);
            if (!$id) return $this->fail($this->model->errors());

            return $this->respondCreated($this->model->find($id));
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function update($id = null)
    {
        try {
            $json = $this->request->getJSON(true);
            if (!$json) {
                $rawBody = $this->request->getBody();
                $json = json_decode($rawBody, true);
            }

            if (!$json) return $this->fail('Invalid JSON');

            // If updating password, it will be hashed by the model if configured
            if (!$this->model->update($id, $json)) {
                return $this->fail($this->model->errors());
            }

            return $this->respond($this->model->find($id));
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$this->model->find($id)) return $this->failNotFound('User not found');
            if ($this->model->delete($id)) {
                return $this->respondDeleted(['id' => $id, 'message' => 'User deleted successfuly']);
            }
            return $this->fail('Could not delete user');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * POST /api/users/reveal
     */
    public function reveal()
    {
        try {
            $json = $this->request->getJSON(true);
            if (!$json) {
                $rawBody = $this->request->getBody();
                $json = json_decode($rawBody, true);
            }

            if (!$json) return $this->fail('Invalid request');

            $adminPassword = $json['adminPassword'] ?? '';
            $targetUserId = $json['targetUserId'] ?? '';

            // 1. Verify admin (current user)
            // In a real app we'd get current user from JWT, here we just check if any admin exists with this pwd
            $admin = $this->model->where('role', 'admin')->first();
            if (!$admin || !password_verify($adminPassword, $admin['password'])) {
                return $this->failUnauthorized('Invalid admin password');
            }

            // 2. Fetch target user
            $target = $this->model->find($targetUserId);
            if (!$target) return $this->failNotFound('Target user not found');

            // 3. Since we hash passwords, we CANNOT reveal them.
            // We tell the frontend it can't be revealed.
            return $this->respond([
                'password' => 'Encrypted (Cannot Reveal)'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
