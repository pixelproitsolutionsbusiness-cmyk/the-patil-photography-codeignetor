<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\SliderModel;
use App\Models\GalleryModel;
use App\Models\LoveStoryModel;
use App\Models\SystemSettingsModel;
use App\Models\PopupModel;
use App\Models\TeamMemberModel;
use App\Models\TestimonialModel;

class MigrateImages extends Controller
{
    /**
     * One-time migration script to convert existing Base64 images in database to files.
     * Access via /api/migrate-images (requires route registration)
     */
    public function index()
    {
        helper('image');
        $results = [];

        // 1. Sliders
        $sliderModel = new SliderModel();
        $sliders = $sliderModel->findAll();
        foreach ($sliders as $item) {
            if (isset($item['image'])) {
                $newPath = save_base64_image($item['image'], 'sliders');
                if ($newPath !== $item['image']) {
                    $sliderModel->update($item['id'], ['image' => $newPath]);
                    $results[] = "Slider ID {$item['id']} converted to file.";
                }
            }
        }

        // 2. Gallery
        $galleryModel = new GalleryModel();
        $galleryItems = $galleryModel->findAll();
        foreach ($galleryItems as $item) {
            if (isset($item['image'])) {
                $newPath = save_base64_image($item['image'], 'gallery');
                if ($newPath !== $item['image']) {
                    $galleryModel->update($item['id'], ['image' => $newPath]);
                    $results[] = "Gallery ID {$item['id']} converted to file.";
                }
            }
        }

        // 3. Love Stories
        $storyModel = new LoveStoryModel();
        foreach ($storyModel->findAll() as $item) {
            if (isset($item['thumbnail'])) {
                $newPath = save_base64_image($item['thumbnail'], 'stories');
                if ($newPath !== $item['thumbnail']) {
                    $storyModel->update($item['id'], ['thumbnail' => $newPath]);
                    $results[] = "Love Story ID {$item['id']} converted to file.";
                }
            }
        }

        // 4. System Settings
        $settingsModel = new SystemSettingsModel();
        $settings = $settingsModel->find(1);
        if ($settings) {
            $update = [];
            foreach (['primaryLogo', 'secondaryLogo', 'backgroundImage'] as $field) {
                if (isset($settings[$field])) {
                    $newPath = save_base64_image($settings[$field], 'settings');
                    if ($newPath !== $settings[$field]) {
                        $update[$field] = $newPath;
                    }
                }
            }
            if (!empty($update)) {
                $settingsModel->update(1, $update);
                $results[] = "System settings logos/background converted to files.";
            }
        }

        // 5. Team Members
        $teamModel = new TeamMemberModel();
        foreach ($teamModel->findAll() as $item) {
            if (isset($item['image'])) {
                $newPath = save_base64_image($item['image'], 'team');
                if ($newPath !== $item['image']) {
                    $teamModel->update($item['id'], ['image' => $newPath]);
                    $results[] = "Team Member ID {$item['id']} converted to file.";
                }
            }
        }

        // 6. Popups
        $popupModel = new PopupModel();
        foreach ($popupModel->findAll() as $item) {
            if (isset($item['image'])) {
                $newPath = save_base64_image($item['image'], 'popups');
                if ($newPath !== $item['image']) {
                    $popupModel->update($item['id'], ['image' => $newPath]);
                    $results[] = "Popup ID {$item['id']} converted to file.";
                }
            }
        }
        
        // 7. Testimonials
        $testiModel = new TestimonialModel();
        foreach ($testiModel->findAll() as $item) {
            if (isset($item['thumbnail'])) {
                $newPath = save_base64_image($item['thumbnail'], 'testimonials');
                if ($newPath !== $item['thumbnail']) {
                    $testiModel->update($item['id'], ['thumbnail' => $newPath]);
                    $results[] = "Testimonial ID {$item['id']} converted to file.";
                }
            }
        }

        if (empty($results)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'No Base64 images found to migrate.']);
        }

        return $this->response->setJSON(['status' => 'success', 'migrated' => $results]);
    }
}
