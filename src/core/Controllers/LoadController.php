<?php
//error_reporting(0);

namespace Semiorbit\Controllers;


use Semiorbit\Config\CFG;

class LoadController extends \Semiorbit\Http\Controller
{

    public function onStart()
    {

        $this->Actions->ExplicitMode()->Define(array(

            'index' => array('method' => 'Index', 'pms' => 'folder/file', 'allow' => SUPER_ADMIN)

        ));

    }

    public function Index()
    {

        CFG::$SanitizeOutput = false;

        $this->Request->PathInfoPattern = "/folder/file";

        $params = $this->Request->ParsePath();

        if (is_empty($params['folder'])) die('Not Found!');

        if (!is_empty($params['file'])) {

            if (!in_array($params['folder'], array('ext', 'images', 'css', 'js'))) die('Access Denied');

        } else {

            $params['file'] = $params['folder'];

            $params['folder'] = "ext";

        }

        $params['file'] = str_ireplace("|", "/", $params['file']);

        $pf_arr = explode("/", $params['file']);

        if (is_array($pf_arr)) {

            if (count($pf_arr) > 1) {

                $params['file'] = array_pop($pf_arr);

                $params['folder'] .= "/" . implode("/", $pf_arr);

            }

        }

        $path = \Semiorbit\Component\Finder::LookFor($params['file'], array(CFG::$Theme . "/" . $params['folder'], $params['folder']), true);


        if (!file_exists($path['path'])) die('Not Found!');



        $info = getimagesize($path['path']);

        $file_type = pathinfo( $path['path'], PATHINFO_EXTENSION );

        $content_type = "text/plain";

        if ( isset( $info['mime'] ) ) {

            $content_type = $info['mime'];

        } else {

            switch ( strtolower( $file_type ) ) {

                case "css":
                    $content_type = "text/css";
                    break;

                case "js":
                    $content_type = "text/javascript";
                    break;

                case "htm":
                    $content_type = "text/html";
                    break;

                case "html":
                    $content_type = "text/html";
                    break;

                case "pdf":
                    $content_type = "application/pdf";
                    break;

                case "exe":
                    $content_type = "application/octet-stream";
                    break;

                case "zip":
                    $content_type = "application/zip";
                    break;

                case "rar":
                    $content_type = "application/rar";
                    break;

                case "txt":
                    $content_type = "text/plain";
                    break;

                case "pps":
                    $content_type = "application/pps";
                    break;

                case "mdb":
                    $content_type = "application/mdb";
                    break;

                case "doc":
                    $content_type = "application/msword";
                    break;

                case "xls":
                    $content_type = "application/vnd.ms-excel";
                    break;

                case "ppt":
                    $content_type = "application/vnd.ms-powerpoint";
                    break;

                case "gif":
                    $content_type = "image/gif";
                    break;

                case "png":
                    $content_type = "image/png";
                    break;

                case "jpeg":
                case "jpg":
                    $content_type = "image/jpg";
                    break;

                case "mp3":
                    $content_type = "audio/mpeg";
                    break;

                case "wav":
                    $content_type = "audio/x-wav";
                    break;

                case "mpeg":
                case "mpg":
                case "mpe":
                    $content_type = "video/mpeg";
                    break;

                case "mov":
                    $content_type = "video/quicktime";
                    break;

                case "avi":
                    $content_type = "video/x-msvideo";
                    break;

                case "bmp":
                    $content_type = "image/bmp";
                    break;

                //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
                case "php":
                case "inc":
                    die("<b>Cannot run " . $file_type . " files!</b>");
                    break;


            }




        }

        ///ini_set('zlib.output_compression', 'on');

        //if ( extension_loaded('zlib') ) {

            //header('Vary: Accept-Encoding');

            //header('Content-Encoding: gzip');

        //}


        header("Content-type: $content_type");

        $bin = file_get_contents( $path['path'] );

       //echo function_exists( 'gzcompress' ) ? gzcompress($bin, 9) :  $bin;

        echo $bin;




    }



}