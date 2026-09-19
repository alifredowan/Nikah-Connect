<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PhotoController extends Controller
{
    /**
     * Securely serve photo: unblurred only if authorized, otherwise server-side blurred (FR-2.2).
     */
    public function show(Request $request, Photo $photo): Response
    {
        $viewer = Auth::user();
        $isAuthorized = $photo->profile?->isPhotoVisibleTo($viewer);

        if ($isAuthorized) {
            return $this->serveOriginal($photo);
        }

        return $this->serveBlurred($photo);
    }

    protected function serveOriginal(Photo $photo): Response
    {
        $filePath = $photo->file_path;

        // If external URL (e.g. Unsplash demo image)
        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return redirect()->away($filePath);
        }

        // If local public storage
        if (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
            $mime = mime_content_type($fullPath) ?: 'image/jpeg';

            return response()->file($fullPath, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            ]);
        }

        return $this->serveSvgSilhouette($photo);
    }

    protected function serveBlurred(Photo $photo): Response
    {
        $cacheDir = storage_path('app/blurred-photos');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir.'/blurred_'.$photo->id.'.jpg';

        if (file_exists($cacheFile)) {
            return response()->file($cacheFile, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            ]);
        }

        // Generate blurred version using GD
        $imageData = null;
        $filePath = $photo->file_path;

        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            try {
                $res = Http::timeout(4)->get($filePath);
                if ($res->successful()) {
                    $imageData = $res->body();
                }
            } catch (\Throwable $e) {
                // fall through to SVG
            }
        } elseif (Storage::disk('public')->exists($filePath)) {
            $imageData = Storage::disk('public')->get($filePath);
        }

        if ($imageData && extension_loaded('gd')) {
            try {
                $im = @imagecreatefromstring($imageData);
                if ($im !== false) {
                    // Downscale slightly for stronger blur effect
                    $w = imagesx($im);
                    $h = imagesy($im);
                    $targetW = min(300, $w);
                    $targetH = (int) ($h * ($targetW / $w));

                    $thumb = imagecreatetruecolor($targetW, $targetH);
                    imagecopyresampled($thumb, $im, 0, 0, 0, 0, $targetW, $targetH, $w, $h);
                    imagedestroy($im);

                    // Apply strong pixelation & multiple passes of Gaussian blur
                    imagefilter($thumb, IMG_FILTER_PIXELATE, 22);
                    for ($i = 0; $i < 10; $i++) {
                        imagefilter($thumb, IMG_FILTER_GAUSSIAN_BLUR);
                    }

                    imagejpeg($thumb, $cacheFile, 65);
                    imagedestroy($thumb);

                    return response()->file($cacheFile, [
                        'Content-Type' => 'image/jpeg',
                        'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                    ]);
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        return $this->serveSvgSilhouette($photo);
    }

    protected function serveSvgSilhouette(Photo $photo): Response
    {
        $gender = $photo->profile?->user?->gender ?? 'female';
        $icon = $gender === 'female' ? '🧕' : '🧔';
        $label = 'Photo Protected (FR-2.2)';

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#064e3b" />
      <stop offset="100%" stop-color="#022c22" />
    </linearGradient>
  </defs>
  <rect width="100%" height="100%" fill="url(#g)" />
  <circle cx="200" cy="170" r="70" fill="#047857" opacity="0.4" />
  <text x="200" y="195" font-size="72" text-anchor="middle" font-family="sans-serif">{$icon}</text>
  <rect x="60" y="275" width="280" height="34" rx="17" fill="#000000" opacity="0.6" />
  <text x="200" y="297" font-size="13" font-weight="bold" fill="#fef3c7" text-anchor="middle" font-family="sans-serif">🔒 {$label}</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ]);
    }
}
