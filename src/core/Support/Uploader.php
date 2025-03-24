<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - STRING HELPER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Support;


use Semiorbit\Data\Msg;




class Uploader
{

    public static function Upload($input_file, $upload_dir, $target_filename)
    {

        if ( is_array($input_file) ) {

            $target_file = $upload_dir . $target_filename . "." . static::FileExt( $input_file['name'] );

            if ( move_uploaded_file( $input_file['tmp_name'], $target_file ) ) return Msg::UPLOAD_OK;

        } else if ( is_string($input_file) ) {

            $target_file = $upload_dir . $target_filename . "." . static::FileExt( $input_file );

            if ( copy( $input_file, $target_file ) ) return Msg::UPLOAD_OK;

        }

        return Msg::UPLOAD_FAILED;

    }


    public static function IsAllowedFileType($input_file_id, $allowed_file_types)
    {

        $fn = is_array($input_file_id) ? $input_file_id['name'] : $input_file_id;

        if ( empty($allowed_file_types) || preg_match("/" . $allowed_file_types . "$/i", strtolower($fn) ) ) {

            return true;

        }

        return false;

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