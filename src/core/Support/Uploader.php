<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - STRING HELPER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Support;


use Semiorbit\Data\Msg;
use Semiorbit\Debug\FileLog;
use SemiorbitUpWatch\UpWatch;


class Uploader
{

    /** Maximum file size allowed (50MB default) */
    const MAX_SIZE = 52428800;

    /** SVG processing mode: safe | rasterize | raw */
    const SVG_MODE = 'safe';


    /**
     * Upload
     *
     * Validates, sanitizes, and stores a file upload. Supports:
     * - Image validation (JPEG/PNG/GIF/WebP)
     * - SVG sanitization or rasterization
     * - PDF validation
     * - Dangerous content scanning
     * - MIME + extension enforcement
     *
     * @param array|string $input_file     $_FILES[] array or literal file path.
     * @param string $upload_dir     Target storage directory.
     * @param string $target_filename Desired filename (without extension).
     *
     * @return int Msg::* constant representing success or failure reason.
     */

    public static function Upload(array|string $input_file, string $upload_dir, string $target_filename): int
    {

        $is_array = is_array($input_file);

        $tmp_path  = $is_array ? $input_file['tmp_name'] : $input_file;

        $orig_name = $is_array ? $input_file['name']     : basename($input_file);


        if (!file_exists($tmp_path)) {

            return Msg::UPLOAD_FAILED;

        }


        // 1. Size check

        if (!FileSanitization::ValidateSize($tmp_path, self::MAX_SIZE)) {

            return Msg::FILE_SIZE_ERR;

        }



        // 2. MIME detection

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime  = finfo_file($finfo, $tmp_path);

        finfo_close($finfo);


        if (!is_string($mime)) {

            return Msg::FILE_TYPE_ERR;

        }


        // 3. Determine safe extension

        $ext = static::SecureExtensionFromMime($mime, $orig_name);

        if (!$ext) return Msg::FILE_TYPE_ERR;


        if (!static::ExtensionMatchesMimeCategory($ext, $mime)) {

            return Msg::FILE_TYPE_ERR;

        }


        // 4. Validation by MIME

        if (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {

            if (!FileSanitization::ValidateImage($tmp_path)) {

                return Msg::FILE_TYPE_ERR;
            }

        }


        if ($mime === 'image/svg+xml') {

            if (self::SVG_MODE === 'safe') {

                if (!FileSanitization::SanitizeSvgSafe($tmp_path)) {

                    return Msg::FILE_TYPE_ERR;
                }
            }

            elseif (self::SVG_MODE === 'rasterize') {

                $png = FileSanitization::RasterizeSvg($tmp_path);

                if (!$png) return Msg::FILE_TYPE_ERR;

                $tmp_path = $png;

                $ext = 'png';

            }

            elseif (self::SVG_MODE === 'raw') {

                if (!FileSanitization::SanitizeSvgRaw($tmp_path)) {

                    return Msg::FILE_TYPE_ERR;

                }

            }

        }


        if ($mime === 'application/pdf') {

            if (!FileSanitization::ValidatePdf($tmp_path)) {

                return Msg::FILE_TYPE_ERR;
            }

        }


        // 5. Generic dangerous signature scan

        if (FileSanitization::ContainsDangerousCode($tmp_path)) {

            return Msg::FILE_TYPE_ERR;

        }
        
        // 6. Move the final file

        if (!is_dir($upload_dir)) {

            mkdir($upload_dir, 0755, true);

        }

        $target_file = rtrim($upload_dir, '/') . '/' . $target_filename . '.' . $ext;

        $ok = $is_array ? move_uploaded_file($tmp_path, $target_file)

            : copy($tmp_path, $target_file);


        if (!$ok) return Msg::UPLOAD_FAILED;


        // 7. Force non-executable permission

        @chmod($target_file, 0644);


        // 8. Add to UpWatch for security scan

        UpWatch::file($target_file, FileLog::LogDirPath() . 'uploads.log', false);


        return Msg::UPLOAD_OK;

    }


    public static function ExtensionMatchesMimeCategory(string $ext, string $mime): bool
    {

        $ext = strtolower($ext);

        // IMAGE EXTENSIONS MUST HAVE REAL IMAGE MIME

        if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {

            return str_starts_with($mime, 'image/');

        }


        // SVG MUST HAVE SVG MIME

        if ($ext === 'svg') {

            return ($mime === 'image/svg+xml');

        }


        // PDF handled by real PDF validator, but require correct MIME family

        if ($ext === 'pdf') {

            return str_starts_with($mime, 'application/pdf');

        }

        // Otherwise: generic files
        // Allow any MIME except explicitly prohibited ones

        return !Uploader::IsProhibitedMime($mime);

    }


    /**
     * SecureExtensionFromMime
     *
     * Maps MIME type to a secure extension. Falls back to original
     * file extension only if it matches a safe pattern.
     *
     * @param string $mime      MIME type detected via finfo.
     * @param string $origName  Original filename for fallback extension.
     *
     * @return string|false Safe extension or FALSE if invalid.
     */

    public static function SecureExtensionFromMime(string $mime, string $origName): string|false
    {


        // 1. Block prohibited MIME types

        if (static::IsProhibitedMime($mime)) {

            return false;

        }


        // 2. Extract original extension (lowercase)

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));


        // 3. Block prohibited extensions

        if (static::IsProhibitedExtension($ext)) {

            return false;

        }


        // 4. Accept extension if it is at least syntactically safe

        // (1–6 chars, alphanumeric only)

        if (preg_match('/^[a-zA-Z0-9]{1,6}$/', $ext)) {

            return $ext;

        }


        // 5. Otherwise fail

        return false;

    }

    public static function IsProhibitedExtension(string $ext): bool
    {

        $bad = [

            // Executable code
            'php','php3','php4','php5','php7','php8','phtml','phar',
            'cgi','exe','msi','bin','dll','com',

            // Shell / script
            'sh','bash','ksh','csh','zsh',
            'bat','cmd','ps1','psm1',
            'vbs','vbe','js','jse','wsf','wsh',
            'py','pyc','pyo','pyw',
            'rb','gemspec',
            'pl','lua',
            'reg','scr',

            // Dangerous archives
            '7z','rar','tar','gz','tgz',

            // JSP / Java
            'jsp','jspx','jspf','war','jar','class',

        ];

        return in_array($ext, $bad, true);

    }

    public static function IsProhibitedMime(string $mime): bool
    {

        $bad = [

            // Executable formats
            'text/html',
            'application/x-php',
            'application/x-httpd-php',
            'application/x-perl',
            'application/x-python',
            'application/x-sh',
            'application/x-shellscript',
            'application/javascript',
            'text/javascript',
            'application/x-msdownload',
            'application/x-executable',
            'application/x-dosexec',

            // JSP / Java related
            'application/x-jsp',
            'application/jsp',
            'text/jsp',
            'application/java',
            'application/java-archive',   // JAR/WAR

            // JVM scripting
            'application/x-groovy',
            'application/x-scala',

            // Dangerous archive formats
            'application/x-7z-compressed',
            'application/x-rar',
            'application/x-tar',
            'application/gzip',
        ];

        return in_array($mime, $bad, true);

    }



//    public static function LegacyUpload($input_file, $upload_dir, $target_filename)
//    {
//
//        if ( is_array($input_file) ) {
//
//            $target_file = $upload_dir . $target_filename . "." . static::FileExt( $input_file['name'] );
//
//            if ( move_uploaded_file( $input_file['tmp_name'], $target_file ) ) return Msg::UPLOAD_OK;
//
//        } else if ( is_string($input_file) ) {
//
//            $target_file = $upload_dir . $target_filename . "." . static::FileExt( $input_file );
//
//            if ( copy( $input_file, $target_file ) ) return Msg::UPLOAD_OK;
//
//        }
//
//        return Msg::UPLOAD_FAILED;
//
//    }


    public static function IsAllowedFileType($input_file, string $allowed_types): bool
    {

        // ---------------------------------------------------
        // 1) Resolve original filename and tmp path
        // ---------------------------------------------------

        $filename = is_array($input_file) ? $input_file['name']     : $input_file;

        $filepath = is_array($input_file) ? $input_file['tmp_name'] : $input_file;

        if (!file_exists($filepath)) return false;


        $filename = strtolower($filename);


        // ---------------------------------------------------
        // 2) EXTENSION check (regex or list)
        //    $allowed_types['extensions'] = 'jpg|jpeg|png|pdf'
        // ---------------------------------------------------

        if (!empty($allowed_types)) {

            if (!preg_match('/\.(' . $allowed_types . ')$/i', $filename)) {

                return false;

            }

        }


        // ---------------------------------------------------
        // 3) MIME TYPE check
        //    $allowed_types['mimes'] = ['image/jpeg', 'image/png', ...]
        // ---------------------------------------------------

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime  = finfo_file($finfo, $filepath);

        finfo_close($finfo);


        if (!empty($allowed_types['mimes'])) {

            if (!in_array($mime, $allowed_types['mimes'], true)) {

                return false;

            }

        }


        // ---------------------------------------------------
        // 4) OPTIONAL: ensure extension matches MIME map
        // ---------------------------------------------------

        if (!empty($allowed_types['mime_map'])) {

            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if (isset($allowed_types['mime_map'][$mime])) {

                if (!in_array($ext, $allowed_types['mime_map'][$mime], true)) {

                    return false;

                }

            }

        }

        return true;

    }


    public static function FileExt($file)
    {
        return strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
    }

    /**
     * @throws \ImagickException
     */
    public static function CreateThumbnail($original_image_path, $thumbnail_directory, $target_file_name, $thumbnail_width, $thumbnail_height = 0, $square = false)
    {


        //Getting Image Type & Creating Image using proper function.

        $img_type = static::FileExt($original_image_path);


        if (extension_loaded('imagick')) {

            // Use Imagick if available

            $image = new \Imagick($original_image_path);

            $orig_width = $image->getImageWidth();

            $orig_height = $image->getImageHeight();

            if ($square) {

                $thumbnail_height = $thumbnail_width;

                $image->cropThumbnailImage($thumbnail_width, $thumbnail_height);

            } elseif ($thumbnail_height == 0) {

                $thumbnail_height = intval($orig_height * ($thumbnail_width / $orig_width));

                $image->resizeImage($thumbnail_width, $thumbnail_height, \Imagick::FILTER_LANCZOS, 1);

            } else {

                $image->resizeImage($thumbnail_width, $thumbnail_height, \Imagick::FILTER_LANCZOS, 1);

            }

            // Ensure directory exists

            if (!file_exists($thumbnail_directory)) mkdir($thumbnail_directory, 0755, true);


            // Save the new thumbnail

            $target_file_path = "{$thumbnail_directory}/{$target_file_name}." . $img_type;

            $image->writeImage($target_file_path);

            $image->destroy();

            return file_exists($target_file_path);

        }

        // Fallback to GD if Imagick is not available


        switch ( $img_type ) {

            case "jpg":
            case "jpeg":

                $src_img = imagecreatefromjpeg($original_image_path);
                break;

            case "png":

                $src_img = imagecreatefrompng($original_image_path);
                break;

            case "gif":

                $src_img = imagecreatefromgif($original_image_path);
                break;

            case "bmp":

                $src_img = imagecreatefromwbmp($original_image_path);
                break;

            default:

                return false;
                break;

        }

        //Getting Original Width & Height

        $orig_width = imagesx($src_img);

        $orig_height = imagesy($src_img);

        //Calculating New Height

        if ($square) {

            $thumbnail_height = $thumbnail_width;

        } elseif ( $thumbnail_height == 0 ) {

            /*if ($origWidth>$origHeight) {
                $ratio = $origWidth / $thumbWidth;
                $thumbHeight = $origHeight * $ratio;
            }else{
                $thumbHeight = $thumbWidth;
                $ratio = $origHeight / $thumbHeight;
                $thumbWidth = $origWidth * $ratio;
            }*/

            $ratio = ( $orig_width / $thumbnail_width );

            $thumbnail_height = $orig_height / $ratio;

        }

        //Creating Resized Image

        $thumb_img = imagecreatetruecolor( $thumbnail_width, $thumbnail_height );

        imagealphablending($thumb_img, false);

        imagesavealpha($thumb_img,true);

        $transparent = imagecolorallocatealpha($thumb_img, 255, 255, 255, 127);

        imagefilledrectangle($thumb_img, 0, 0, $thumbnail_width, $thumbnail_height, $transparent);
        
        imagecopyresampled( $thumb_img, $src_img, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, imagesx($src_img), imagesy($src_img) );
        
        //Saving new thumb
        
        $target_file_path = "{$thumbnail_directory}/{$target_file_name}.{$img_type}";
        
        if ( file_exists($target_file_path) ) unlink($target_file_path);
        
        switch ( $img_type ) {
            
            case "jpg":
            case "jpeg":
                
                return imagejpeg( $thumb_img, $target_file_path );
                break;
            
            case "png":
                
                return imagepng( $thumb_img, $target_file_path );
                break;
            
            case "gif":
                
                return imagegif( $thumb_img, $target_file_path );
                break;
            
            case "bmp":
                
                return imagewbmp( $thumb_img, $target_file_path );
                break;
            
            default:
                
                return false;
                break;
            
        }
        
    }


    /**
     * Retrieves an uploaded file from the $_FILES array, handling both simple and nested structures.
     *
     * Supported Cases:
     * 1. Single file input: <input type="file" name="avatar"><br/>
     *    Usage: `$avatar = InputFile('avatar');`
     *
     * 2. Multiple files input: <input type="file" name="documents[]" multiple><br/>
     *    Usage: `$firstDocument = InputFile('documents', 0);`
     *
     * 3. Nested multiple files: <input type="file" name="attachments[invoice][]" multiple><br/>
     *    Usage: `$firstInvoice = InputFile('attachments.invoice', 0);`
     *
     * 4. Nested single file: <input type="file" name="attachments[contract]"><br/>
     *    Usage: `$contract = InputFile('attachments.contract');`
     *
     * @param string $key The file input name like 'photo' or 'attachments.contract' (supports dot notation for nested keys).
     * @param int|null $index Optional index for multiple file inputs.
     * @return array|null Returns the file array if found, otherwise null.
     */

    public static function InputFile(string $key, int $index = null): ?array
    {

        // Split key if it uses dot notation

        $keys = explode('.', $key);

        $mainKey = $keys[0];

        $subKey = $keys[1] ?? null;

        // Check if the main key exists in $_FILES

        if (!isset($_FILES[$mainKey])) {

            return null;

        }


        $files = $_FILES[$mainKey];


        // If it's a single file (not an array)

        if (!is_array($files['name'])) {

            return $files;

        }


        // If it's a flat multiple file upload (e.g., name="passport_page[]")

        if (isset($files['name'][0]) && is_string($files['name'][0])) {

            $organizedFiles = [];

            foreach ($files['name'] as $i => $name) {

                if (!empty($name)) {

                    $organizedFiles[] = [

                        'name'     => $name,

                        'type'     => $files['type'][$i] ?? null,

                        'tmp_name' => $files['tmp_name'][$i] ?? null,

                        'error'    => $files['error'][$i] ?? null,

                        'size'     => $files['size'][$i] ?? null,

                    ];

                }
            }


            return $index !== null ? ($organizedFiles[$index] ?? null) : $organizedFiles;

        }


        // If it's a nested file array (e.g., name="pickup[id_copy][]")

        $organizedNestedFiles = [];

        foreach ($files['name'] as $field => $fileNames) {

            foreach ($fileNames as $i => $name) {

                if (!empty($name)) {

                    $organizedNestedFiles[$field][$i] = [

                        'name'     => $name,

                        'type'     => $files['type'][$field][$i] ?? null,

                        'tmp_name' => $files['tmp_name'][$field][$i] ?? null,

                        'error'    => $files['error'][$field][$i] ?? null,

                        'size'     => $files['size'][$field][$i] ?? null,

                    ];

                }

            }

        }


        // Handle dot notation subKey

        if ($subKey !== null) {

            if (!isset($organizedNestedFiles[$subKey])) {

                return null;

            }

            return $index !== null ? ($organizedNestedFiles[$subKey][$index] ?? null) : $organizedNestedFiles[$subKey];

        }


        return $organizedNestedFiles;

    }


}