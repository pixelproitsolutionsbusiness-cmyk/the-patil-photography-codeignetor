<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SystemSettingsModel;
use App\Models\SocialLinksModel;

class Settings extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new SystemSettingsModel();
        $socialModel = new SocialLinksModel();
        
        $settings = $model->getSettings();
        $settings['socialLinks'] = $socialModel->getLinksBySettings($settings['id']);
        
        return $this->respond($settings);
    }

    public function update($id = null)
    {
        $model = new SystemSettingsModel();
        $socialModel = new SocialLinksModel();
        
        $data = $this->request->getJSON(true);
        
        $settingsData = [
            'businessName' => $data['businessName'] ?? null,
            'primaryLogo' => $data['primaryLogo'] ?? null,
            'secondaryLogo' => $data['secondaryLogo'] ?? null,
            'backgroundImage' => $data['backgroundImage'] ?? null,
            'contactEmail' => $data['contactEmail'] ?? null,
            'contactPhone' => $data['contactPhone'] ?? null,
            'primaryMobileNumber' => $data['primaryMobileNumber'] ?? null,
            'secondaryMobileNumber' => $data['secondaryMobileNumber'] ?? null,
            'address' => $data['address'] ?? null,
            'gstNumber' => $data['gstNumber'] ?? null,
            'hideServices' => $data['hideServices'] ?? null,
            'websiteUrl' => $data['websiteUrl'] ?? null,
        ];

        // Filter out nulls
        $settingsData = array_filter($settingsData, function($v) { return !is_null($v); });

        if (!empty($settingsData)) {
            $model->update(1, $settingsData);
        }

        if (isset($data['socialLinks']) && is_array($data['socialLinks'])) {
            // Simple approach: delete existing and re-insert
            $socialModel->where('settings_id', 1)->delete();
            foreach ($data['socialLinks'] as $link) {
                $socialModel->insert([
                    'settings_id' => 1,
                    'platform' => $link['platform'],
                    'url' => $link['url'],
                    'icon' => $link['icon'] ?? '',
                    'active' => $link['active'] ?? true,
                ]);
            }
        }

        return $this->index();
    }
}
