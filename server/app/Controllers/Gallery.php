<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\GalleryModel;

class Gallery extends ResourceController
{
    protected $modelName = 'App\Models\GalleryModel';
    protected $format    = 'json';

    public function index()
    {
        try {
            return $this->respond($this->model->findAll());
        } catch (\Exception $e) {
            return $this->respond(['error' => $e->getMessage()], 500);
        }
    }


    public function create()
    {
        $data = $this->request->getJSON(true);
        helper('image');
        if (isset($data['image'])) {
            if (is_array($data['image'])) {
                foreach ($data['image'] as $key => $img) {
                    $data['image'][$key] = save_base64_image($img, 'gallery');
                }
            } else {
                $data['image'] = save_base64_image($data['image'], 'gallery');
            }
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
            if (is_array($data['image'])) {
                foreach ($data['image'] as $key => $img) {
                    $data['image'][$key] = save_base64_image($img, 'gallery');
                }
            } else {
                $oldItem = $this->model->find($id);
                $oldImage = is_array($oldItem['image'] ?? null) ? null : ($oldItem['image'] ?? null);
                $data['image'] = save_base64_image($data['image'], 'gallery', $oldImage);
            }
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
                if (is_array($oldItem['image'])) {
                    foreach ($oldItem['image'] as $img) {
                        delete_image($img);
                    }
                } else {
                    delete_image($oldItem['image']);
                }
            }
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors());
    }
}
