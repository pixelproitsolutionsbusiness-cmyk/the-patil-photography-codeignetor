<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PopupModel;

class Popup extends ResourceController
{
    protected $modelName = 'App\Models\PopupModel';
    protected $format    = 'json';

    public function index()
    {
        $popup = $this->model->find(1);
        if (!$popup) {
            $this->model->insert(['id' => 1, 'title' => '', 'content' => '', 'isActive' => false]);
            $popup = $this->model->find(1);
        }
        
        // Map content to description for frontend compatibility
        $popup['description'] = $popup['content'] ?? '';
        
        return $this->respond($popup);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        helper('image');
        if (isset($data['image'])) {
            $data['image'] = save_base64_image($data['image'], 'popups');
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
        $id = $id ?? 1; // Default to singleton ID 1
        $data = $this->request->getJSON(true);
        helper('image');
        
        // Map frontend description back to database content
        if (isset($data['description'])) {
            $data['content'] = $data['description'];
            unset($data['description']);
        }
        
        if (isset($data['image']) && strpos($data['image'], 'data:image') === 0) {
            $oldItem = $this->model->find($id);
            $data['image'] = save_base64_image($data['image'], 'popups', $oldItem['image'] ?? null);
        }
        
        if ($this->model->update($id, $data)) {
            return $this->index();
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
