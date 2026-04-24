<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class TestEmail extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        try {
            $email = \Config\Services::email();
            
            $to = $this->request->getGet('to') ?? 'test@example.com';
            
            $email->setFrom('noreply@thepatilphotography.com', 'Test Sender');
            $email->setTo($to);
            $email->setSubject('Email Test');
            $email->setMessage('This is a test email from CodeIgniter 4.');

            if ($email->send()) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Email sent successfully to ' . $to
                ]);
            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to send email',
                    'debug' => $email->printDebugger(['headers', 'subject', 'body'])
                ]);
            }
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
