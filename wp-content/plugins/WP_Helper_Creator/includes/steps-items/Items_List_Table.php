<?php
if (!class_exists('WP_List_Table')) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Fhpc_Items_List_Table extends WP_List_Table {
        
    public $helperID;
    
	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$columns = $this -> get_columns();
		$hidden = $this -> get_hidden_columns();
		$sortable = $this -> get_sortable_columns();

		$data = $this -> table_data();

		$this -> _column_headers = array($columns, $hidden, $sortable);
		$this -> items = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array('id' => 'ID', 'title' => 'Title','step'=>'Step','order'=>'Order','stepID'=>'Step','type'=>'Type','remove'=>'');

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array('id','stepID');
		return null;
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return null;
	}
	
	/**
	 * Get steps names
	 *
	 * @return Array
	 */
	 private function getStepsNames() {
	 	global $wpdb;
		$table_name = $wpdb -> prefix . "fhpc_steps";
		$rows = $wpdb -> get_results("SELECT * FROM $table_name");

		$data = array();
		foreach ($rows as $row) {
			$data[] = array('id'=>$row->id,'title'=>$row->title);
		}
		return $data;
	 }

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	 private function table_data() {
		global $wpdb;
                $helperID = $this->helperID;
		$steps = $this->getStepsNames();
		$table_name = $wpdb -> prefix . "fhpc_items";
                if ($helperID>0){
                    $rows = $wpdb -> get_results("SELECT * FROM $table_name WHERE stepID=$helperID ORDER BY ordersort ASC");                    
                }else {
                    $rows = $wpdb -> get_results("SELECT * FROM $table_name ORDER BY stepID ASC, ordersort ASC");                    
                }

		$data = array();
		foreach ($rows as $row) {
			$step_name = "";
			foreach ($steps as $step) {
				if ($step['id'] == $row->stepID){
					$step_name = $step['title'];
				}
			}
			$data[] = array('id'=>$row->id,'stepID'=>$row->stepID,'step'=>$step_name,'title'=>$row->title,'order'=>$row->ordersort,'type'=>$row->type,'remove'=>'');
		}
		return $data;
	}
	

	// Used to display the value of the id column
	public function column_id($item) {
		return $item['id'];
	}
	
	/**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
        	case 'title':
				return '<a href="admin.php?page=fhpc-item-add&item='.$item['id'].'">'.$item[$column_name].'</a>';
				break;
        	case 'remove' :
				return '<a href="admin.php?page=fhpc-items&remove='.$item['id'].'">Delete</a>';
				break;
            case 'id':
            case 'image':
            case 'order':
            case 'group':
            case 'price':
            case 'stepID':
            case 'step':
            case 'type':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

}
?>