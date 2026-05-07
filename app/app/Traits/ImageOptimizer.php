<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ImageOptimizer
{
    /**
     * Optimize image: Resize to max width and convert to WebP.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth
     * @param int $quality
     * @return string|false Path to the stored optimized image
     */
    public function optimizeAndStore(UploadedFile $file, string $directory, int $maxWidth = 1200, int $quality = 80, string $disk = 'public')
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $tempPath = $file->getRealPath();

            // 1. Create image resource based on extension
            switch ($extension) {
                case 'jpeg':
                case 'jpg':
                    $image = imagecreatefromjpeg($tempPath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($tempPath);
                    // Preserve transparency for PNG
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                case 'webp':
                    $image = imagecreatefromwebp($tempPath);
                    break;
                default:
                    // If unsupported by our optimizer, just store normally
                    return $file->store($directory, $disk);
            }

            if (!$image) {
                return $file->store($directory, $disk);
            }

            // 2. Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // 3. Resize if needed
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) ($height * ($maxWidth / $width));
                
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Handle transparency for resized image
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
                
                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resizedImage;
            }

            // 5. Save image to a temporary buffer
            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($image, null, $quality);
                $content = ob_get_clean();
                $filename = Str::random(40) . '.webp';
            } else {
                // Fallback to JPEG if WebP is not supported
                imagejpeg($image, null, $quality);
                $content = ob_get_clean();
                $filename = Str::random(40) . '.jpg';
            }
            
            $finalPath = $directory . '/' . $filename;
            imagedestroy($image);

            // 6. Store to disk
            Storage::disk($disk)->put($finalPath, $content);

            return $finalPath;
        } catch (\Exception $e) {
            \Log::error('Image Optimization Failed: ' . $e->getMessage());
            // Fallback to normal storage if anything fails
            return $file->store($directory, $disk);
        }
    }
}
