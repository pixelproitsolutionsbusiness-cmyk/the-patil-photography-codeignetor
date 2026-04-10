<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EnquiryModel;

class Enquiries extends ResourceController
{
    protected $modelName = 'App\Models\EnquiryModel';
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
            $this->sendEnquiryEmail($data);
            return $this->respondCreated($data);
        }
        return $this->fail($this->model->errors());
    }

    private function sendEnquiryEmail($data)
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
        $email->setSubject('New Wedding Enquiry from ' . ($data['groomName'] ?? 'Guest') . ' & ' . ($data['brideName'] ?? 'Guest'));
        
        $message = "You have received a new wedding enquiry.\n\n";
        $message .= "Groom Name: " . ($data['groomName'] ?? 'N/A') . "\n";
        $message .= "Bride Name: " . ($data['brideName'] ?? 'N/A') . "\n";
        $message .= "Phone Number: " . ($data['phoneNumber'] ?? 'N/A') . "\n";
        $message .= "Event Start Date: " . ($data['eventStartDate'] ?? 'N/A') . "\n";
        $message .= "Event End Date: " . ($data['eventEndDate'] ?? 'N/A') . "\n";
        $message .= "Budget: " . ($data['budget'] ?? 'N/A') . "\n";
        $message .= "Location: " . ($data['location'] ?? 'N/A') . "\n";
        
        if (isset($data['events']) && is_array($data['events'])) {
            $message .= "Events: " . (is_string($data['events']) ? $data['events'] : implode(', ', $data['events'])) . "\n";
        }
        
        if (isset($data['services']) && is_array($data['services'])) {
            $message .= "Services: " . (is_string($data['services']) ? $data['services'] : implode(', ', $data['services'])) . "\n";
        }
        
        $message .= "Message/Vision: " . ($data['message'] ?? 'N/A') . "\n";

        $email->setMessage($message);

        try {
            $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Email failed: ' . $e->getMessage());
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
