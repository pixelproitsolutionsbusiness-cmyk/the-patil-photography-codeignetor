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
        log_message('debug', 'LoveStories::create - Data received: ' . json_encode(array_keys($data)));
        helper('image');
        if (isset($data['thumbnail'])) {
            log_message('debug', 'LoveStories::create - Saving thumbnail');
            $data['thumbnail'] = save_base64_image($data['thumbnail'], 'stories');
        }
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            log_message('debug', 'LoveStories::create - Saving gallery images: ' . count($data['gallery']));
            foreach ($data['gallery'] as $key => $img) {
                $data['gallery'][$key] = save_base64_image($img, 'stories/gallery');
            }
        }
        if (isset($data['order'])) {
            $data['display_order'] = $data['order'];
        }
        
        // Remove non-database fields
        unset($data['id'], $data['_id'], $data['order'], $data['createdAt'], $data['updatedAt'], $data['created_at'], $data['updated_at']);

        log_message('debug', 'LoveStories::create - Inserting into model');
        if ($this->model->insert($data)) {
            log_message('debug', 'LoveStories::create - Success');
            return $this->respondCreated($data);
        }
        log_message('error', 'LoveStories::create - Failed: ' . json_encode($this->model->errors()));
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
        log_message('debug', "LoveStories::update - ID: $id, Keys: " . json_encode(array_keys($data)));
        helper('image');
        $oldItem = $this->model->find($id);
        if (isset($data['thumbnail'])) {
            log_message('debug', 'LoveStories::update - Saving thumbnail');
            $data['thumbnail'] = save_base64_image($data['thumbnail'], 'stories', $oldItem['thumbnail'] ?? null);
        }
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            log_message('debug', 'LoveStories::update - Saving gallery images: ' . count($data['gallery']));
            foreach ($data['gallery'] as $key => $img) {
                log_message('debug', 'LoveStories::update - Processing gallery image ' . $key . ' (length: ' . (is_string($img) ? strlen($img) : 'not a string') . ')');
                $data['gallery'][$key] = save_base64_image($img, 'stories/gallery');
            }
        }
        
        if (isset($data['order'])) {
            $data['display_order'] = $data['order'];
        }

        // Remove non-database fields
        unset($data['id'], $data['_id'], $data['order'], $data['createdAt'], $data['updatedAt'], $data['created_at'], $data['updated_at']);

        log_message('debug', 'LoveStories::update - Updating model');
        if ($this->model->update($id, $data)) {
            log_message('debug', 'LoveStories::update - Success');
            return $this->respond($data);
        }
        log_message('error', 'LoveStories::update - Failed: ' . json_encode($this->model->errors()));
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
