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
        // Handle JSON encoding for arrays if not handled by model
        if (isset($data['services']) && is_array($data['services'])) {
            $data['services'] = json_encode($data['services']);
        }
        if (isset($data['events']) && is_array($data['events'])) {
            $data['events'] = json_encode($data['events']);
        }
        
        if ($id = $this->model->insert($data)) {
            $created = $this->model->find($id);
            
            // --- SEND EMAIL ---
            $this->sendEnquiryEmail($created);
            
            return $this->respondCreated($created);
        }
        return $this->fail($this->model->errors());
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
        unset($data['_id'], $data['id']);
        
        if (isset($data['services']) && is_array($data['services'])) {
            $data['services'] = json_encode($data['services']);
        }
        if (isset($data['events']) && is_array($data['events'])) {
            $data['events'] = json_encode($data['events']);
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond($this->model->find($id));
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

    private function sendEnquiryEmail($data)
    {
        try {
            $email = \Config\Services::email();
            $settingsModel = new \App\Models\SystemSettingsModel();
            $settings = $settingsModel->find(1);
            
            $fromEmail = !empty($settings['contactEmail']) ? $settings['contactEmail'] : 'noreply@thepatilphotography.com';
            $fromName = !empty($settings['businessName']) ? $settings['businessName'] : 'The Patil Photography';
            
            $email->setFrom($fromEmail, $fromName);
            $email->setTo('pixelproitsolutionsbusiness@gmail.com'); // Admin copy
            $email->setSubject('New Booking Enquiry: ' . ($data['groomName'] ?? '') . ' & ' . ($data['brideName'] ?? ''));
            
            $message = "You have received a new booking enquiry.\n\n";
            $message .= "Couples: " . ($data['groomName'] ?? '') . " & " . ($data['brideName'] ?? '') . "\n";
            $message .= "Phone: " . ($data['phoneNumber'] ?? '') . "\n";
            $message .= "Location: " . ($data['location'] ?? '') . "\n";
            $message .= "Event Date: " . ($data['eventStartDate'] ?? '') . "\n";
            $message .= "Message: " . ($data['message'] ?? '') . "\n";

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', '[Enquiries::sendEnquiryEmail] ' . $e->getMessage());
        }
    }
}
