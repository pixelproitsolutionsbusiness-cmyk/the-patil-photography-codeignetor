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

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterFind      = ['formatData'];

    protected function formatData(array $data)
    {
        if (!isset($data['data'])) return $data;

        $formatRow = function (&$row) {
            if (is_array($row)) {
                if (isset($row['id'])) {
                    $row['_id'] = $row['id'];
                }
                if (isset($row['services']) && is_string($row['services'])) {
                    $row['services'] = json_decode($row['services'], true) ?: [];
                }
                if (isset($row['serviceConfig']) && is_string($row['serviceConfig'])) {
                    $row['serviceConfig'] = json_decode($row['serviceConfig'], true) ?: [];
                }
            }
        };

        if (isset($data['data']['id']) || (isset($data['singular']) && $data['singular'])) {
            // Single Record
            $formatRow($data['data']);
        } else {
            // Collection
            foreach ($data['data'] as &$row) {
                $formatRow($row);
            }
        }
        return $data;
    }
}