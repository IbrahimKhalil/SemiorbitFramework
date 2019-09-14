<?php
/**
 * Created by PhpStorm.
 * User: Ibrahim Khalil
 * Date: 15/10/2017
 * Time: 04:29 م
 */

namespace Semiorbit\Http;


class RestfulScaffold extends ScaffoldingProvider
{

    protected $_Disabled = array("Index"=>false, "Edit"=>true, "Delete"=>true,

        "Show"=>false, "ListView"=>true, "TableView"=>true);


    public function Index()
    {
        // TODO: Implement Index() method.
    }

    public function Show()
    {
        // TODO: Implement Show() method.
    }

    public function Edit()
    {
        // TODO: Implement Edit() method.
    }

    public function Delete()
    {
        // TODO: Implement Delete() method.
    }

    public function ListView()
    {
        return null;
    }

    public function TableView()
    {
        return null;
    }

    public function FormOptions()
    {
        return null;
    }

    public function setFormOptions(array $form_options)
    {
        return $this;
    }
}