<?php
interface TraversalVisitor{
    public function visitItem($item, $accumulated_results_from_children);
}

class DFSTraversal{

    protected $raw_array;
    protected $adjacency_function;
    protected $id_function;
    protected $adjacency_list;

    protected function getAdjacencyList(){
        if(!isset($this->adjacency_list)){
            $this->generateAdjacencyList();
        }
        return $this->adjacency_list;
    }

    protected function generateAdjacencyList(){
        $adjacency_function = $this->adjacency_function;
        $this->adjacency_list = array();
        foreach ($this->raw_array as $item){
            $key_for_item = $adjacency_function($item);
            if(is_null($key_for_item)) continue;
            if(!isset($this->adjacency_list[$key_for_item])){
                $this->adjacency_list[$key_for_item] = array();
            }
            $this->adjacency_list[$key_for_item][] = $item;
        }
    }

    public function traverse($initial_item, $item_visitor){
        $id_function = $this->id_function;
        $key_for_item = $id_function($initial_item);
        $adjacency_list = $this->getAdjacencyList();
        $accumulated_results = array();

        if(isset($adjacency_list[$key_for_item])){
            foreach($adjacency_list[$key_for_item] as $child){
                $accumulated_results[] = $this->traverse($child,$item_visitor);
            }
        }
        return $item_visitor->visitItem($initial_item, $accumulated_results);
    }

    public function __construct($raw_array, $adjacency_function, $id_function)
    {
            $this->raw_array = $raw_array;
            $this->adjacency_function = $adjacency_function;
            $this->id_function = $id_function;
    }
}


// Examlple
class TreeGenerator implements TraversalVisitor{

    public function visitItem($item, $accumulated_results_from_children){
        return array("key"=>$item, "children"=>$accumulated_results_from_children);
    }
}

$a1 = array('category_id'=>1, "parent_id"=>null);
$a2 = array('category_id'=>2, "parent_id"=>1);
$a3 = array('category_id'=>3, "parent_id"=>1);
$a4 = array('category_id'=>4, "parent_id"=>2);
$array_tree = array($a1, $a2, $a3, $a4);
$taversal = new DFSTraversal($array_tree, function($x){return $x["parent_id"];}, function($y){return $y["category_id"];});
$res =  $taversal->traverse($a1, new TreeGenerator());
echo json_encode($res);
