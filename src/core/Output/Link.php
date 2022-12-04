<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - Link Builder    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Auth\Auth;


class Link extends Widget
{

    public $Url;

    public $Text;

    public $Icon;

    public $HtmlIcon;

    public $Roles;

    public $Permissions;

    public $Target;

    public $CssClass;

    public $TextIsHidden = false;

    protected $_DefaultIcon;

    protected $_DefaultText;

    protected $_DefaultCssClass;


    public function Render($flush_output = true)
    {

        if ( Auth::Check( $this->Roles, $this->Permissions ) ) {

            if ( ! $this->_View ) $this->UseView('link');

            if ( ! empty($this->Target) ) $this->Target = 'target="' . $this->Target . '"';

            $this->CssClass = $this->DefaultCssClass() . ' ' . $this->CssClass;

            return parent::Render($flush_output);

        }

        return '';

    }

    /**
     * @param null $widget
     * @return static
     */
    public static function Load($widget = null)
    {
        if ( empty($widget) ) $widget = 'link';

        return parent::Load($widget);
    }

    /**
     * @return Link
     */

    public function HideText()
    {
        $this->TextIsHidden = true;

        return $this;
    }

    public function ShowText()
    {
        $this->TextIsHidden = false;

        return $this;
    }

    public function NoIcon()
    {
        $this->Icon = null;

        return $this;
    }

    /**
     * @return Link
     */

    public function DefaultIcon()
    {
        $this->Icon = $this->_DefaultIcon ?: THEME . "images/link.png";

        return $this;
    }

    public function setDefaultIcon($icon)
    {
        $this->_DefaultIcon = $icon;

        $this->DefaultIcon();

        return $this;
    }

    public function DefaultText()
    {
        $this->Text = $this->_DefaultText ?: VIEW;

        return $this;
    }

    public function setDefaultText($text)
    {
        $this->_DefaultText = $text;

        $this->DefaultText();

        return $this;
    }

    public function DefaultCssClass()
    {
        return $this->_DefaultCssClass;
    }

    public function setDefaultCssClass($css_class)
    {
        $this->_DefaultCssClass = $css_class;

        return $this;
    }

    public function setUrl($url)
    {
        $this->Url = $url;

        return $this;
    }

    public function setCssClass($css_class)
    {
        $this->CssClass = $css_class;

        return $this;
    }


    public function setText($Text)
    {
        $this->Text = $Text;

        return $this;
    }


    public function setIcon($Icon)
    {
        $this->Icon = $Icon;

        return $this;
    }

    public function setHtmlIcon($html_icon)
    {
        $this->HtmlIcon = $html_icon;

        $this->NoIcon();

        return $this;
    }

    /**
     * @param $Roles
     * @return static
     */
    public function setRoles($Roles)
    {
        $this->Roles = $Roles;

        return $this;
    }


    public function setPermissions($Permissions)
    {
        $this->Permissions = $Permissions;

        return $this;
    }


    public function setTarget($Target)
    {
        $this->Target = $Target;

        return $this;
    }




}