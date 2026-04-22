<?php

namespace App\Models;

use CodeIgniter\Model;

class EnquiryModel extends Model
{
    protected $table            = 'enquiries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'groomName', 'brideName', 'phoneNumber', 'eventStartDate', 
        'eventEndDate', 'events', 'budget', 'location', 'services', 
        'message', 'status'
    ];

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

        $formatRow = function (&$row) {
            if (is_array($row)) {
                if (isset($row['id'])) $row['_id'] = $row['id'];
                if (isset($row['created_at'])) $row['createdAt'] = $row['created_at'];
                if (isset($row['updated_at'])) $row['updatedAt'] = $row['updated_at'];
                if (isset($row['services']) && is_string($row['services'])) {
                    $row['services'] = json_decode($row['services'], true) ?: [];
                }
                if (isset($row['events']) && is_string($row['events'])) {
                    $row['events'] = json_decode($row['events'], true) ?: [];
                }
            } elseif (is_object($row)) {
                if (isset($row->id)) $row->_id = $row->id;
                if (isset($row->created_at)) $row->createdAt = $row->created_at;
                if (isset($row->updated_at)) $row->updatedAt = $row->updated_at;
                if (isset($row->services) && is_string($row->services)) {
                    $row->services = json_decode($row->services, true) ?: [];
                }
                if (isset($row->events) && is_string($row->events)) {
                    $row->events = json_decode($row->events, true) ?: [];
                }
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