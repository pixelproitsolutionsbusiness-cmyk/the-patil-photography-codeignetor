<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table            = 'services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'basePrice', 'ratePerDay', 'category', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowCallbacks = true;
    protected $afterFind      = ['formatId'];

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
}
