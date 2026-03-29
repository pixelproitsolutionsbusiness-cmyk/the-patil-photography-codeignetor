<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'customerName',
        'customerPhone',
        'email',
        'event_name',
        'photographyType',
        'location',
        'date',
        'event_end_date',
        'start_time',
        'end_time',
        'services',
        'albumPages',
        'amount',
        'paidAmount',
        'remaining_amount',
        'deliverables',
        'delivery_date',
        'status',
        'notes',
        'serviceConfig',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $afterFind      = ['formatData'];

    protected function formatData(array $data)
    {
        if (isset($data['data'])) {
            if (isset($data['data']['id'])) {
                $data['data']['_id'] = $data['data']['id'];
            }
            if (isset($data['data']['services']) && is_string($data['data']['services'])) {
                $data['data']['services'] = json_decode($data['data']['services'], true);
            }
            if (isset($data['data']['serviceConfig']) && is_string($data['data']['serviceConfig'])) {
                $data['data']['serviceConfig'] = json_decode($data['data']['serviceConfig'], true);
            }
        } else {
            foreach ($data as &$row) {
                if (isset($row['id'])) {
                    $row['_id'] = $row['id'];
                }
                if (isset($row['services']) && is_string($row['services'])) {
                    $row['services'] = json_decode($row['services'], true);
                }
                if (isset($row['serviceConfig']) && is_string($row['serviceConfig'])) {
                    $row['serviceConfig'] = json_decode($row['serviceConfig'], true);
                }
            }
        }
        return $data;
    }
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