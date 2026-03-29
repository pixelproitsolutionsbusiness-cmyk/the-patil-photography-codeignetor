<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Auth extends ResourceController
{
    protected $format = 'json';

    /**
     * POST /api/auth/login
     */
    public function login()
    {
        try {
            $body = $this->request->getBody();
            if (empty($body)) {
                return $this->fail('Body is empty');
            }

            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->fail('Invalid JSON: ' . json_last_error_msg());
            }
            
            if (empty($data['email']) || empty($data['password'])) {
                return $this->fail('Email and password are required');
            }

            $email = $data['email'];
            $password = $data['password'];


            $model = new UserModel();
            $user = $model->where('email', $email)->first();

            // Check if user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Remove sensitive fields
                unset($user['password']);
                
                // For now, return a simple token. In production, use JWT.
                $token = bin2hex(random_bytes(32));
                
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user
                ]);
            }

            // Check if we should auto-create the admin user if it's the specific first login attempt
            if ($email === 'admin@lumina.studio' && $password === 'admin' && !$user) {
                $adminData = [
                    'name' => 'Admin',
                    'email' => 'admin@lumina.studio',
                    'password' => 'admin', // Will be hashed by UserModel beforeInsert
                    'role' => 'admin',
                    'status' => 'Active'
                ];
                
                if ($model->insert($adminData)) {
                    $insertedId = $model->getInsertID();
                    $user = $model->find($insertedId);
                    unset($user['password']);
                    
                    return $this->respond([
                        'status' => 'success',
                        'message' => 'First-time admin initialized',
                        'token' => bin2hex(random_bytes(32)),
                        'user' => $user
                    ]);
                }
            }

            return $this->failUnauthorized('Invalid email or password');
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }


    /**
     * POST /api/auth/logout
     */
    public function logout()
    {
        return $this->respond(['message' => 'Logged out successfully']);
    }
}
