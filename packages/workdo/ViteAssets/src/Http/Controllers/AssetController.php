<?php

namespace Workdo\ViteAssets\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Response;

class AssetController extends Controller
{
 /**
 * Serve Vite-built assets with optional compression
 */
 public function serveAsset($file)
 {
 $assetPath = public_path('build/assets/' . $file);
 
 if (!file_exists($assetPath)) {
 abort(404);
 }
 
 // Determine content type
 $extension = pathinfo($assetPath, PATHINFO_EXTENSION);
 $contentTypes = [
 'css' => 'text/css; charset=utf-8',
 'js' => 'application/javascript; charset=utf-8',
 'map' => 'application/json; charset=utf-8',
 ];
 
 $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
 
 // Read file content
 $content = file_get_contents($assetPath);
 
 // Return response without middleware (compression handled by GzipMiddleware)
 $response = Response($content, 200);
 $response->header('Content-Type', $contentType);
 $response->header('Cache-Control', 'public, max-age=31536000, immutable');
 
 return $response;
 }
}