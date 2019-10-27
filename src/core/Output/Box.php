<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - BLOCK BUILDER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Component\Finder;
use Semiorbit\Config\Config;


class Box extends ViewBase
{

    public $Open;

    public $Close;

    public $Title;

    public $ID;


    /**
     * Widget file path or FALSE if not found
     *
     * @param string $box Box name
     * @return bool|string
     */

    public static function FindPath($box)
    {

        $view_path = Render::CacheViewPath( Config::ViewsDir() . '@Box_' . $box ) ?: call_user_func ( function () use ( $box ) {

            $box_ext = '.' . trim( Config::BoxExt(), '.' );

            $box_path = Finder::LookFor( $box . $box_ext, Finder::Views, true );

            Render::CacheViewPath( Config::ViewsDir() . '@Box_' . $box );

            return $box_path;

        });

        return $view_path ? $view_path['path'] : false;

    }

    /**
     * Start block
     *
     * @param string $box Block name
     * @return $this
     */

    public static function Open($box = 'panel')
    {

        $myBox = new static($box);

        $myBox->setOpen();

        return $myBox;
    }

    /**
     * End block
     *
     * @param string $box Block name
     * @return $this
     */

    public static function Close($box = 'panel')
    {
        $myBox = new static($box);

        $myBox->setClose();

        return $myBox;
    }


    /**
     * Set block title text or html
     *
     * @param string $title Block head text
     * @return $this
     */

    public function setTitle($title)
    {
        $this->Title = $title;

        $this->With('box_title', $title);

        return $this;
    }

    /**
     * Set block html id tag
     *
     * @param string $id Block html id
     * @return $this
     */

    public function setID($id)
    {
        $this->ID = $id;

        $this->With('box_id', $id);

        return $this;
    }


    /**
     * Prepare to output opening block
     *
     * @return $this
     */

    public function setOpen()
    {
        $this->Open = true;

        $this->Close = false;

        return $this;
    }

    /**
     * Prepare to output closing block
     *
     * @return $this
     */

    public function setClose()
    {
        $this->Close = true;

        $this->Open = false;

        return true;
    }


}