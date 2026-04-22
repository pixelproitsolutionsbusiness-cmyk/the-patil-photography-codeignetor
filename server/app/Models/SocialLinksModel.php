<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialLinksModel extends Model
{
    protected $table            = 'social_links';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['settings_id', 'platform', 'url', 'icon', 'active'];

    public function getLinksBySettings($settingsId = 1)
    {
        return $this->where('settings_id', $settingsId)->findAll();
    }
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