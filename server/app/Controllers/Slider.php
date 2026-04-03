<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SliderModel;

class Slider extends ResourceController
{
    protected $modelName = 'App\Models\SliderModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        helper('image');
        if (isset($data['image'])) {
            $data['image'] = save_base64_image($data['image'], 'sliders');
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
        if (isset($data['image'])) {
            $oldItem = $this->model->find($id);
            $data['image'] = save_base64_image($data['image'], 'sliders', $oldItem['image'] ?? null);
        }
        if ($this->model->update($id, $data)) {
            return $this->respond($data);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        $oldItem = $this->model->find($id);
        if ($this->model->delete($id)) {
            if ($oldItem && isset($oldItem['image'])) {
                helper('image');
                delete_image($oldItem['image']);
            }
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors());
    }
}
