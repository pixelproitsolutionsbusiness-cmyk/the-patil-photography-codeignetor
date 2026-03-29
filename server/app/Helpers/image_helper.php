<?php

if (!function_exists('save_base64_image')) {
    /**
     * Saves a base64 encoded image to a file and returns the relative path.
     * 
     * @param string $base64String The base64 encoded image string
     * @param string $folder The subfolder within uploads/ (inside public/)
     * @param string|null $oldFile Optional path to an old file to delete (relative to public/)
     * @return string The path to the saved file or the original string if not base64
     */
    function save_base64_image($base64String, $folder = 'general', $oldFile = null) {
        // Only process if it looks like a base64 image data URI
        if ($base64String && is_string($base64String) && preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $decodedData = base64_decode($data);
            if ($decodedData === false) {
                return $base64String;
            }

            // Create image resource from decoded data
            $img = @imagecreatefromstring($decodedData);
            if (!$img) {
                return $base64String; // Not a valid image
            }

            // Define target path (FCPATH is usually public/)
            $targetDir = FCPATH . 'uploads/' . $folder;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Create unique filename ALWAYS as JPG
            $fileName = $folder . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $fullPath = $targetDir . '/' . $fileName;

            // Save as JPEG (Quality: 85)
            // If transparent PNG, background will be white by default usually?
            // To be safer, create white background if needed
            $width = imagesx($img);
            $height = imagesy($img);
            $outputImg = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($outputImg, 255, 255, 255);
            imagefill($outputImg, 0, 0, $white);
            imagecopy($outputImg, $img, 0, 0, 0, 0, $width, $height);

            if (imagejpeg($outputImg, $fullPath, 85)) {
                imagedestroy($img);
                imagedestroy($outputImg);

                // Delete old file if provided and different
                if ($oldFile && !empty($oldFile) && is_string($oldFile)) {
                    $relativePath = 'uploads/' . $folder . '/' . $fileName;
                    if ($oldFile !== $relativePath) {
                        $oldFullPath = FCPATH . $oldFile;
                        if (is_file($oldFullPath)) {
                            unlink($oldFullPath);
                        }
                    }
                }
                return 'uploads/' . $folder . '/' . $fileName;
            }
            
            imagedestroy($img);
            imagedestroy($outputImg);
        }
        return $base64String;
    }
}
