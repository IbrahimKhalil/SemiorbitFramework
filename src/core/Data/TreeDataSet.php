<?php 
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - TREE MODEL     					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Data;



use Semiorbit\Db\DB;
use Semiorbit\Field\Field;
use Semiorbit\Field\Option;
use Semiorbit\Field\Select;

/**
 * Class TreeDataSet
 * @package Semiorbit\Data
 */

class TreeDataSet extends DataSet
{


    protected $_ParentDataSet;

    protected $_ParentID;

    protected $_Level;

    protected $_Position;

    protected $_ChildrenCount;

    private $_prev_ = null;


    /**
     * @param $name
     * @param $option_key
     * @param $option_title
     * @param $position_field
     * @return Select
     */

	public function AssignParentIDField($name, $option_key, $option_title, $position_field)
    {

        $this->_ParentID = Field::Select($name)->setForeignKey($this->TableName(), $option_key, $option_title)

            ->setFKeyOrderBy($position_field)->setOption('0', '---')->setRequired(true)->setInputCssClass('semiorbit-tree-parent-select')

            ->UseOptionTextBuilder(function($key, $opt) use ($option_key) {

                $level = DB::Find("SELECT {$this->LevelField()->Name} FROM {$this->TableName()} WHERE {$option_key} = '{$key}'");

                return str_repeat(' --- ', $level) . $opt;

            });


        return $this->_ParentID;

    }

    /**
     * @param $name
     * @return \Semiorbit\Field\Number
     */

    public function AssignLevelField($name)
    {
        $this->_Level = Field::Number($name)->setRequired(true)->setDefaultValue(0)->NoControl()->HideColumn();

        return $this->_Level;
    }


    /**
     * @param $name
     * @param $option_title
     * @return Select
     */

    public function AssignPositionField($name, $option_title)
    {

        $this->_Position = Field::Select($name)->setForeignKey($this->TableName(), $name, $option_title)

            ->setFKeyOrderBy($name)->setDefaultValue(0)->setOption(Option::set(0, AtTheBottom)->setAtBottom())

            ->setFormat(BeforeFormat)->setSelectedFormat(AtFormat)->setInputCssClass('semiorbit-tree-position-select')

            ->setRequired(true)->HideColumn();

        return $this->_Position;

    }


    /**
     * @param $name
     * @return \Semiorbit\Field\Number
     */

    public function AssignChildrenCountField($name)
    {
        $this->_ChildrenCount = Field::Number($name)->setRequired(true)->setDefaultValue(0)->NoControl();

        return $this->_ChildrenCount;
    }


    /**
     * @return Select
     */

    public function ParentIDField()
    {
        return $this->_ParentID;
    }

    /**
     * @return \Semiorbit\Field\Number;
     */

    public function LevelField()
    {
        return $this->_Level;
    }

    /**
     * @return Select
     */

    public function PositionField()
    {
        return $this->_Position;
    }

    /**
     * @return \Semiorbit\Field\Number
     */

    public function ChildrenCountField()
    {
        return $this->_ChildrenCount;
    }


    public function onInsert($res)
    {

        if ($res != Msg::DBOK) return;

        $this->setParentDataSet();

        $this->setLevel();

        $this->setPosition();

        $this->FixPositionsAfter();

        $this->UpdateNodePos();

        $this->ParentChildren();

    }

    public function onBeforeUpdate()
    {

        $prev_ = DB::Row("SELECT {$this->ParentIDField()->Name} AS parent, {$this->PositionField()->Name} AS pos, {$this->LevelField()->Name} AS lvl FROM {$this->TableName()} WHERE {$this->ID->Name} = '{$this->ID->Value}' ");

        $this->_prev_ = $prev_;

        $this->_prev_['next_node_'] = $this->NextNode($this->_prev_['pos'], $this->_prev_['lvl'] + 1);

        $this->_prev_['me_n_children_'] = $this->_prev_['next_node_'] - $this->_prev_['pos'];

        $this->PositionField()->setReadOnly(true);

        $this->LevelField()->setReadOnly(true);

    }

    public function onUpdate($res)
    {

        if ($res != Msg::DBOK) return ;

        $this->setParentDataSet();

        $this->setLevel();

        $this->setPosition();

        if ( $this->PositionField()->Value != $this->_prev_['pos'] ) {

            $this->MoveNode($this->_prev_['pos'], $this->_prev_['next_node_']);

        }

        $this->ParentChildren();

        if ( $this->ParentIDField()->Value != $this->_prev_['parent'] ) {

            $parent = static::Find($this->_prev_['parent']);

            if ($parent) $this->ParentChildren($parent);

        }

        $this->PositionField()->setReadOnly(false);

        $this->LevelField()->setReadOnly(false);

    }

    public function onRemove($res)
    {
        if ($res != Msg::DBOK) return ;

        $next_node = $this->NextNode($this->PositionField()->Value, $this->LevelField()->Value + 1);

        $total_count = $next_node - $this->PositionField()->Value;

        DB::Cmd("DELETE FROM {$this->TableName()} WHERE {$this->PositionField()->Name} >= '{$this->PositionField()->Value}' AND {$this->PositionField()->Name} < '{$next_node}' ");

        $this->FixPositionsAfter($next_node, -$total_count);

        $this->ParentChildren();

    }

    public function IsRoot()
    {
        return $this->ParentIDField()->Value == '0';
    }

    protected function setLevel()
    {

        if ($this->IsRoot()) {

            $this->LevelField()->Value = 0;

        }	else {

            $this->LevelField()->Value = $this->getParentDataSet()->LevelField()->Value + 1;

        }

    }

    protected function setPosition()
    {

        $next_node = null;

        if ($this->PositionField()->Value == 0) {

            $next_node = $this->NextNode();

            $this->PositionField()->Value = $next_node;

        }

        return $next_node;

    }

    public function NextNode($parent_pos = null, $level = null)
    {

        if ( ! $parent_pos )

            $parent_pos = !$this->IsRoot() ? $this->getParentDataSet()->PositionField()->Value : 0;

        if ( ! $level ) $level = $this->LevelField()->Value;

        $next_node = DB::Find("SELECT MIN({$this->PositionField()->Name}) AS min_pos FROM {$this->TableName()} WHERE {$this->LevelField()->Name} < '{$level}' AND {$this->PositionField()->Name} > {$parent_pos} ");

        //In case of inserting a node at the end of the tree

        if ( ! $next_node ) {

            $next_node = DB::Find("SELECT MAX({$this->PositionField()->Name}) AS max_pos from {$this->TableName()} ") ;

            if ($next_node > 0) $next_node += 1;

        }

        //In case of inserting first node

        if ( ! $next_node ) $next_node = 1;

        return $next_node;

    }

    protected function MoveNode($pos, $next_node, $new_pos = null)
    {

        if ( ! $new_pos ) $new_pos = $this->PositionField()->Value;

        $total_count = $next_node - $pos;

        $this->FixPositionsAfter($new_pos, $total_count);

        if ($new_pos < $pos) {

            $pos = $pos + $total_count;

            $next_node = $next_node + $total_count;

        }

        $pos_diff = $new_pos - $pos;

        $lvl_diff = $this->LevelField()->Value - $this->_prev_['lvl'];

        $res = cmd ("UPDATE {$this->TableName()} SET 
						{$this->PositionField()->Name} = {$this->PositionField()->Name} + {$pos_diff},
						{$this->LevelField()->Name} = {$this->LevelField()->Name} + {$lvl_diff}  
						 WHERE {$this->PositionField()->Name} >= '{$pos}' AND {$this->PositionField()->Name} < '{$next_node}' ");

        $this->FixPositionsAfter($next_node, - $total_count);

        $this->PositionField()->Value = DB::Find("SELECT {$this->PositionField()->Name} AS p FROM {$this->TableName()} WHERE {$this->ID->Name} = '{$this->ID->Value}' ");

        return $res;

    }


    protected function FixPositionsAfter($pos = null, $add = 1)
    {
        if ( ! $pos ) $pos = $this->PositionField()->Value;

        $res = DB::Cmd("UPDATE {$this->TableName()} SET {$this->PositionField()->Name} = {$this->PositionField()->Name} + {$add} WHERE {$this->PositionField()->Name} >= '{$pos}' ");

        return $res;
    }

    public function UpdateNodePos()
    {
        $res = DB::Cmd("UPDATE {$this->TableName()} SET 
						{$this->LevelField()->Name} = '{$this->LevelField()->Value}',
						{$this->PositionField()->Name} = '{$this->PositionField()->Value}'
					WHERE {$this->ID->Name} = '{$this->ID->Value}'");

        return $res;
    }


    public function setParentDataSet($parent = null)
    {

        if ( ! $parent ) $parent = $this->ParentIDField()->Value;

        if ( $parent != 0 ) {

            $this->_ParentDataSet = static::Load($parent);

        }

    }

    protected function ParentChildren(TreeDataSet $parent = null)
    {

        if ( ! $parent )  $parent = $this->getParentDataSet();

        if ( ! $parent ) return 0;

        $children = $parent->CountChildren();

        $res = cmd ("UPDATE {$this->TableName()} SET {$this->ChildrenCountField()->Name} = '{$children}' WHERE {$this->ID->Name} = '{$parent->ID()->Value}' ");

        return $res;

    }

    public function LevelToDashesBuilder(Field $title_field)
    {
        return function () use ($title_field) {

            return str_repeat(' --- ', $this->LevelField()->Value) . $title_field->Value;

        };
    }

    /**
     * @return static
     */

    public function getParentDataSet()
    {
        if (!$this->_ParentDataSet) $this->setParentDataSet();

        return $this->_ParentDataSet;
    }


    public function NextSibling($expected_pos = true)
    {

        $next_sibling = $this->ActiveConnection()->Find("SELECT MIN({$this->PositionField()->Name}) AS pos FROM {$this->TableName()} WHERE {$this->PositionField()->Name} > {$this->PositionField()->Value} AND {$this->LevelField()->Name} <= {$this->LevelField()->Value}");

        if (! $next_sibling && $expected_pos) {

            $next_sibling = $this->ActiveConnection()->Find("SELECT (IFNULL(MAX({$this->PositionField()->Name}), 0) + 1) AS pos FROM {$this->TableName()}");

        }

        return $next_sibling;

    }


    /**
     * @return static
     */

    public function ListParents()
    {

        $table = $this->ActiveConnection()->Table(

            "SELECT {$this->TableName()}.* FROM {$this->TableName()} 

                    JOIN 
                    
                    (SELECT MAX({$this->PositionField()->Name}) AS mp FROM `{$this->TableName()}` 
                    
                              WHERE {$this->PositionField()->Name} <= {$this->PositionField()->Value} 
                              
                              GROUP BY Level) AS t 
                              
                      ON {$this->TableName()}.{$this->PositionField()->Name} = t.mp");

        return static::LoadFrom($table);

    }

    /**
     * @return static
     */

    public function ListChildren()
    {

        $table = $this->ActiveConnection()->Table(

            "SELECT * FROM {$this->TableName()} 

                    WHERE 
                    
                    ({$this->PositionField()->Name} = {$this->PositionField()->Value}) 
                    
                    OR
                     
                     ({$this->PositionField()->Name} > {$this->PositionField()->Value} 
                     
                        AND {$this->PositionField()->Name} < {$this->NextNode()} 
                     
                        AND {$this->LevelField()->Name} > {$this->LevelField()->Value})  
                    
                    ORDER BY {$this->PositionField()->Name}");

        return static::LoadFrom($table);

    }


    public function CountChildren()
    {

        $children = $this->ActiveConnection()->Find(

            "SELECT COUNT({$this->ID()->Name}) FROM {$this->TableName()} 

                    WHERE 
                     
                     ({$this->PositionField()->Name} > {$this->PositionField()->Value} 
                     
                        AND {$this->PositionField()->Name} < {$this->NextNode()} 
                        
                        AND {$this->LevelField()->Name} > {$this->LevelField()->Value}) ");

        return $children;

    }

    public function GenerateUl($ul_css_class, callable $func = null, $flush_output = true)
    {

        $this->Table()->Rewind();

        $level = 0;

        $first = true;

        $ul = '<ul class="'.$ul_css_class.'">';

        if ($this->Count() > 0) $ul .= '<li>';

        while ($this->Row()) :

            if (! $first) {

                if ($this->LevelField()->Value > $level) {

                    $ul .= '<ul class="'.$ul_css_class.'"><li>';

                } elseif ($this->LevelField()->Value < $level) {

                    $ul .= str_repeat('</ul></li>', $level - $this->LevelField()->Value);

                    $ul .= '<li>';

                } else {

                    $ul .= '</li><li>';

                }

            }

            $first = false;

            $level = $this->LevelField()->Value;

            $ul .= ($func) ? call_user_func_array($func, array($this))  :  $this->Title;

        endwhile;

        if ($this->Count() > 0) {

            if ($this->LevelField()->Value > 0) $ul .= str_repeat('</ul></li>', $level);

            else $ul .= '</li>';

        }

        $ul .= '</ul>';

        if ($flush_output) echo $ul;

        return $ul;

    }

    /**
	public function TreeView($pms = array(), $fixed_nodes = array())
	{
		
		$nodes = $this->ListNodes();
		
		$pms['nodes'] = array_merge( $fixed_nodes, $nodes ); 
		
		return Render::Widget('treeview', $pms);
		
	}
	
	public function ListNodes()
	{
		$nodes = array();
		
		$tree = DB::Table("select * from {$this->TableName()} order by {$this->Position['name']}");
		
		while ( $node = $tree->Row() )
		{
			$node_id = $node[$this->ID['name']];
		
			$node_title = $node[$this->Title['name']];
				
			$node_position = $node[$this->Position['name']];
				
			$node_level = $node[$this->Level['name']];
				
			$node_children = $node[$this->ChildrenCount['name']];
				
			$node_arr = array('id'=>$node_id, 'title'=>$node_title, 'position'=>$node_position, 'level'=>$node_level, 'children'=>$node_children);
				
			$nodes[] = $node_arr;
				
		}
		
		return $nodes;
		
	}
	**/

}