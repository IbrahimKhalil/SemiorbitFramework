<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - View Builder    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Component\Finder;
use Semiorbit\Config\Config;
use Semiorbit\Support\AltaArray;


class ViewBase extends AltaArray
{

    protected $_Path;

    protected $_View;

    protected $_ViewRoot;

    protected $_ViewSub;

    protected $_ViewExt;

    protected $_Content;

    protected $_RenderedContent = true;



    /**
     * @param $view string|array
     * View name consists of two parts, root and sub view, separated by dot.<p>
     * <p>View argument value could be an <b>array</b> to select the first existing view in the applied list<p><p>
     *
     * Examples: <p><p>'<b>posts.post_card</b>' or '<b>users.profile</b>' <p>
     * In views structure 'users' folder name or root name
     * and 'profile' sub name so path
     * to this view could be one of these two paths:
     * <p>Either '<b>views/users/profile.phtml</b>' or file without sub folder as follows '<b>views/users.profile.phtml</b>' <p><p>
     * Index view ('<b>users</b>' index view for example) could be located in '<b>views/users.phtml</b>' or '<b>views/users/index.phtml</b>'<p>
     *
     */

    public function __construct($view = null)
    {

        if ( $view ) $this->UseView( $view );

        parent::__construct(array());

    }

    /**
     * Load view by view name<p><p>
     *
     *
     * @param $view string|array Examples: <p><p>'<b>posts.post_card</b>' or '<b>users.profile</b>' <p>
     * In views structure 'users' folder name or root name
     * and 'profile' sub name so path
     * to this view could be one of these two paths:
     * <p>Either '<b>views/users/profile.phtml</b>' or file without sub folder as follows '<b>views/users.profile.phtml</b>' <p><p>
     * Index view ('<b>users</b>' index view for example) could be located in '<b>views/users.phtml</b>' or '<b>views/users/index.phtml</b>'
     * <p>
     * <p>View argument value could be an <b>array</b> to select the first existing view in the applied list
     *
     * @return $this
     */

    public function UseView($view)
    {

        if ( is_array( $view ) ) $view = static::Select( $view );

        $this->_View = $view;

        $view_name_parts = static::ViewNameParts( $view );

        if ( $view_name_parts ) {

            $this->_ViewRoot = $view_name_parts[0];

            $this->_ViewSub = $view_name_parts[1];

        }

        return $this;

    }


    /**
     * View name
     *
     * @return string View name
     */

    public function ViewName()
    {
        return $this->_View;
    }


    /**
     * Render view
     *
     * @param bool $flush_output
     * @return string returns view output
     */

    public function Render($flush_output = true)
    {


        /* Extract vars and send them to view */

        extract( $this->ToArray() );

        extract( array_change_key_case( $this->ToArray(), CASE_LOWER) );

        ob_start();

        // Render view content from file if no other content is set

        if ( empty( $this->_Content ) || $this->_RenderedContent ) {

            $path = $this->Path();

            if ($path)

                /** @noinspection PhpIncludeInspection */
                include "{$path}";

            $this->_RenderedContent = true;


        } else {

            echo $this->Content();

        }

        $output = ob_get_contents();

        // Store view rendered output in Content variable

        if ( $this->_RenderedContent ) $this->_Content = $output;

        @ob_end_clean();

        if ( $flush_output ) echo $output;

        return $output;

    }

    /**
     * Extract view name into root and sub view by dot.<p><p>
     * Examples: <p><p>'<b>posts.post_card</b>' or '<b>users.profile</b>' <p>
     * In views structure 'users' folder name or root name
     * and 'profile' sub name so path
     * to this view could be one of these two paths:
     * <p>Either '<b>views/users/profile.phtml</b>' or file without sub folder as follows '<b>views/users.profile.phtml</b>' <p><p>
     * Index view ('<b>users</b>' index view for example) could be located in '<b>views/users.phtml</b>' or '<b>views/users/index.phtml</b>'
     *
     *
     * @param $view
     * @return array|false
     */

    public static function ViewNameParts($view)
    {

        $view_parts = Render::Clipboard( 'ViewParts@' . $view ) ?:

            Render::Clipboard( 'ViewParts@' . $view, call_user_func( function() use ($view) {

                if ( is_empty( $view ) ) return false;

                $parts = explode( '.', $view );

                if ( is_empty( $parts[0] ) ) return false;

                if ( ! isset( $parts[1] ) || is_empty( $parts[1] ) ) $parts[1] = null;

                return $parts;

            } ) );

        return $view_parts;

    }

    /**
     * View file path or FALSE if not found
     *
     * @param $view string eg. 'users.profile'
     * @return bool|string
     */

    public static function FindPath( $view )
    {

        $view_path = Render::CacheViewPath( Config::ViewsDir() . '@View_' . $view ) ?: call_user_func( function () use ( $view ) {

            $view_parts = static::ViewNameParts($view);

            if ( ! $view_parts ) return false;


            $view_root = $view_parts[0];

            $view_sub = $view_parts[1];

            $view_ext = '.' . trim( Config::ViewsExt(), '.' );


            if ( empty ( $view_sub ) ) {

                $view_files = array($view_root . $view_ext, $view_root . "/" . $view_root . $view_ext);

            } else if ( strtolower( $view_sub ) == strtolower( Config::IndexAction() ) ) {

                $view_files = array($view_root . $view_ext, $view_root . "/" . $view_root . $view_ext, $view_root . "/" . $view_sub . $view_ext, $view_root . "." . $view_sub . $view_ext);

            } else {

                $view_files = array($view_root . "/" . $view_sub . $view_ext, $view_root . "." . $view_sub . $view_ext );

            }

            $view_path = Finder::LookFor($view_files, Finder::Views, true);

            Render::CacheViewPath( Config::ViewsDir() . '@View_' . $view . $view_ext, $view_path );

            return $view_path;

        });



        return $view_path ? $view_path['path'] : false;

    }


    /**
     * View file path
     *
     * @return string
     */

    public function Path()
    {
        return $this->_Path = static::FindPath( $this->_View ) ?: null;
    }

    /**
     * Set parameter as view property
     *
     * @param $key
     * @param $value
     * @return $this
     */

    public function With($key, $value)
    {
        $this->$key = $value;

        return $this;
    }

    /**
     * Add a list of parameters to view input
     *
     * @param array $pms List of parameters as an associative array with key=>value pairs
     * @return $this
     */

    public function WithParams(array $pms = array())
    {
        if ( is_array( $pms ) ) $this->Merge( $pms );

        return $this;
    }


    /**
     * Render view and return output
     *
     * @return string
     */

    public function __toString()
    {
        return $this->Render(false);
    }


    /**
     * Set page body content
     *
     * @param $content
     * @return $this
     */

    public function setContent($content)
    {

        if ( is_array($content) ) $content = implode('', $content);

        $this->_Content = $content;

        $this->_RenderedContent = false;

        return $this;

    }

    /**
     * Get page body content
     *
     * @return string
     */

    public function Content()
    {
        return $this->_Content;
    }


    /**
     * Create new instance and load from file
     *
     * @param string|array $view View name to load
     *                      <p>View argument value could be an <b>array</b> to select the first existing view
     *                      in the applied list<p><p>
     * @return static
     */

    public static function Load($view)
    {
        $myView = new static($view);

        return $myView;
    }

    /**
     * Select the first existing view in a list of expected views
     *
     * @param array $views
     * @return string
     */

    public static function Select(array $views)
    {

        $selected_view = false;

        foreach( $views as $view ) {

            $path = static::FindPath( $view );

            if ( $path )  { $selected_view = $view; break; }

        }

        return $selected_view ?: '';

    }


}