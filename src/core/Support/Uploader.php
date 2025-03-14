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
    
}