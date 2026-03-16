<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingsModel extends Model
{
    protected $table            = 'system_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id', 'businessName', 'primaryLogo', 'secondaryLogo', 'backgroundImage',
        'contactEmail', 'contactPhone', 'primaryMobileNumber', 'secondaryMobileNumber',
        'address', 'gstNumber', 'hideServices', 'websiteUrl'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSettings()
    {
        $settings = $this->find(1);
        if (!$settings) {
            $data = [
                'id' => 1,
                'businessName' => "The Patil Photography & Film's"
            ];
            $this->insert($data);
            return $this->find(1);
        }
        return $settings;
    }
}
