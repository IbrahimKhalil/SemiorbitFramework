<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - RENDER CLASS   	    				 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;




use Semiorbit\Support\ClipboardTrait;

class Render
{

    public static $_CacheMethod;


    const CACHE_DISABLED = 0;

    const CACHE_VIEW_PATH = 1;


    use ClipboardTrait {
        Clipboard as public;
    }


    /**
     * Render View
     *
     * @param string|array $view View name
     *        <p>View argument value could be an <b>array</b> to select the first existing view in the applied list<p><p>
     * @param array|null $pms
     * @param bool|true $flush_output
     * @return View Returns view, or view output if flush_output is true
     */

    public static function View($view, array $pms = array(), $flush_output = true)
    {
        $myView = View::Load($view)->WithParams($pms)->NoLayout();

        return $flush_output ? $myView->Render($flush_output) : $myView;
    }

    /**
     * Render Widget
     *
     * @param string|array $widget Widget name
     *                     <p>Widget argument value could be an <b>array</b> to select the first existing view in the applied list<p><p>
     * @param array $pms
     * @param bool|false $flush_output
     * @return Widget Returns widget, or widget output if flush_output is true
     */
    public static function Widget($widget, array $pms = array(), $flush_output = false)
    {
        $myWidget = Widget::Load($widget)->WithParams($pms);

        return $flush_output ? $myWidget->Render($flush_output) : $myWidget;
    }

    /**
     * Start Block
     *
     * @param string|array $box_title
     * @param string $box
     *              <p>Box argument value could be an <b>array</b> to select the first existing view in the applied list<p><p>
     * @param string $box_id
     * @param bool|true $flush_output
     * @param array $pms
     * @return Box  Returns box, or box output if flush_output is true
     */

    public static function OpenBox($box_title = '', $box = 'panel', $box_id = '', $flush_output = true, array $pms = array())
    {
        $myBox = Box::Open($box)->WithParams($pms)->setTitle($box_title)->setID($box_id);

        return $flush_output ? $myBox->Render($flush_output) : $myBox;
    }

    public static function OpenArticle($box_title = '', $box_id = '', $flush_output = true, array $pms = array())
    {
        $myBox = Box::Open('article')->WithParams($pms)->setTitle($box_title)->setID($box_id);

        return $flush_output ? $myBox->Render($flush_output) : $myBox;
    }

    /**
     * End Block
     *
     * @param string|array $box
     *              <p>Box argument value could be an <b>array</b> to select the first existing view in the applied list<p><p>
     * @param bool|true $flush_output
     * @param array $pms
     * @return Box  Returns box, or box output if flush_output is true
     */

    public static function CloseBox($box = 'panel', $flush_output = true, array $pms = array())
    {
        $myBox = Box::Close($box)->WithParams($pms);

        return $flush_output ? $myBox->Render($flush_output) : $myBox;
    }


    public static function CloseArticle($flush_output = true, array $pms = array())
    {
        $myBox = Box::Close('article')->WithParams($pms);

        return $flush_output ? $myBox->Render($flush_output) : $myBox;
    }

    /**
     * @param string $url
     * @param string $text
     * @param array  $roles
     * @return Link
     */

    public static function Link($url, $text = View, $roles = array())
    {
       return Link::Load()->setUrl($url)->setText($text)->setRoles($roles)->DefaultIcon();
    }

    public static function EditLink($url, $roles = SUPER_ADMIN)
    {
        $icon = THEME . "images/edit.png";

        return Link::Load()->setUrl($url)->setRoles($roles)->setDefaultCssClass("semiorbit-edit-link ")

            ->setDefaultIcon($icon)->setDefaultText(Edit)->HideText();
    }


    public static function DeleteLink($url, $roles = SUPER_ADMIN)
    {
        $icon = THEME . "images/delete.png";

        return Link::Load()->setUrl($url)->setRoles($roles)->setDefaultCssClass("semiorbit-delete-link ")

            ->setDefaultIcon($icon)->setDefaultText(Delete)->HideText();
    }

    public static function FileIcon($file_extension)
    {
        return Render::Widget('file-icon')->With('file_extension', $file_extension);
    }


    /**
     * Set cache method
     *
     * @param int $cache_method
     * <ul>
     *      <li><b>0</b> = Render::CACHE_DISABLED</li>
     *      <li><b>1</b> = Render::CACHE_VIEW_PATH</li>
     * </ul>
     */

    public static function CacheConfig($cache_method = Render::CACHE_VIEW_PATH)
    {
        static::$_CacheMethod = $cache_method;
    }

    /**
     * Get cache method
     *
     * @return int
     *
     * <ul>
     *      <li><b>0</b> = Render::CACHE_DISABLED</li>
     *      <li><b>1</b> = Render::CACHE_VIEW_PATH</li>
     * </ul>
     *
     */

    public static function CacheMethod()
    {
        return static::$_CacheMethod ?: Render::CACHE_VIEW_PATH;
    }

    /**
     * Get or set view path in cache
     *
     * @param $key
     * @param null $value
     * @return bool|mixed
     */

    public static function CacheViewPath($key, $value = null)
    {
        return (static::CacheMethod() == Render::CACHE_VIEW_PATH) ? static::Clipboard('path_' . $key, $value) : false;
    }

}