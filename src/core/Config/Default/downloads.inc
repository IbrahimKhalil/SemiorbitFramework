<?php

use Semiorbit\Config\Config;


return [


    /*
    | -----------------------------------------------------------
    | HTTP SERVER
    | -----------------------------------------------------------
    |
    | Available: apache | nginx | php
    |
    | DOWNLOAD_HTTP_SERVER, when set, overrides this setting.
    | Otherwise Apache is used by default.
    |
    | Selecting a server does not enable its file transfer module.
    | Use "php" to always stream files through PHP.
    | -----------------------------------------------------------
    */

    Config::DOWNLOADS__HTTP_SERVER => 'apache',


    /*
    | -----------------------------------------------------------
    | SERVER FILE TRANSFER
    | -----------------------------------------------------------
    |
    | false: Stream files through PHP without additional server setup.
    | true:  Use the configured Apache or Nginx file transfer mechanism.
    |
    | Apache requires mod_xsendfile, XSendFile On, and XSendFilePath.
    | Nginx requires an internal location and a matching mapping below.
    |
    | Enable only after the corresponding server configuration is ready.
    | A failed server handoff cannot be retried through PHP automatically.
    | -----------------------------------------------------------
    */

    Config::DOWNLOADS__SENDFILE_ENABLED => false,


    /*
    | -----------------------------------------------------------
    | NGINX INTERNAL PATH MAPPINGS
    | -----------------------------------------------------------
    |
    | Absolute local directory => Nginx internal URI prefix.
    | The longest matching local directory is used.
    | With no matching mapping, the file is streamed through PHP.
    |
    | Example mapping:
    | '/srv/example/storage/' => '/_storage/',
    |
    | Corresponding Nginx server configuration:
    |
    | location /_storage/ {
    |     internal;
    |     alias /srv/example/storage/;
    | }
    |
    | The alias must refer to the same directory visible to PHP.
    | -----------------------------------------------------------
    */

    Config::DOWNLOADS__NGINX_MAPPINGS => [

        // '/srv/example/storage/' => '/_storage/',

    ],


];
