<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;

class Invoices extends ResourceController
{
    protected $modelName = 'App\Models\InvoiceModel';
    protected $format    = 'json';

    public function index()
    {
        $invoices = $this->model->findAll();
        $itemModel = new InvoiceItemModel();
        
        foreach ($invoices as &$inv) {
            $id = $inv['id'] ?? $inv['_id'] ?? null;
            if ($id) {
                $items = $itemModel->where('invoice_id', $id)->findAll();
                $inv['services'] = $items;
                $inv['items'] = $items;
            } else {
                $inv['services'] = [];
                $inv['items'] = [];
            }
        }
        
        return $this->respond($invoices);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $services = $data['services'] ?? $data['items'] ?? [];
        
        // Remove fields not in allowedFields
        unset($data['services'], $data['items'], $data['_id'], $data['id']);

        if ($id = $this->model->insert($data)) {
            $itemModel = new InvoiceItemModel();
            foreach ($services as $item) {
                $item['invoice_id'] = $id;
                unset($item['id'], $item['_id']);
                $itemModel->insert($item);
            }

            // --- AUTO-CREATE CLIENT ---
            try {
                if (!empty($data['clientName'])) {
                    $clientModel = new \App\Models\ClientModel();
                    $name = $data['clientName'];
                    $existingClient = $clientModel->where('name', $name)->first();
                    
                    if (!$existingClient) {
                        $clientModel->insert([
                            'name'     => $name,
                            'category' => 'New Client',
                            'status'   => 'Active'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                log_message('error', '[Invoices::autoCreateClient] ' . $e->getMessage());
            }
            // ---------------------------
            
            $created = $this->model->find($id);
            if ($created) {
                $items = $itemModel->where('invoice_id', $id)->findAll();
                $created['services'] = $items;
                $created['items'] = $items;
            }
            
            return $this->respondCreated($created);
        }
        return $this->fail($this->model->errors());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if ($data) {
            $itemModel = new InvoiceItemModel();
            $items = $itemModel->where('invoice_id', $id)->findAll();
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
                $itemModel = new InvoiceItemModel();
                $itemModel->where('invoice_id', $id)->delete();
                foreach ($services as $item) {
                    $item['invoice_id'] = $id;
                    unset($item['id'], $item['_id']);
                    $itemModel->insert($item);
                }
            }
            $updated = $this->model->find($id);
            if ($updated) {
                $itemModel = new InvoiceItemModel();
                $items = $itemModel->where('invoice_id', $id)->findAll();
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
            $itemModel = new InvoiceItemModel();
            $itemModel->where('invoice_id', $id)->delete();
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors());
    }
}
