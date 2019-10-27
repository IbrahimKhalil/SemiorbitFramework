<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - View Builder    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Base\AppManager;
use Semiorbit\Config\Config;
use Semiorbit\Http\Controller;
use Semiorbit\Http\Request;
use Semiorbit\Support\Str;


class View extends ViewBase
{

    protected $_Layout;

    protected $_LayoutPath;

    protected $_PageTitle;

    protected $_PageTitleSeparator = ' - ';

    protected $_Header;

    protected $_CanonicalUrl;

    protected $_Request;

    protected $_RequestLayoutList;




    /**
     * Get view name from active request controller name and
     * actions array ( or action method name ) according to requested action
     *
     * @return $this
     */

    public function FromRequest()
    {

        $request = $this->ActiveRequest();

        $view_root = Str::ParamCase( Controller::Name( $request->Controller->Class ) );

        $view_sub = ( ! empty( $request->Action['view'] ) ) ? $request->Action['view'] :

                     ( ( isset ( $request->Action['method'] ) ) ? Str::ParamCase( $request->Action['method'] ) : null );


        $view_name = $view_sub ? $view_root . '.' . $view_sub : $view_root;

        $this->UseView( $view_name );

        return $this;

    }


    /**
     * Render view
     *
     * @param bool $flush_output
     * @return string returns view output
     */

    public function Render($flush_output = true)
    {

        // Get view name from active request, if no view was used yet and active request is assigned

        if ( ! $this->ViewName() && $this->ActiveRequest() ) $this->FromRequest();

        // Render view content

        parent::Render( false );


        /* Extract vars and send them to view */

        extract( $this->ToArray() );

        extract( array_change_key_case( $this->ToArray(), CASE_LOWER) );


        ob_start();

        //Render layout if exists

        $path = $this->LayoutPath();

        if ( $path )

            /** @noinspection PhpIncludeInspection */
            include "{$path}";

        else

            //Send content directly to output

            echo $this->Content();


        $output = ob_get_contents();

        @ob_end_clean();

        if ( $flush_output ) echo $output;

        return $output;

    }


    /**
     * Find layout file path
     *
     * @return bool|string
     */

    public function LayoutPath()
    {
        $layout = $this->ActiveLayout();

        if ( empty( $layout ) ) return false;

        return $this->_LayoutPath = static::FindPath( $layout );
    }

    /**
     * Set active request
     *
     * @param Request $request
     * @return $this
     */

    public function UseRequest( Request $request )
    {
        $this->_Request = $request;

        return $this;
    }


    /**
     * Get active request
     *
     * @return Request
     */

    public function ActiveRequest()
    {
        return $this->_Request;
    }


    /**
     * Set html page title
     *
     * @param null $title
     * @param bool $display_project_title
     * @return $this
     */

    public function setPageTitle($title = null, $display_project_title = true)
    {

        if ( $title == null && $this->_PageTitle != null ) return $this->_PageTitle;

        $default_project_title = AppManager::CallMainApp('Title');

        if ( $display_project_title ) {

            $title = str_ireplace($default_project_title . Config::PageTitleSeparator(), "", $title);

            is_empty( $title ) || $title == $default_project_title ? $title = $default_project_title :

                $title = $default_project_title . Config::PageTitleSeparator() . $title;

        }

        $this->_PageTitle = $title;

        return $this;

    }


    /**
     * Get html page title
     *
     * @return string
     */

    public function PageTitle()
    {
        if ( $this->_PageTitle == null ) $this->setPageTitle();

        return $this->_PageTitle;
    }

    /**
     * Set html page title separator between project title and title page
     * eg. "Project Title - Page Title"
     *
     * @param string $separator
     * @return $this
     */

    public function setPageTitleSeparator($separator = ' - ')
    {
        $this->_PageTitleSeparator = $separator ? $separator : ' - ';

        return $this;
    }

    /**
     * Get html page title separator between project title and title page
     * eg. "Project Title - Page Title"
     *
     * @return string
     */

    public function PageTitleSeparator()
    {
        if ( $this->_PageTitleSeparator == null ) $this->PageTitleSeparator();

        return $this->_PageTitleSeparator;
    }

    /**
     * Set page canonical url
     *
     * @param $url
     * @return $this
     */

    public function setCanonicalUrl($url)
    {
        $this->_CanonicalUrl = $url;

        return $this;
    }

    /**
     * Get page canonical url
     *
     * @return string
     */

    public function CanonicalUrl()
    {
        return is_empty( $this->_CanonicalUrl ) ?  Request::Url() : $this->_CanonicalUrl;
    }

    /**
     * Page layout file to use in Render view. View content will be sent to layout and will be included in layout output
     *
     * @param $layout
     * @return $this
     */

    public function UseLayout($layout)
    {
        $this->_Layout = $layout;

        return $this;
    }

    /**
     * No layout will be used in Render. View content will sent directly to output
     *
     * @return $this
     */

    public function NoLayout()
    {
        $this->_Layout = '';

        return $this;
    }

    /**
     * Array of layout names that are valid to pass from $_REQUEST params
     *
     * @param array $layouts Array of layout names
     * @return $this
     */

    public function RequestLayoutWhiteList(array $layouts)
    {
        $this->_RequestLayoutList = $layouts;

        return $this;
    }

    /**
     * Project default layout will be used in Render. Default layout can be set in config
     * or can be set in $_REQUEST array
     *
     * @return $this
     */

    public function UseDefaultLayout()
    {

        if ( $this->_RequestLayoutList ) {

            if ( isset( $_REQUEST[ Config::LayoutParamName() ] )

                && in_array( $_REQUEST[ Config::LayoutParamName() ], $this->_RequestLayoutList ) )

                $this->_Layout = $_REQUEST[ Config::LayoutParamName() ];

        } else {

            $this->_Layout = Config::DefaultLayout();
        }

        return $this;
    }

    /**
     * Layout name or empty string if no layout assigned
     *
     * @return string
     */

    public function ActiveLayout()
    {
        if ( $this->_Layout === null ) $this->UseDefaultLayout();

        return $this->_Layout;
    }



}