<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table            = 'gallery';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'image', 'category', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $beforeInsert   = ['encodeImages'];
    protected $beforeUpdate   = ['encodeImages'];
    protected $afterFind      = ['formatId', 'decodeImages'];

    protected function formatId(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }

        if (isset($data['data']['id'])) {
            // Single result
            $data['data']['_id'] = $data['data']['id'];
        } else {
            // Multiple results
            foreach ($data['data'] as &$row) {
                if (is_array($row) && isset($row['id'])) {
                    $row['_id'] = $row['id'];
                } elseif (is_object($row) && isset($row->id)) {
                    $row->_id = $row->id;
                }
            }
        }
        return $data;
    }
        } else {
            foreach ($data['data'] as &$row) {
                if (isset($row['id'])) {
                    $row['_id'] = $row['id'];
                }
            }
        }
        return $data;
    }

    protected function decodeImages(array $data)
    {
        if (isset($data['data'])) {
            if (isset($data['singleton']) && $data['singleton']) {
                $image = $data['data']['image'] ?? null;
                if (is_string($image) && strpos($image, '~%~') !== false) {
                    $data['data']['image'] = explode('~%~', $image);
                }
            } else {
                foreach ($data['data'] as &$row) {
                    $image = $row['image'] ?? null;
                    if (is_string($image) && strpos($image, '~%~') !== false) {
                        $row['image'] = explode('~%~', $image);
                    }
                }
            }
        }
        return $data;
    }

    protected function encodeImages(array $data)
    {
        if (isset($data['data']['image']) && is_array($data['data']['image'])) {
            $data['data']['image'] = implode('~%~', $data['data']['image']);
        }
        return $data;
    }
}