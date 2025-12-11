<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Semiorbit\Support;

use DOMDocument;
use DOMXPath;

/**
 * FileSanitization
 *
 * Provides validation and sanitization utilities for uploaded files.
 * This class is used internally by the Uploader class but can also be
 * called directly for any public-facing validations.
 */

class FileSanitization
{

    /**
     * ValidateSize
     *
     * Ensures that a file exists and its size is within allowed limits.
     *
     * @param string $path      Full path to file.
     * @param int    $maxBytes  Maximum allowed size in bytes.
     *
     * @return bool  TRUE if file size is valid, FALSE otherwise.
     */

    public static function ValidateSize(string $path, int $maxBytes = 52428800): bool
    {

        $size = @filesize($path);

        return ($size !== false && $size > 0 && $size <= $maxBytes);

    }


    /**
     * ContainsDangerousCode
     *
     * Performs a lightweight scan on the first 8 KB of the file to detect
     * embedded executable code such as PHP, JS, HTML event handlers, etc.
     *
     * This protects against disguised files like image.jpg.php or files
     * containing malicious payloads.
     *
     * @param string $path  Path to the file to scan.
     *
     * @return bool TRUE if dangerous patterns found, FALSE otherwise.
     */

    public static function ContainsDangerousCode(string $path): bool
    {

        $patterns = [

            // PHP
            '<?php', '<?=', '<%=', '<%php', 'eval(', 'assert(', 'preg_replace(', 'base64_decode(',

            // JavaScript / HTML
            '<script', 'javascript:', 'onload=', 'onclick=',

            // System commands
            'system(', 'exec(', 'shell_exec(', 'passthru(', 'popen(',

            // Windows scripting
            'wscript.', 'createobject(', 'vbscript', 'msgbox', 'wshshell',

            // Batch / PowerShell
            '@echo off', 'powershell', 'cmd.exe',

            // Shell
            '#!/bin/bash', '#!/bin/sh',

            // Python
            'import os', 'import subprocess', 'subprocess.',

            // Ruby
            'require "',

            // Perl
            '#!/usr/bin/perl',
        ];


        $chunkSize = 8192;   // 8 KB

        $overlap   = 512;    // Carry last 512 bytes for boundary check

        $buffer    = '';     // Holds trailing data from previous chunk


        $handle = fopen($path, 'rb');

        if (!$handle) return false;

        while (!feof($handle)) {

            $chunk = fread($handle, $chunkSize);

            if ($chunk === false) break;

            // Create combined window: previous tail + current chunk

            $window = strtolower($buffer . $chunk);

            foreach ($patterns as $p) {

                if (str_contains($window, $p)) {

                    fclose($handle);

                    return true;

                }

            }

            // Prepare overlap buffer for next loop

            $buffer = substr($window, -$overlap);

        }


        fclose($handle);

        return false;

    }



    /**
     * ValidateImage
     *
     * Smart single-function image validation with automatic fallback layers.
     *
     * Priority:
     *  1. GD full decode test (most secure, detects fake/corrupt images)
     *  2. exif_imagetype() magic-byte validation (header signature check)
     *  3. getimagesize() fallback (checks format + dimensions)
     *
     * This method ensures the file is a real image even on systems that
     * lack GD or EXIF extensions. No metadata is modified.
     *
     * @param string $path  Path to the image file.
     *
     * @return bool TRUE if file is a valid image, FALSE if invalid or suspicious.
     */

    public static function ValidateImage(string $path)
    {

        // -----------------------------------------
        // LAYER 1: GD image decode (best validation)
        // -----------------------------------------

        if (function_exists('imagecreatefromstring')) {

            $data = @file_get_contents($path);

            if ($data === false) return false;

            $img = @imagecreatefromstring($data);

            if ($img === false) return false;


            imagedestroy($img);

            return true;

        }


        // -----------------------------------------
        // LAYER 2: exif_imagetype() (magic bytes)
        // -----------------------------------------

        if (function_exists('exif_imagetype')) {

            $type = @exif_imagetype($path);

            if ($type === false) return false;

            return in_array($type, [

                IMAGETYPE_JPEG,

                IMAGETYPE_PNG,

                IMAGETYPE_GIF,

                IMAGETYPE_WEBP,

                IMAGETYPE_BMP,

            ], true);

        }


        // -----------------------------------------
        // LAYER 3: getimagesize() fallback
        // -----------------------------------------

        if (function_exists('getimagesize')) {

            $info = @getimagesize($path);

            return $info !== false;

        }


        // -----------------------------------------
        // NO IMAGE FUNCTIONS AVAILABLE
        // -----------------------------------------

        return false;

    }


    /**
     * SanitizeSvgSafe
     *
     * Secure SVG sanitizer that removes:
     * - <script>, <foreignObject>, <iframe>, <object>, <embed>
     * - External references (http, https, javascript)
     * - Inline event handlers (onclick, onload, etc.)
     *
     * This produces a clean SVG suitable for UI rendering and
     * user-generated content without executing scripts.
     *
     * @param string $path  Path to SVG file.
     *
     * @return bool TRUE if sanitized successfully, FALSE on failure.
     */

    public static function SanitizeSvgSafe(string $path)
    {

        $svg = @file_get_contents($path);

        if ($svg === false || stripos($svg, '<svg') === false) {

            return false;

        }


        $dom = new DOMDocument();

        libxml_use_internal_errors(true);

        if (!$dom->loadXML($svg, LIBXML_NONET)) return false;

        libxml_clear_errors();


        // Forbidden SVG tags

        $forbidden = [
            'script','foreignObject','iframe','embed','object','link','feImage'
        ];


        foreach ($forbidden as $tag) {

            $nodes = $dom->getElementsByTagName($tag);

            while ($nodes->length > 0) {

                $node = $nodes->item(0);

                $node->parentNode->removeChild($node);

            }

        }


        // Remove dangerous attributes (onclick, javascript:, etc.)

        $xpath = new DOMXPath($dom);

        $attrs = $xpath->query('//@*');


        foreach ($attrs as $attr) {

            $name  = strtolower($attr->nodeName);

            $value = strtolower($attr->nodeValue);


            if (str_starts_with($name, 'on')) {

                $attr->ownerElement->removeAttributeNode($attr);

                continue;

            }


            if (str_contains($value, 'javascript:') ||

                str_contains($value, 'data:text/html') ||

                str_contains($value, 'http://') ||

                str_contains($value, 'https://')) {

                $attr->ownerElement->removeAttributeNode($attr);

            }

        }


        $clean = $dom->saveXML($dom->documentElement);

        file_put_contents($path, $clean);


        return true;

    }


    /**
     * RasterizeSvg
     *
     * Converts an SVG file into a PNG image using Imagick.
     * This is the ultra-safe mode that discards all SVG logic,
     * producing a pure bitmap instead.
     *
     * @param string $path Path to the input SVG file.
     *
     * @return string|false Path to PNG file on success, FALSE on failure.
     */

    public static function RasterizeSvg(string $path)
    {

        if (!class_exists('Imagick')) return false;


        try {

            $svg = file_get_contents($path);

            $img = new \Imagick();

            $img->setBackgroundColor('white');

            $img->readImageBlob($svg);

            $img->setImageFormat("png");


            $rasterized = $path . ".png";

            $img->writeImage($rasterized);

            return $rasterized;

        } catch (\Exception $e) {

            return false;

        }

    }


    /**
     * SanitizeSvgRaw
     *
     * Developer-mode SVG acceptance:
     * Allows raw SVG content but still blocks obvious injected scripts.
     *
     * @param string $path Path to SVG file.
     *
     * @return bool TRUE if SVG contains no harmful code, FALSE otherwise.
     */

    public static function SanitizeSvgRaw(string $path): bool
    {
        return !static::ContainsDangerousCode($path);
    }


    /**
     * ValidatePdf
     *
     * Performs basic PDF validation:
     * - Ensures the file begins with "%PDF"
     * - Rejects PDFs containing embedded JavaScript ("/JS" or "/JavaScript")
     *
     * @param string $path Path to PDF file.
     *
     * @return bool TRUE if PDF is safe, FALSE otherwise.
     */

    public static function ValidatePdf(string $path): bool
    {

        $hdr = @file_get_contents($path, false, null, 0, 1024);

        if ($hdr === false || stripos($hdr, '%PDF-') !== 0) {

            return false;

        }


        $contents = @file_get_contents($path);

        if ($contents === false) return false;


        if (stripos($contents, '/JavaScript') !== false ||

            stripos($contents, '/JS') !== false) {

            return false;

        }

        return true;

    }

}