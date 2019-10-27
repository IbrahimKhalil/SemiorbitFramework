<?php 
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT - PAGINATION TOOL 					 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Output;



use Semiorbit\Config\Config;
use Semiorbit\Db\Table;
use Semiorbit\Http\Url;


class Pagination
{

    protected $_Table;

    protected $_PaginationWidget = "pagination";

    protected $_AddParams = array();

    protected $_ExcludeParams = array();

    protected $_PageParam;

    protected $_UrlPath = "";


    public function __construct(Table $table)
    {
        $this->UseTable($table);
    }

    public function RequestedPage()
    {
        $page = isset($_REQUEST[$this->PageParam()]) ? intval($_REQUEST[$this->PageParam()]) : 1;

        return ($page > 0) ? $page : 1;
    }

    public function UseTable(Table $table)
    {
        $this->_Table = $table;

        return $this;
    }

    /**
     * @return Table
     */

    public function Table()
    {
        return $this->_Table;
    }

    public function UsePaginationWidget($pagination_widget)
    {
        $this->_PaginationWidget = $pagination_widget;

        return $this;
    }

    public function PaginationWidget()
    {

        if (is_empty($this->_PaginationWidget))

            $this->_PaginationWidget = (is_empty(Config::PaginationWidget())) ? "Pagination" : Config::PaginationWidget();

        return $this->_PaginationWidget;

    }

    public function CurrentPage()
    {
        return $this->Table()->CurrentPage();
    }

    public function NextPage()
    {
        return $this->HasMorePages() ? $this->CurrentPage() + 1 : false;
    }

    public function PreviousPage()
    {
        $prev_page = $this->CurrentPage() - 1;

        return $prev_page > 0 ? $prev_page : false;
    }

    public function FirstPage()
    {
        return 1;
    }

    public function LastPage()
    {
        return $this->PageCount();
    }

    public function HasMorePages()
    {
        return ($this->CurrentPage() <= $this->PageCount());
    }

    public function PageCount()
    {
        return $this->Table()->PageCount();
    }

    public function PageParam()
    {
        if (empty($this->_PageParam)) $this->_PageParam = empty(Config::PageParam()) ? "page" : Config::PageParam();

        return $this->_PageParam;
    }


    public function PageUrl($page)
    {

        $page = intval($page);

        if ($page < 1) return false;

        //GET URL PARAMETERS EXCLUDING _ExcludeParams + _PageParam

        array_push($this->_ExcludeParams, $this->PageParam());

        $main_params = trim( Url::Params( $this->_ExcludeParams ) );

        $extra_params = '';

        if (!empty($main_params)) $main_params = trim($main_params, "&") . "&";

        if (!empty($this->_AddParams)) $extra_params = "&" . implode("&", $this->_AddParams);

        return $this->_UrlPath . "?" . $main_params . $this->PageParam() . "=" . $page . $extra_params;

    }

    public function CurrentPageUrl()
    {
        return $this->PageUrl($this->CurrentPage());
    }

    public function FirstPageUrl()
    {
        return $this->PageUrl($this->FirstPage());
    }

    public function LastPageUrl()
    {
        return $this->PageUrl($this->LastPage());
    }

    public function PreviousPageUrl()
    {
        return $this->PageUrl($this->PreviousPage());
    }

    public function NextPageUrl()
    {
        return $this->PageUrl($this->NextPage());
    }

    public function AddParams(array $params)
    {
        $this->_AddParams = $params;

        return $this;
    }

    public function ExcludeParams(array $params)
    {
        $this->_ExcludeParams = $params;

        return $this;
    }

    public function UseUrlPath($url_path)
    {
        $this->_UrlPath = $url_path;

        return $this;
    }

    public function UrlPath()
    {
        return $this->_UrlPath;
    }


    public function Total()
    {
        return $this->Table()->Total();
    }

    public function RowsPerPage()
    {
        return $this->Table()->RowsPerPage();
    }

    public function CurrentPageRowCount()
    {
        return $this->Table()->RowCount();
    }

    public function Render($flush_output = true, $class = "", $data_attr = "")
    {

        $links = array();

        if ( $this->PageCount() > 1 ) {

            if ($this->CurrentPage() > 1) {

                $links['first'] = $this->FirstPageUrl();

                $links['prev'] = $this->PreviousPageUrl();

            }

            //===========================================================================================================1

            $num_page_1 = $this->CurrentPage() - 2;

            if ($num_page_1 > 2) {

                $links[$num_page_1] = $this->PageUrl($num_page_1);

            }

            //===========================================================================================================

            //===========================================================================================================2

            $num_page_2 = $this->CurrentPage() - 1;

            if ($num_page_2 > 1) {

                $links[$num_page_2] = $this->PageUrl($num_page_2);

            }

            //=================================================================================================== CURRENT

            $links['current'] = $this->CurrentPage();

            //===========================================================================================================

            //===========================================================================================================3

            $num_page_3 = $this->CurrentPage() + 1;

            if ($num_page_3 < $this->PageCount()) {

                $links[$num_page_3] = $this->PageUrl($num_page_3);

            }

            //===========================================================================================================

            //===========================================================================================================4

            $num_page_4 = $this->CurrentPage() + 2;

            if ($num_page_4 < $this->PageCount()) {

                $links[$num_page_4] = $this->PageUrl($num_page_4);

            }

            //===========================================================================================================


            if ($this->CurrentPage() < $this->PageCount()) {

                $links['next'] = $this->NextPageUrl();

                $links['last'] = $this->LastPageUrl();

            }

            //===========================================================================================================

        } else {

            $links['current'] = 1;

        }

        $pms['links'] = $links;

        $pms['cur_page'] = $this->CurrentPage();

        $pms['rows_count'] = $this->Total();

        $pms['pages_count'] = $this->PageCount();

        $pms['class'] = $class;

        $pms['data_attr'] = $data_attr;

        // LOAD PAGINATION CONTROL

        $buffer = Render::Widget($this->PaginationWidget(), $pms, $flush_output);

        return $buffer;

    }

    public function __toString()
    {
        return $this->Render(false)->Render(false);
    }

}