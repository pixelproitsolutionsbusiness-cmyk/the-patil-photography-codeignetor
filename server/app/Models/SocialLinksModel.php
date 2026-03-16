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
}
