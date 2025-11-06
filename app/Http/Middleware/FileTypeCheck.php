<?php

namespace App\Http\Middleware;

use Closure;

class FileTypeCheck
{
    public function handle($request, Closure $next)
    {
        // Check all files in the request
        foreach ($request->files->all() as $file) {
            if ($file) {
                // Block double extensions (e.g. file.jpg.php)
                $filename = $file->getClientOriginalName();
                if (substr_count($filename, '.') > 1) {
                    abort(422, 'Invalid file: double extension not allowed.');
                }

                // Restrict mime type (image example)
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                if (!in_array($file->getMimeType(), $allowedMimes, true)) {
                    abort(422, 'Invalid file type: only jpeg, png, jpg, webp allowed.');
                }
            }
        }
        return $next($request);
    }
}

