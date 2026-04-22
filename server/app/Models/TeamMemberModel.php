<?php

namespace App\Models;

use CodeIgniter\Model;

class TeamMemberModel extends Model
{
    protected $table            = 'team_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'role', 'image', 'status', 'order'];

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
}