<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT                       				 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


use Semiorbit\Auth\Auth;
use Semiorbit\Base\Application;
use Semiorbit\Data\DataSet;
use Semiorbit\Form\Form;
use Semiorbit\Output\Render;
use Semiorbit\Output\View;
use Semiorbit\Support\Str;
use Semiorbit\Translation\Lang;

class ScaffoldBase extends ScaffoldBaseProvider
{

    private ?Action $_ActiveAction = null;
    
    
    public function Index()
    {

        Auth::Allow( $this->ActiveAction()['allow'] );

        $this->ActiveController()->TableView();

    }

    public function Edit()
    {

        if ( ! $this->ActiveController()->DataSet instanceof DataSet )  Application::Abort(404);


        Auth::Allow( $this->ResetActiveAction()['allow'] );

        $this->ActiveController()->DataSet->Read($this->ActiveController()->Request->ID);

        if ($this->ActiveController()->DataSet->IsNew()) {

            if ( $this->ActiveController()->DataSet->Policy()->DeniesCreate() ) Application::Abort(401);

        } else {

            if ( $this->ActiveController()->DataSet->Policy()->DeniesUpdate() && $this->ActiveController()->DataSet->Policy()->DeniesRead() ) Application::Abort(401);

            else if ( $this->ActiveController()->DataSet->Policy()->DeniesUpdate() && $this->ActiveController()->DataSet->Policy()->AllowsRead() )

                $this->_FormOptions[Form::READONLY] = true;

        }


        if ($this->ActiveController()->DataSet->IsNew()) {

            $ttl =  $this->ActiveAction()['title'] ?: Lang::Trans("{$this->ActiveController()->PackagePrefix}{$this->ActiveController()->ControllerName}.__create");

        } else {

            $ttl = $this->ActiveAction()['title'] ?: Lang::Trans("{$this->ActiveController()->PackagePrefix}{$this->ActiveController()->ControllerName}.__edit");

        }


        $content[] = Render::OpenBox($ttl, $this->ActiveAction()['box'],

            isset($this->ActiveAction()['box_id']) ? $this->ActiveAction()['box_id'] : "", false)
            
                ->WithParams($this->ActiveAction()->Params());


        $content[] = $this->ActiveController()->DataSet->RenderForm(false, $this->FormOptions());


        $content[] = Render::CloseBox($this->ActiveAction()['box'], false)

            ->WithParams($this->ActiveAction()->Params());
        

        $content[] = $this->DisplayCP(false, $this->ActiveController()->DataSet->IsNew() ? "" : $this->ActiveController()->DataSet->ID->Value);

        $content = implode(' ', $content);

        $this->ActiveController()->View->setPageTitle($ttl)->setContent($content)->Render();

    }


    public function Delete()
    {

        if ( ! $this->ActiveController()->DataSet instanceof DataSet )  Application::Abort(404);


        Auth::Allow( $this->ResetActiveAction()['allow'] );

        $id = $this->ActiveController()->DataSet->ID->FilterValue($this->ActiveController()->Request->ID);

        $this->ActiveController()->DataSet->Read($id);



        if ($this->ActiveController()->DataSet->IsNew()) { Url::GotoHomePage('?msg=DELETE_ERR_NO_ITEM'); return; }

        $ttl = $this->ActiveAction()['title'] ?: Lang::Trans($this->ActiveController()->PackagePrefix . Str::ParamCase($this->ActiveController()->ControllerName) . ".__delete");

        if ( $this->ActiveController()->DataSet->Policy()->DeniesDelete() ) Application::Abort(401);

        $content[] = Render::OpenBox($ttl, $this->ActiveAction()['box'],

            isset($this->ActiveAction()['box_id']) ? $this->ActiveAction()['box_id'] : "", false)
        
                ->WithParams($this->ActiveAction()->Params());

        $content[] = $this->DisplayDelMsg($this->ActiveController()->DataSet->ID['value'], false);

        $content[] = Render::CloseBox($this->ActiveAction()['box'], false)

            ->WithParams($this->ActiveAction()->Params());

        $content[] = $this->DisplayCP(false, $this->ActiveController()->DataSet->IsNew() ? "" : $this->ActiveController()->DataSet->ID['value']);

        $content = implode(' ', $content);

        $this->ActiveController()->View->setPageTitle($ttl)->setContent($content)->Render();


    }

    public function Show()
    {

        if ( ! $this->ActiveController()->DataSet instanceof DataSet )  Application::Abort(404);


        Auth::Allow( $this->ResetActiveAction()['allow'] );

        //$this->ListView();
        $id = filter_var($this->ActiveController()->Request->ID);

        //$this->DataSet->Read($this->Request->ID);

        if ( $this->ActiveController()->DataSet->Policy()->DeniesRead() ) Application::Abort(401);

        $this->ActiveController()

            ->View

            ->With('id', $id)

            ->WithParams($this->ActiveAction()->Params())

            ->Render();

    }

    public function ListView()
    {

        /*
        IS_SECURE($this->Request->Action['allow']);

        $this->DataSet->Read($this->Request->ID);

        $this->onAction( $this->Request->Action['method'] );

        $pms['grps'] = $this->DataSet->Groups();

        $ttl = constant("View_" . $this->ControllerName);

        $content[] = Render::OpenBox($ttl, "lvpanel", $this->Request->Action['box_id'], false);

        $content[] = Render::View('default.lv', $pms, false);

        $content[] = Render::CloseBox("lvpanel", false);

        $content[] = $this->DisplayCP(false, $this->DataSet->ID['value']);

        $this->View->setPageTitle($this->DataSet->Title['value'])->setContent($content);

        */

    }

    public function TableView()
    {

        if ( ! $this->ActiveController()->DataSet instanceof DataSet )  Application::Abort(404);


        Auth::Allow( $this->ResetActiveAction()['allow'] );

        $ttl = $this->ActiveAction()['title'] ?: $this->ActiveController()->ControllerTitle;

        $content[] = Render::OpenBox($ttl, $this->ActiveAction()['box'],

            isset($this->ActiveAction()['box_id']) ? $this->ActiveAction()['box_id'] : "", false)

                ->WithParams($this->ActiveAction()->Params());

        /*
                 * TODO: TABLE ORDER AND SEARCH TOOLS
                 * TODO: TABLE ROW SUB VIEW



                $myTable->OrderByFields = $this->Options['orderby_flds'];

                $myTable->SubView = $this->Options['subview'];


                if ($_REQUEST['orderby']) {

                    $this->Options['order'] = " order by " . base64_decode($_REQUEST['orderby']);

                }

                if (isset($_REQUEST['txtsrch'])) {

                    $fltr = false;

                    foreach ($this->Options['filterby_flds'] as $fld) {

                        if (isset($_REQUEST['tv_chk_' . $fld]))
                            $fltr[] = " {$fld} like '%{$_REQUEST['txtsrch']}%' ";

                    }

                    if (is_array($fltr))
                        $this->Options['filter'] = " where " . implode(" or ", $fltr);

                }

                */


        $content[] = $this->ActiveController()->DataSet->ActiveTableView()->Render(false);

        $content[] = Render::CloseBox($this->ActiveAction()['box'], false)

                            ->WithParams($this->ActiveAction()->Params());

        $content[] = $this->DisplayCP(false);

        $content = implode(' ', $content);

        $this->ActiveController()->View->setPageTitle($ttl)->setContent($content)->Render();

    }


    public function DisplayDelMsg($id, $flush_output = true)
    {

        if ( ! isset($id) ) return null;

        ob_start();

        if (Form::IsSubmit('del-form')) {

            if ( Form::Input('del-item-id', 'del-form') !== $id  ) { Url::GotoHomePage('?msg=DELETE_ERR_INVALID'); exit; }

            $res = $this->ActiveController()->DataSet->RemoveRow();

            $this->ActiveController()->DataSet->onUserRemovedRow($res);

        } else {

            $delete_action_url = Controller::DeleteUrl($id, get_class($this->ActiveController())) . Url::QueryString();

            $no_action_url = (isset ($_REQUEST['back-to'])) ? base64_decode($_REQUEST['back-to']) : $this->ActiveController()->ControllerPath . Url::QueryString();

            $pms = array(
                'delete_action_url' => $delete_action_url,
                'item_title' => $this->ActiveController()->DataSet->Title->Value,
                'item_id' => $this->ActiveController()->DataSet->ID->Value,
                'no_action_url' => $no_action_url
            );

            Render::View('default.delete', $pms);

        }


        $buffer = ob_get_contents();

        if ($flush_output) ob_flush();

        @ob_end_clean();
        return $buffer;
    }

    public function Export()
    {

        /**
         *TODO:: EXPORT MODEL
         *
         *
        if ( ! $this->DataSet instanceof DataSet )  abort(404);



        IS_SECURE($this->Request->Action['allow']);

        //$p_arr = $this->Request->ParsePath();

        if (!isset ($_REQUEST['ext'])) {

        $pms['action'] = $this->ControllerPath . "Export";

        Render::View('default.export', $pms);

        } else {

        if ($_REQUEST['ext'] == 'iso-8859-1') {

        $this->DataSet->Export("", "", "\n", ";", "iso-8859-1", false);

        }

        }
         *
         */

    }

    public function DisplayCP($flush_output = true, $id = '', $extra_pms = '', $pms = array())
    {

        if (!$this->ActiveAction()['cp']) return '';
        
        if (!$pms) $pms = $this->ActiveAction()->Params();

        $pms['extra_pms'] = $extra_pms ?: Url::Params();

        $pms['id'] = $id;
        
        $pms['controller'] = $this->ActiveController();
        

        $pms['CTR_PATH'] = $this->ActiveController()->ControllerPath;

        $pms['CTR_NAME'] = $this->ActiveController()->PackagePrefix . Str::ParamCase($this->ActiveController()->ControllerName);

        $pms['CTR_TITLE'] = $this->ActiveController()->ControllerTitle;

        $pms['CTR_ACTION'] = $this->ActiveAction()->Alias;


        if ( ! empty($id) && isset( $this->ActiveController()->DataSet->Title->Value ) ) {

            $pms['DS_TITLE'] = $this->ActiveController()->DataSet->Title->Value;

        }

        $view_path = View::FindPath(

            $view_name = ($this->ActiveController()->PackagePrefix . Str::ParamCase( $this->ActiveController()->ControllerName ) . '.cp') );


        $cp_output = Render::View( ($view_path ? $view_name : 'default.cp'), $pms, $flush_output );

        return $cp_output;

    }
    
    
    private function ActiveAction() : Action
    {
        return $this->_ActiveAction ?:
            
            $this->_ActiveAction = $this->ActiveController()->Request->Action;
    }

    private function ResetActiveAction() : Action
    {
        return $this->_ActiveAction = $this->ActiveController()->Request->Action;
    }


}