<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ContactModel;

class Contacts extends ResourceController
{
    protected $modelName = 'App\Models\ContactModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if ($this->model->insert($data)) {
            // Send Email Notification
            $this->sendContactEmail($data);
            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    private function sendContactEmail($data)
    {
        $email = \Config\Services::email();

        $to = 'pixelproitsolutionsbusiness@gmail.com';
        
        $settingsModel = new \App\Models\SystemSettingsModel();
        $settings = $settingsModel->find(1);
        
        $fromEmail = !empty($settings['contactEmail']) ? $settings['contactEmail'] : 'noreply@thepatilphotography.com';
        $fromName = !empty($settings['businessName']) ? $settings['businessName'] : 'The Patil Photography';
        
        $email->setFrom($fromEmail, $fromName);
        $email->setTo($to);
        if ($settings && !empty($settings['contactEmail'])) {
            $email->setBCC($settings['contactEmail']);
        }
        $email->setSubject('New Contact Message: ' . ($data['subject'] ?? 'No Subject'));
        
        $message = "You have received a new message from your website contact form.\n\n";
        $message .= "Name: " . ($data['name'] ?? 'N/A') . "\n";
        $message .= "Email: " . ($data['email'] ?? 'N/A') . "\n";
        $message .= "Subject: " . ($data['subject'] ?? 'N/A') . "\n";
        $message .= "Message: \n" . ($data['message'] ?? 'N/A') . "\n";

        $email->setMessage($message);

        try {
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Contact Email failed: ' . $e->getMessage());
        }
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            return $this->respond($data);
        }
        return $this->failNotFound('Not Found');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if ($this->model->update($id, $data)) {
            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors());
    }
}
