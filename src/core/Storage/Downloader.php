<?php

namespace Semiorbit\Storage;


use Semiorbit\Config\Config;


/**
 * Send an authorized local file through Apache, Nginx, or PHP.
 *
 * Install at src/core/Support/Downloader.php. Requires PHP 8.1+ and Fileinfo.
 * The caller must check access and supply a trusted local path. This class
 * does not choose a storage root: pass File::RealPath() or another local path.
 * Send() ends the request, preventing framework rendering after the file.
 *
 * Defaults: src/core/Config/Default/downloads.inc.
 * Optional application config/downloads.inc overrides:
 *
 * return [
 *     'http_server' => 'apache',         // apache | nginx | php
 *     'sendfile_enabled' => false,       // Enable after configuring the server.
 *     'nginx_mappings' => [
 *         '/srv/example/storage/' => '/_storage/',
 *     ],
 * ];
 *
 * DOWNLOAD_HTTP_SERVER overrides the server setting through env(). Otherwise,
 * HttpServer overrides Config; Config defaults to apache. Null static
 * properties mean read Config. Other settings do not read the environment.
 *
 * Apache needs mod_xsendfile, XSendFile On, and an allowed XSendFilePath.
 * Nginx needs a matching internal location, for example:
 *
 * location /_storage/ {
 *     internal;
 *     alias /srv/example/storage/;
 * }
 *
 * Offload is disabled by default. PHP is used when disabled, when the server
 * is php, or when no Nginx mapping matches. An offload header cannot be tested
 * for success after handoff: incorrect server configuration cannot trigger
 * a second PHP response. PHP sends the complete file (no byte-range support).
 * Server offload may support byte ranges using its own configuration.
 *
 * @example Downloader::Send($myProduct->Brochure->RealPath());
 * @example Downloader::Send($myProduct->ProductPhoto->RealPath(), null, true);
 * @example Downloader::Send('/srv/example/storage/results/report.pdf', 'Result.pdf');
 */

class Downloader
{

    /** Config fallback for the HTTP server; environment has priority. */

    public static ?string $HttpServer = null;

    /** Whether server offload has been configured; null reads Config. */

    public static ?bool $SendfileEnabled = null;

    /** @var array<string, string>|null Local directory => Nginx internal URI. */

    public static ?array $NginxMappings = null;


    /**
     * Resolve DOWNLOAD_HTTP_SERVER, the configured server, then apache.
     *
     * @return string apache, nginx, or php.
     * @throws \InvalidArgumentException For an unsupported server name.
     */

    public static function HttpServer(): string
    {

        $http_server = \env('DOWNLOAD_HTTP_SERVER');


        if ($http_server === null || $http_server === '')

            $http_server = static::$HttpServer ?? Config::DownloadHttpServer();


        $http_server = strtolower(trim((string) $http_server));


        if (!in_array($http_server, ['apache', 'nginx', 'php'], true))

            throw new \InvalidArgumentException('Unsupported download HTTP server.');


        return $http_server;

    }


    /**
     * Send a local file and end the request. Call before sending any output.
     *
     * @param string $file_path Trusted local path, including the extension.
     * @param string|null $download_name Browser filename; null uses basename.
     *                                  Must be valid UTF-8.
     * @param bool $inline Allow browser display instead of forcing a download.
     *                     Use only for content suitable for inline display.
     * @param bool $no_cache prevent caching
     * @return never
     */

    public static function Send(string $file_path, ?string $download_name = null, bool $inline = false, bool $no_cache = false): never
    {

        $http_server = static::HttpServer();

        $file_path = realpath($file_path);


        if ($file_path === false || !is_file($file_path) || !is_readable($file_path))

            throw new \RuntimeException('Download file not found or not readable.', 404);


        if (preg_match('/[\x00-\x1F\x7F]/', $file_path))

            throw new \InvalidArgumentException('Invalid download path.');


        $disposition = static::Disposition($download_name ?? basename($file_path), $inline);

        $myFileInfo = new \finfo(FILEINFO_MIME_TYPE);

        $mime_type = $myFileInfo->file($file_path) ?: 'application/octet-stream';

        $sendfile_enabled = static::$SendfileEnabled ?? (bool) Config::DownloadSendfileEnabled();

        $nginx_uri = $sendfile_enabled && $http_server === 'nginx' ? static::NginxUri($file_path) : null;

        $use_apache = $sendfile_enabled && $http_server === 'apache';

        $use_php = !$use_apache && $nginx_uri === null;


        static::PrepareOutput();

        // Open before sending headers so an unreadable file is still reportable.

        $file_handle = $use_php ? @fopen($file_path, 'rb') : null;


        if ($use_php && $file_handle === false)

            throw new \RuntimeException('Download file could not be opened.', 404);


        http_response_code(200);


        if ($no_cache) {

            header('Cache-Control: private, no-store, no-transform');

            header('Pragma: no-cache');

            header('Expires: 0');

        } else {

            header('Cache-Control: private, max-age=6048000, no-transform');

            header_remove('Pragma');

            header_remove('Expires');

        }


        header('Content-Type: ' . $mime_type);

        header('Content-Disposition: ' . $disposition);

        header('X-Content-Type-Options: nosniff');


        if ($use_apache) {

            header('X-Sendfile: ' . $file_path);

            exit;

        }


        if ($nginx_uri !== null) {

            header('X-Accel-Redirect: ' . $nginx_uri);

            exit;

        }


        // Stream from the open file; never load the entire file into a string.

        $file_stat = fstat($file_handle);


        if ($file_stat !== false && !ini_get('zlib.output_compression'))

            header('Content-Length: ' . $file_stat['size']);


        header('Accept-Ranges: none');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {


            $bytes_sent = @fpassthru($file_handle);


            if ($file_stat !== false && $bytes_sent < $file_stat['size'])

                error_log('Downloader: file streaming was incomplete.');

        }


        fclose($file_handle);

        exit;

    }


    /** Remove pending page output and release the PHP session lock. */

    protected static function PrepareOutput(): void
    {

        if (headers_sent())

            throw new \RuntimeException('Download must begin before output is sent.');


        if (session_status() === PHP_SESSION_ACTIVE)

            session_write_close();


        while (ob_get_level() > 0) {


            if (!@ob_end_clean())

                throw new \RuntimeException('Cannot clear the download output buffer.');

        }


        if (headers_sent())

            throw new \RuntimeException('Output was sent while preparing the download.');


        @ini_set('zlib.output_compression', '0');


        foreach (['Content-Length', 'Content-Range', 'Content-Encoding',

                  'Transfer-Encoding', 'Accept-Ranges', 'ETag', 'Last-Modified',

                  'X-Sendfile', 'X-Accel-Redirect'] as $header_name)

            header_remove($header_name);

    }


    /** Build a quoted ASCII fallback and a UTF-8 filename parameter. */

    protected static function Disposition(string $download_name, bool $inline): string
    {

        $download_name = basename(str_replace('\\', '/', $download_name));


        if ($download_name === '' || $download_name === '.' || $download_name === '..'

            || preg_match('/[\x00-\x1F\x7F]/', $download_name)

            || preg_match('//u', $download_name) !== 1)

            throw new \InvalidArgumentException('Invalid download filename.');


        $fallback_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $download_name);

        return ($inline ? 'inline' : 'attachment')

            . '; filename="' . $fallback_name . '"'

            . "; filename*=UTF-8''" . rawurlencode($download_name);

    }


    /** Map the canonical local path using the longest matching directory. */

    protected static function NginxUri(string $file_path): ?string
    {

        $mappings = static::$NginxMappings ?? Config::DownloadNginxMappings();


        if (!is_array($mappings))

            throw new \InvalidArgumentException('Nginx mappings must be an array.');


        $file_path = str_replace(DIRECTORY_SEPARATOR, '/', $file_path);

        $matched_length = 0;

        $matched_uri = null;


        foreach ($mappings as $root_path => $internal_uri) {

            if (!is_string($root_path) || !is_string($internal_uri)

                || !str_starts_with($internal_uri, '/')

                || str_starts_with($internal_uri, '//')

                || preg_match('/[\x00-\x20\x7F?#]/', $internal_uri))

                throw new \InvalidArgumentException('Invalid Nginx mapping.');


            $root_path = realpath($root_path);

            if ($root_path === false || !is_dir($root_path)) continue;

            $root_path = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $root_path), '/') . '/';


            if (!str_starts_with($file_path, $root_path)

                || strlen($root_path) <= $matched_length)

                continue;


            $relative_path = substr($file_path, strlen($root_path));

            $matched_length = strlen($root_path);

            $matched_uri = rtrim($internal_uri, '/') . '/'

                . implode('/', array_map('rawurlencode', explode('/', $relative_path)));

        }


        return $matched_uri;

    }

}
