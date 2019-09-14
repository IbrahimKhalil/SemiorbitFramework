<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - TABLE VIEW HELPER					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Output;


use Semiorbit\Data\DataSet;
use Semiorbit\Field\Field;
use Semiorbit\Http\Controller;




/**
 *
 * Class TableView
 * @package Semiorbit\Output
 */

class TableView
{

    protected $_Columns = array();

    protected $_DataSet = null;

    protected $_Controller = null;

    protected $_SkippedRows = 0;


    protected $_CssClass = "table-view";


    protected $_ShowAllColumns = false;
	
	protected $_ShowEditLink = true;

    protected $_ShowEditLinkForReadOnlyRows = false;

    protected $_ShowDeleteLink = true;

	
	protected $_CountPerPage = 20;

    protected $_Roles = SUPER_ADMIN;

    protected $_Permissions;



    /**
     * @param DataSet $data_set
     * @return $this
     */

    public function UseDataSet(DataSet $data_set)
    {
        $this->_DataSet = $data_set;

        return $this;
    }

    /**
     * @return DataSet|null
     */

    public function ActiveDataSet()
    {
        return $this->_DataSet;
    }


    /**
     * @param Controller $controller
     * @return $this
     */

    public function UseController(Controller $controller)
    {
        $this->_Controller = $controller;

        return $this;
    }

    /**
     * @return Controller|null
     */

    public function ActiveController()
    {
        return $this->_Controller;
    }

    /**
     * Set or get column by id
     *
     * @param string $id Column ID
     * @return TableViewCol
     */

    public function Column($id)
    {
        return isset( $this->_Columns[ $id ] ) ? $this->_Columns[ $id ]

            : false;
    }

    /**
     * @param TableViewCol|Field $col
     * @return $this
     */
    public function AddColumn($col)
    {

        if ( $col instanceof TableViewCol ) $this->_Columns[] = $col;

        elseif ( $col instanceof Field ) $this->_Columns[] = $col->ActiveTableViewCol();

        return $this;

    }

    /**
     * TableView columns array
     *
     * @return array
     */

    public function Columns()
    {

        if ( empty($this->_Columns) && ! empty($this->_DataSet) ) {

            if ( $this->IsShowAllColumns() ) {

                foreach ( $this->ActiveDataSet()->Fields() as $field ) {

                    /**@var \Semiorbit\Field\Field $field */

                    if ( $field->ActiveTableViewCol()->IsVisible() !== false )

                        $this->AddColumn( $field->ActiveTableViewCol() );

                }

            } else {

                foreach ( $this->ActiveDataSet()->Fields() as $field ) {

                    /**@var \Semiorbit\Field\Field $field */

                    if ( $field->ActiveTableViewCol()->IsVisible() )

                        $this->AddColumn( $field->ActiveTableViewCol() );

                }

                if (count($this->_Columns) == 0) {

                    if ($this->ActiveDataSet()->Title instanceof Field &&

                        $this->ActiveDataSet()->Title->ActiveTableViewCol()->IsVisible() !== false
                    )

                        $this->AddColumn($this->ActiveDataSet()->Title->ActiveTableViewCol());

                }

            }

        }

        return $this->_Columns;

    }

    /**
     * Remove column from TableView columns array
     *
     * @param string $id
     * @return $this
     */

    public function RemoveColumn($id)
    {
        if ( isset( $this->_Columns[ $id ] ) ) unset( $this->_Columns[ $id ] );

        return $this;
    }

    public function Render($flush_output = true)
    {

        $pms = array();

        $pms['myTableView'] = $this;

        $html_output = Render::View('default.table', $pms, false);

        return $html_output->Render( $flush_output );

    }


    public function __toString()
    {
        return $this->Render(false);
    }

    public function AddSkippedRow($count = 1)
    {
        $this->_SkippedRows += $count;
    }

    public function SkippedRows()
    {
        return $this->_SkippedRows;
    }

    public function ShowEditLink($value = true)
    {
        $this->_ShowEditLink = $value;

        return $this;
    }

    public function ShowEditLinkForReadOnlyRows($value = true)
    {
        $this->_ShowEditLinkForReadOnlyRows = $value;

        return $this;
    }

    public function ShowDeleteLink($value = true)
    {
        $this->_ShowDeleteLink = $value;

        return $this;
    }

    public function HideEditLink($value = true)
    {
        $this->_ShowEditLink = !$value;

        return $this;
    }

    public function HideDeleteLink($value = true)
    {
        $this->_ShowDeleteLink = !$value;

        return $this;
    }

    public function IsShowEditLink()
    {
        return $this->_ShowEditLink;
    }

    public function IsShowDeleteLink()
    {
        return $this->_ShowDeleteLink;
    }

    public function IsShowEditLinkForReadOnlyRows()
    {
        return $this->_ShowEditLinkForReadOnlyRows;
    }

    public function setCssClass($value = "table-view")
    {
        $this->_CssClass = $value;

        return $this;
    }

    public function CssClass()
    {
        return $this->_CssClass;
    }

    public function setCountPerPage($value)
    {
        $this->_CountPerPage = $value;

        return $this;
    }

    public function CountPerPage()
    {
        return $this->_CountPerPage;
    }

    public function setRoles($value)
    {
        $this->_Roles = $value;

        return $this;
    }

    public function Roles()
    {
        return $this->_Roles;
    }

    public function setPermissions($value)
    {
        $this->_Permissions = $value;

        return $this;
    }

    public function Permissions()
    {
        return $this->_Permissions;
    }

    public function ShowAllColumns($value = true)
    {
        $this->_ShowAllColumns = $value;

        return $this;
    }

    public function IsShowAllColumns()
    {
        return $this->_ShowAllColumns;
    }



}
