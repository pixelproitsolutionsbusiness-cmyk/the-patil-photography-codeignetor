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
        'title', 'location', 'description', 'thumbnail', 'gallery', 'status', 'order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    protected $beforeInsert   = ['encodeGallery'];
    protected $beforeUpdate   = ['encodeGallery'];
    protected $afterFind      = ['formatData', 'decodeGallery'];

    protected function formatData(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }

        $formatRow = function (&$row) {
            if (is_array($row)) {
                if (isset($row['id'])) $row['_id'] = $row['id'];
                if (isset($row['created_at'])) $row['createdAt'] = $row['created_at'];
                if (isset($row['updated_at'])) $row['updatedAt'] = $row['updated_at'];
                if (isset($row['order'])) $row['display_order'] = $row['order'];
            } elseif (is_object($row)) {
                if (isset($row->id)) $row->_id = $row->id;
                if (isset($row->created_at)) $row->createdAt = $row->created_at;
                if (isset($row->updated_at)) $row->updatedAt = $row->updated_at;
                if (isset($row->order)) $row->display_order = $row->order;
            }
        };

        if (isset($data['data']['id']) || (isset($data['singular']) && $data['singular'])) {
            $formatRow($data['data']);
        } else {
            foreach ($data['data'] as &$row) {
                $formatRow($row);
            }
        }
        return $data;
    }

    protected function decodeGallery(array $data)
    {
        if (isset($data['data'])) {
            $isSingular = isset($data['data']['id']) || (isset($data['singular']) && $data['singular']);
            
            if ($isSingular) {
                $gallery = $data['data']['gallery'] ?? null;
                $data['data']['gallery'] = $this->_parseGallery($gallery);
            } else {
                foreach ($data['data'] as &$row) {
                    $gallery = $row['gallery'] ?? null;
                    $row['gallery'] = $this->_parseGallery($gallery);
                }
            }
        }
        return $data;
    }
    
    private function _parseGallery($gallery) {
        if (empty($gallery)) return [];
        if (is_array($gallery)) return $gallery;
        if (strpos($gallery, '~%~') !== false) {
            return explode('~%~', $gallery);
        }
        $decoded = json_decode($gallery, true);
        return is_array($decoded) ? $decoded : [$gallery];
    }

    protected function encodeGallery(array $data)
    {
        if (isset($data['data']['gallery']) && is_array($data['data']['gallery'])) {
            $data['data']['gallery'] = implode('~%~', $data['data']['gallery']);
        }
        return $data;
    }
}