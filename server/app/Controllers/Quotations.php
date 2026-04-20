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
        return $this->respond($this->model->findAll());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $items = $data['items'] ?? [];
        unset($data['items']);

        if ($id = $this->model->insert($data)) {
            $itemModel = new QuotationItemModel();
            foreach ($items as $item) {
                $item['quotation_id'] = $id;
                $itemModel->insert($item);
            }
            
            $created = $this->model->find($id);
            $created['items'] = $itemModel->where('quotation_id', $id)->findAll();
            
            // --- SEND EMAIL ---
            $this->sendQuotationEmail($created);
            
            return $this->respondCreated($created);
        }
        return $this->fail($this->model->errors());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            $itemModel = new QuotationItemModel();
            $data['items'] = $itemModel->where('quotation_id', $id)->findAll();
            return $this->respond($data);
        }
        return $this->failNotFound('Not Found');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $items = $data['items'] ?? null;
        unset($data['items'], $data['_id'], $data['id']);

        if ($this->model->update($id, $data)) {
            if ($items !== null) {
                $itemModel = new QuotationItemModel();
                $itemModel->where('quotation_id', $id)->delete();
                foreach ($items as $item) {
                    $item['quotation_id'] = $id;
                    unset($item['id'], $item['_id']);
                    $itemModel->insert($item);
                }
            }
            $updated = $this->model->find($id);
            if ($updated) {
                $itemModel = new QuotationItemModel();
                $updated['items'] = $itemModel->where('quotation_id', $id)->findAll();
            }
            return $this->respond($updated);
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
