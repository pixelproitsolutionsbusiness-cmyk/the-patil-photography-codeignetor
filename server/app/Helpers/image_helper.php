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
            $extension = strtolower($type[1]); // e.g., png, jpeg, webp
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

            // Create unique filename with correct extension
            $fileName = $folder . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
            $fullPath = $targetDir . '/' . $fileName;

            $success = false;
            
            // For PNG and WEBP, we want to preserve transparency
            if ($extension === 'png' || $extension === 'webp') {
                imagealphablending($img, false);
                imagesavealpha($img, true);
                
                if ($extension === 'png') {
                    $success = imagepng($img, $fullPath, 9); // Compression 0-9
                } else {
                    $success = imagewebp($img, $fullPath, 85);
                }
            } else {
                // For other types (like JPEG), or if forced to JPEG
                // If it was originally a PNG but we save as JPEG, we should fill with white or keep as is?
                // The previous code always converted to JPEG with white background.
                // Let's stick to original format if possible.
                
                if ($extension === 'jpeg' || $extension === 'jpg') {
                    $success = imagejpeg($img, $fullPath, 85);
                } else {
                    // Fallback to PNG if unknown but valid image
                    $success = imagepng($img, $fullPath);
                }
            }

            if ($success) {
                imagedestroy($img);

                // Delete old file if provided and different
                if ($oldFile && !empty($oldFile) && is_string($oldFile)) {
                    $relativePath = 'uploads/' . $folder . '/' . $fileName;
                    if ($oldFile !== $relativePath) {
                        $oldFullPath = FCPATH . ltrim($oldFile, '/');
                        if (is_file($oldFullPath)) {
                            @unlink($oldFullPath);
                        }
                    }
                }
                return 'uploads/' . $folder . '/' . $fileName;
            }
            
            imagedestroy($img);
        }
        return $base64String;
    }
}

if (!function_exists('delete_image')) {
    /**
     * Deletes a file relative to public/
     * 
     * @param string|null $path
     * @return bool
     */
    function delete_image($path) {
        if (!$path || empty($path) || !is_string($path)) {
            return false;
        }
        
        $fullPath = FCPATH . $path;
        if (is_file($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
