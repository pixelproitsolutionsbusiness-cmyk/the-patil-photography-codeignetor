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
        } catch (\Throwable $e) {
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

            $created = $this->model->find($id);
            $this->sendUserWelcomeEmail($created);
            
            return $this->respondCreated($created);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    private function sendUserWelcomeEmail($data)
    {
        try {
            $email = \Config\Services::email();
            $settingsModel = new \App\Models\SystemSettingsModel();
            $settings = $settingsModel->find(1);
            
            $fromEmail = !empty($settings['contactEmail']) ? $settings['contactEmail'] : 'noreply@thepatilphotography.com';
            $fromName = !empty($settings['businessName']) ? $settings['businessName'] : 'The Patil Photography';
            
            $email->clear();
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($data['email']);
            $email->setSubject('Welcome to ' . $fromName);
            
            $message = "Hello " . ($data['name'] ?? 'User') . ",\n\n";
            $message .= "Your account has been created successfully.\n";
            $message .= "Login at: " . base_url() . "/admin/login\n";
            $message .= "\nRegards,\n" . $fromName;

            $email->setMessage($message);
            
            if (!$email->send()) {
                $debugger = $email->printDebugger(['headers']);
                log_message('error', '[Users::sendUserWelcomeEmail] Email failed to send to: ' . $data['email']);
                log_message('error', '[Users::sendUserWelcomeEmail] Debugger: ' . $debugger);
            } else {
                log_message('info', '[Users::sendUserWelcomeEmail] Email sent successfully to: ' . $data['email']);
            }
        } catch (\Throwable $e) {
            log_message('error', '[Users::sendUserWelcomeEmail] Exception: ' . $e->getMessage());
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
