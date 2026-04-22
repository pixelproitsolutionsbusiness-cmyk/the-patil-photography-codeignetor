<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $table            = 'contacts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'email', 'subject', 'message', 'status'];

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
            if (isset($data['data']['created_at'])) {
                $data['data']['createdAt'] = $data['data']['created_at'];
            }
            if (isset($data['data']['updated_at'])) {
                $data['data']['updatedAt'] = $data['data']['updated_at'];
            }
        } else {
            // Multiple results
            foreach ($data['data'] as &$row) {
                if (is_array($row)) {
                    if (isset($row['id'])) $row['_id'] = $row['id'];
                    if (isset($row['created_at'])) $row['createdAt'] = $row['created_at'];
                    if (isset($row['updated_at'])) $row['updatedAt'] = $row['updated_at'];
                } elseif (is_object($row)) {
                    if (isset($row->id)) $row->_id = $row->id;
                    if (isset($row->created_at)) $row->createdAt = $row->created_at;
                    if (isset($row->updated_at)) $row->updatedAt = $row->updated_at;
                }
            }
        }
        return $data;
    }
}