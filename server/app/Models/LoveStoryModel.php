<?php

namespace App\Models;

use CodeIgniter\Model;

class LoveStoryModel extends Model
{
    protected $table            = 'love_stories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title', 'location', 'description', 'thumbnail', 'gallery', 'status', 'order', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowCallbacks = true;
    protected $afterFind      = ['formatId', 'decodeGallery'];

    protected function formatId(array $data)
    {
        if (!isset($data['data'])) return $data;

        if (isset($data['singleton']) && $data['singleton']) {
            if (isset($data['data']['id'])) {
                $data['data']['_id'] = $data['data']['id'];
            }
            if (isset($data['data']['created_at'])) {
                $data['data']['createdAt'] = $data['data']['created_at'];
            }
            if (isset($data['data']['updated_at'])) {
                $data['data']['updatedAt'] = $data['data']['updated_at'];
            }
        } else {
            foreach ($data['data'] as &$row) {
                if (isset($row['id'])) {
                    $row['_id'] = $row['id'];
                }
                if (isset($row['created_at'])) {
                    $row['createdAt'] = $row['created_at'];
                }
                if (isset($row['updated_at'])) {
                    $row['updatedAt'] = $row['updated_at'];
                }
            }
        }
        return $data;
    }

    protected function decodeGallery(array $data)
    {
        if (isset($data['data'])) {
            if (isset($data['singleton']) && $data['singleton']) {
                $gallery = $data['data']['gallery'] ?? null;
                $data['data']['gallery'] = is_string($gallery) ? (json_decode($gallery, true) ?: []) : (is_array($gallery) ? $gallery : []);
            } else {
                foreach ($data['data'] as &$row) {
                    $gallery = $row['gallery'] ?? null;
                    $row['gallery'] = is_string($gallery) ? (json_decode($gallery, true) ?: []) : (is_array($gallery) ? $gallery : []);
                }
            }
        }
        return $data;
    }
}