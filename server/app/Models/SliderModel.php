<?php

namespace App\Models;

use CodeIgniter\Model;

class SliderModel extends Model
{
    protected $table            = 'sliders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'subtitle', 'image', 'status', 'order'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowCallbacks = true;
    protected $afterFind      = ['formatId'];

    protected function formatId(array $data)
    {
        if (isset($data['data'])) {
            if (isset($data['data']['id'])) {
                $data['data']['_id'] = $data['data']['id'];
            }
        } else {
            foreach ($data as &$row) {
                if (isset($row['id'])) {
                    $row['_id'] = $row['id'];
                }
            }
        }
        return $data;
    }
}
