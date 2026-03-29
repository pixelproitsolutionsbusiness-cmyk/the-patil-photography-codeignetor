<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrderModel;

class Orders extends ResourceController
{
    protected $modelName = 'App\Models\OrderModel';
    protected $format    = 'json';

    public function index()
    {
        try {
            return $this->respond($this->model->findAll());
        } catch (\Exception $e) {
            log_message('error', '[Orders::index] ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    public function show($id = null)
    {
        try {
            $data = $this->model->find($id);
            if (!$data) return $this->failNotFound('Order not found');
            return $this->respond($data);
        } catch (\Exception $e) {
            log_message('error', '[Orders::show] ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    public function create()
    {
        try {
            $json = $this->request->getJSON(true);
            if (!$json) {
                // Fallback to manual decoding for malformed requests
                $rawBody = $this->request->getBody();
                $json = json_decode($rawBody, true);
            }

            if (!$json) return $this->fail('Invalid JSON body');

            // Convert arrays to JSON strings
            if (isset($json['services']) && is_array($json['services'])) {
                $json['services'] = json_encode($json['services']);
            }
            if (isset($json['serviceConfig']) && is_array($json['serviceConfig'])) {
                $json['serviceConfig'] = json_encode($json['serviceConfig']);
            }

            $id = $this->model->insert($json);
            if (!$id) return $this->fail($this->model->errors());

            $created = $this->model->find($id);
            return $this->respondCreated($created);
        } catch (\Exception $e) {
            log_message('error', '[Orders::create] ' . $e->getMessage());
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

            if (!$json) return $this->fail('Invalid JSON body');

            // Convert arrays to JSON strings
            if (isset($json['services']) && is_array($json['services'])) {
                $json['services'] = json_encode($json['services']);
            }
            if (isset($json['serviceConfig']) && is_array($json['serviceConfig'])) {
                $json['serviceConfig'] = json_encode($json['serviceConfig']);
            }

            if (!$this->model->update($id, $json)) {
                return $this->fail($this->model->errors());
            }

            return $this->respond($this->model->find($id));
        } catch (\Exception $e) {
            log_message('error', '[Orders::update] ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$this->model->find($id)) return $this->failNotFound('Order not found');
            if ($this->model->delete($id)) {
                return $this->respondDeleted(['id' => $id, 'message' => 'Order deleted successfuly']);
            }
            return $this->fail('Could not delete order');
        } catch (\Exception $e) {
            log_message('error', '[Orders::delete] ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }
}
