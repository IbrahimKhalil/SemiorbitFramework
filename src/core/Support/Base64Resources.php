<?php

namespace Semiorbit\Support;


class Base64Resources
{


    public static function ExtractFromHtml(string $html, string $dir_url, string $types = 'gif|png|jpeg') : array
    {


        preg_match('#data:image/('.$types.');base64,([\w=+/]++)#', $html, $x);

        $images = [];

        while(isset($x[0]))
        {

            $img_data = explode(",", $x[0])[1];

            $info = explode(";", explode("/", $x[0])[1])[0];

            $img_fn = hash('sha256', $img_data) . "." . $info;


            $images[] = [$img_data, $img_fn];


            $html = str_replace($x[0], Path::Normalize($dir_url) . $img_fn, $html);

            preg_match('#data:image/(gif|png|jpeg);base64,([\w=+/]++)#', $html, $x);


        }

        return [$html, $images];

    }


}