<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\LoveStoryModel;

class LoveStories extends ResourceController
{
    protected $modelName = 'App\Models\LoveStoryModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        helper('image');
        if (isset($data['thumbnail'])) {
            $data['thumbnail'] = save_base64_image($data['thumbnail'], 'stories');
        }
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $key => $img) {
                $data['gallery'][$key] = save_base64_image($img, 'stories/gallery');
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
        $oldItem = $this->model->find($id);
        if (isset($data['thumbnail'])) {
            $data['thumbnail'] = save_base64_image($data['thumbnail'], 'stories', $oldItem['thumbnail'] ?? null);
        }
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $key => $img) {
                $data['gallery'][$key] = save_base64_image($img, 'stories/gallery');
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
            helper('image');
            if ($oldItem && isset($oldItem['thumbnail'])) {
                delete_image($oldItem['thumbnail']);
            }
            if ($oldItem && isset($oldItem['gallery'])) {
                $gallery = $oldItem['gallery'];
                if (is_array($gallery)) {
                    foreach ($gallery as $img) {
                        delete_image($img);
                    }
                }
            }
            return $this->respondDeleted(['id' => $id]);
        }
        return $this->fail($this->model->errors());
    }
}
