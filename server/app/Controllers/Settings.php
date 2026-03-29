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
        try {
            $model = new SystemSettingsModel();
            $socialModel = new SocialLinksModel();
            
            $settings = $model->getSettings();
            if (!$settings) {
                return $this->respond(['error' => 'Settings not found'], 404);
            }
            
            $settings['socialLinks'] = $socialModel->getLinksBySettings($settings['id'] ?? 1);
            
            return $this->respond($settings);
        } catch (\Exception $e) {
            log_message('error', '[Settings::index] ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function update($id = null)
    {
        $model = new SystemSettingsModel();
        $socialModel = new SocialLinksModel();
        
        $data = $this->request->getJSON(true);
        helper('image');
        $oldSettings = $model->find(1);
        
        $settingsData = [
            'businessName' => $data['businessName'] ?? null,
            'primaryLogo' => isset($data['primaryLogo']) ? save_base64_image($data['primaryLogo'], 'settings', $oldSettings['primaryLogo'] ?? null) : null,
            'secondaryLogo' => isset($data['secondaryLogo']) ? save_base64_image($data['secondaryLogo'], 'settings', $oldSettings['secondaryLogo'] ?? null) : null,
            'backgroundImage' => isset($data['backgroundImage']) ? save_base64_image($data['backgroundImage'], 'settings', $oldSettings['backgroundImage'] ?? null) : null,
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
