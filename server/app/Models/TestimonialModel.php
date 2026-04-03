<?php

namespace App\Models;

use CodeIgniter\Model;

class TestimonialModel extends Model
{
    protected $table            = 'testimonials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'role', 'text', 'rating', 'status', 'order'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowCallbacks = true;
    protected $afterFind      = ['formatId'];

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
}