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
    // Aligning with DB schema and providing aliases
    protected $allowedFields    = ['clientName', 'review', 'rating', 'status', 'display_order', 'thumbnail'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowCallbacks = true;
    protected $afterFind      = ['formatData'];

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
                
                // Add aliases for frontend compatibility
                if (isset($row['clientName'])) $row['name'] = $row['clientName'];
                if (isset($row['review'])) $row['text'] = $row['review'];
                if (isset($row['review'])) $row['fullDescription'] = $row['review'];
                if (isset($row['clientName'])) $row['coupleName'] = $row['clientName'];
            } elseif (is_object($row)) {
                if (isset($row->id)) $row->_id = $row->id;
                if (isset($row->created_at)) $row->createdAt = $row->created_at;
                if (isset($row->updated_at)) $row->updatedAt = $row->updated_at;
                
                if (isset($row->clientName)) $row->name = $row->clientName;
                if (isset($row->review)) $row->text = $row->review;
                if (isset($row->review)) $row->fullDescription = $row->review;
                if (isset($row->clientName)) $row->coupleName = $row->clientName;
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
}