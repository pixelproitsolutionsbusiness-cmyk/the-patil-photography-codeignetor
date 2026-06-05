<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\QuotationModel;
use App\Models\QuotationItemModel;

class Quotations extends ResourceController
{
    protected $modelName = 'App\Models\QuotationModel';
    protected $format    = 'json';

    public function index()
    {
        $quotations = $this->model->findAll();
        $itemModel = new QuotationItemModel();
        
        foreach ($quotations as &$q) {
            $id = $q['id'] ?? $q['_id'] ?? null;
            if ($id) {
                $items = $itemModel->where('quotation_id', $id)->findAll();
                $q['services'] = $items;
                $q['items'] = $items;
            } else {
                $q['services'] = [];
                $q['items'] = [];
            }
        }
        
        return $this->respond($quotations);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $services = $data['services'] ?? $data['items'] ?? [];
        
        // Remove fields not in allowedFields
        unset($data['services'], $data['items'], $data['_id'], $data['id']);

        // Auto-populate required database fields to avoid strict mode SQL errors
        if (empty($data['quotationNumber'])) {
            $data['quotationNumber'] = 'QT-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999));
        }
        if (empty($data['quotationDate'])) {
            $data['quotationDate'] = date('Y-m-d');
        }
        if (empty($data['eventDate'])) {
            $data['eventDate'] = date('Y-m-d');
        }
        if (empty($data['validityDate'])) {
            $data['validityDate'] = date('Y-m-d', strtotime('+30 days'));
        }
        if (empty($data['eventType'])) {
            $data['eventType'] = 'Wedding';
        }

        if ($id = $this->model->insert($data)) {
            $itemModel = new QuotationItemModel();
            foreach ($services as $item) {
                $item['quotation_id'] = $id;
                unset($item['id'], $item['_id']);
                $itemModel->insert($item);
            }

            // --- AUTO-CREATE CLIENT ---
            try {
                $clientModel = new \App\Models\ClientModel();
                $email = $data['email'] ?? '';
                $phone = $data['whatsapp_no'] ?? '';
                $name = $data['clientName'] ?? 'Unknown';

                if (!empty($email) || !empty($phone)) {
                    $existingClient = $clientModel->groupStart()
                                                 ->where('email', $email)
                                                 ->orWhere('phone', $phone)
                                                 ->groupEnd()
                                                 ->first();
                    if (!$existingClient) {
                        $clientModel->insert([
                            'name'     => $name,
                            'email'    => $email,
                            'phone'    => $phone,
                            'category' => 'New Inquiry',
                            'status'   => 'Active'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                log_message('error', '[Quotations::autoCreateClient] ' . $e->getMessage());
            }
            // ---------------------------
            
            $created = $this->model->find($id);
            if ($created) {
                $items = $itemModel->where('quotation_id', $id)->findAll();
                $created['services'] = $items;
                $created['items'] = $items;
                
                // --- SEND EMAIL ---
                $this->sendQuotationEmail($created);
            }
            
            return $this->respondCreated($created);
        }
        return $this->fail($this->model->errors());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            $itemModel = new QuotationItemModel();
            $items = $itemModel->where('quotation_id', $id)->findAll();
            $data['services'] = $items;
            $data['items'] = $items;
            return $this->respond($data);
        }
        return $this->failNotFound('Not Found');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $services = $data['services'] ?? $data['items'] ?? null;
        
        // Remove fields not in allowedFields
        unset($data['services'], $data['items'], $data['_id'], $data['id']);

        if ($this->model->update($id, $data)) {
            if ($services !== null) {
                $itemModel = new QuotationItemModel();
                $itemModel->where('quotation_id', $id)->delete();
                foreach ($services as $item) {
                    $item['quotation_id'] = $id;
                    unset($item['id'], $item['_id']);
                    $itemModel->insert($item);
                }
            }
            $updated = $this->model->find($id);
            if ($updated) {
                $itemModel = new QuotationItemModel();
                $items = $itemModel->where('quotation_id', $id)->findAll();
                $updated['services'] = $items;
                $updated['items'] = $items;
            }
            return $this->respond($updated);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            // Also delete items (though DB has ON DELETE CASCADE, it's safer to check)
            $itemModel = new QuotationItemModel();
            $itemModel->where('quotation_id', $id)->delete();
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors());
    }

    private function sendQuotationEmail($data)
    {
        try {
            $email = \Config\Services::email();
            $settingsModel = new \App\Models\SystemSettingsModel();
            $settings = $settingsModel->find(1);
            
            $fromEmail = !empty($settings['contactEmail']) ? $settings['contactEmail'] : 'noreply@thepatilphotography.com';
            $fromName = !empty($settings['businessName']) ? $settings['businessName'] : 'The Patil Photography';
            
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($data['email'] ?? 'pixelproitsolutionsbusiness@gmail.com');
            $email->setSubject('Your Quotation - ' . ($data['quotationNumber'] ?? 'The Patil Photography'));
            
            $message = "Hello " . ($data['clientName'] ?? 'Customer') . ",\n\n";
            $message .= "Your quotation " . ($data['quotationNumber'] ?? '') . " for " . ($data['eventType'] ?? 'the event') . " has been created.\n";
            $message .= "Quotation Date: " . ($data['quotationDate'] ?? 'N/A') . "\n";
            $message .= "Total Amount: " . ($data['grandTotal'] ?? '0') . "\n";
            $message .= "\nWe hope to work with you soon!";

            $email->setMessage($message);
            $email->send();
        } catch (\Exception $e) {
            log_message('error', '[Quotations::sendQuotationEmail] ' . $e->getMessage());
        }
    }
}
