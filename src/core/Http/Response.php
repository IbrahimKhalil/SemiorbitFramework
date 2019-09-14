<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - RESPONSE                                                       semiorbit.com
 *------------------------------------------------------------------------------------------------
 */


namespace Semiorbit\Http;


use Semiorbit\Output\ViewBase;

class Response extends ResponseHeaders
{

    protected $_Content;

    protected $_View;


    public function Json($data, $options = JSON_UNESCAPED_UNICODE)
    {

        $this->setContentType('application/json');

        $this->_Content = json_encode($data, $options);

        return $this;

    }

    public function UseView(ViewBase $view)
    {

        $this->setContentType('text/html');

        $this->_View = $view;

        return $this;

    }

    /**
     * @return ViewBase
     */

    public function ActiveView()
    {
        return $this->_View;
    }



    public function setContent($data)
    {
        $this->_Content = $data;

        return $this;
    }

    public function Content()
    {

        if ($this->_View)

            return $this->ActiveView()->Render(false);

        else return $this->_Content;

    }

    /**
     * @param bool $flush_output
     * @return string
     */

    public function Send($flush_output = true)
    {

        $this->SendHeaders();

        if ($flush_output) echo $this->Content();

        return $this->_Content;

    }

    public function __toString()
    {
        return strval( $this->Send(false) );
    }

}