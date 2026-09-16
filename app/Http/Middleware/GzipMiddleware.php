<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gzip/Brotli Compression Middleware (P6 Fix)
 * Provides server-level compression via PHP for static assets
 * KILL-SWITCH: GZIP_ENABLED=false in .env disables all compression
 */
class GzipMiddleware
{
 public function handle(Request $request, Closure $next)
 {
 $response = $next($request);
 
 // KILL-SWITCH CHECK
 if (!config('app.gzip_enabled', true)) {
 return $response;
 }
 
 // Skip if already compressed
 $encodingType = $response->headers->get('Content-Encoding');
 if ($encodingType) {
 return $response;
 }
 
 $contentType = $response->headers->get('Content-Type', '');
 
 // Only compress text-based content
 if (!$this->isCompressible($contentType)) {
 return $response;
 }
 
 // Don't compress tiny responses
 $content = $response->getContent();
 if (strlen($content) < 1024) {
 return $response;
 }
 
 // Detect preferred encoding
 $acceptEncoding = $request->headers->get('Accept-Encoding', '');
 
 if (strpos($acceptEncoding, 'br') !== false && function_exists('brotli_compress')) {
 $compressed = brotli_compress($content, 11);
 if ($compressed !== false && strlen($compressed) < strlen($content)) {
 $response->setContent($compressed);
 $response->headers->set('Content-Encoding', 'br');
 $response->headers->set('Content-Length', strlen($compressed));
 $response->headers->set('Vary', 'Accept-Encoding');
 }
 } elseif (strpos($acceptEncoding, 'gzip') !== false && function_exists('gzencode')) {
 $compressed = gzencode($content, 9);
 if ($compressed !== false && strlen($compressed) < strlen($content)) {
 $response->setContent($compressed);
 $response->headers->set('Content-Encoding', 'gzip');
 $response->headers->set('Content-Length', strlen($compressed));
 $response->headers->set('Vary', 'Accept-Encoding');
 }
 }
 
 return $response;
 }
 
 /**
 * Check if content type should be compressed
 */
 private function isCompressible($contentType)
 {
 $compressibleTypes = [
 'text/html',
 'text/css',
 'text/javascript',
 'text/xml',
 'application/javascript',
 'application/x-javascript',
 'application/json',
 'application/xml',
 'application/rss+xml',
 'application/atom+xml',
 'image/svg+xml',
 ];
 
 foreach ($compressibleTypes as $type) {
 if (stripos($contentType, $type) !== false) {
 return true;
 }
 }
 
 return false;
 }
}