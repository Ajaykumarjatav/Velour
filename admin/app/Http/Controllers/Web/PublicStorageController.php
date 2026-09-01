<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\PublicStorage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves files from the public disk when `public/storage` is not a symlink.
 *
 * Shared hosting often refuses symlinks, which leaves every `asset('storage/…')`
 * URL — logos, banners, photos, avatars — returning the app's 404 page. Those
 * URLs fall through to this route, so uploads work with or without the link.
 * Where the symlink does exist the web server answers first and this never runs.
 */
class PublicStorageController extends Controller
{
    /** Uploads are user supplied, so anything the server might execute is refused. */
    private const BLOCKED_EXTENSIONS = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'htaccess', 'htm', 'html', 'js', 'svgz'];

    public function show(string $path): BinaryFileResponse
    {
        // Flysystem throws on traversal, which would surface as a 500.
        try {
            $resolved = PublicStorage::resolveExistingPath($path);
        } catch (\Throwable) {
            abort(404);
        }

        abort_if($resolved === null, 404);
        abort_if(in_array(strtolower(pathinfo($resolved, PATHINFO_EXTENSION)), self::BLOCKED_EXTENSIONS, true), 404);

        return response()
            ->file(Storage::disk('public')->path($resolved), [
                'Cache-Control' => 'public, max-age=31536000',
            ]);
    }
}
