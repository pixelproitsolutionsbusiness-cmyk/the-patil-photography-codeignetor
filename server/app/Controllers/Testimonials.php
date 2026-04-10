<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TestimonialModel;

class Testimonials extends ResourceController
{
    protected $modelName = 'App\Models\TestimonialModel';
    protected $format    = 'json';

    public function index()
    {
        $type = $this->request->getGet('type');
        
        if ($type === 'active') {
            return $this->respond($this->model->where('status', 'Active')->findAll());
        }
        
        return $this->respond($this->model->findAll());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        helper('image');
        if (isset($data['thumbnail'])) {
            $data['thumbnail'] = save_base64_image($data['thumbnail'], 'testimonials');
        }
        if ($this->model->insert($data)) {
            return $this->respondCreated($data);
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
        helper('image');
        if (isset($data['thumbnail'])) {
            $oldItem = $this->model->find($id);
            $data['thumbnail'] = save_base64_image($data['thumbnail'], 'testimonials', $oldItem['thumbnail'] ?? null);
        }
        if ($this->model->update($id, $data)) {
            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        $item = $this->model->find($id);
        if ($item && $this->model->delete($id)) {
            helper('image');
            if (!empty($item['thumbnail'])) {
                delete_image($item['thumbnail']);
            }
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors() ?: 'Item not found');
    }
}
