<?php
ob_start();

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TT_Example_List_Table2 extends WP_List_Table {
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'unsubsciber',     //singular name of the listed records
            'plural'    => 'unsubscibers',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
      
    }

    function column_default($item, $column_name){
        // var_export($item);exit;
        switch($column_name){
            case 'reason':
            case 'email':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    
   

    function column_email($item){
        
        //Build row actions
        $actions = array(
            
            'delete'    => sprintf('<a href="?page=%s&action=%s&unsubsciber=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID'])
        );
        
        //Return the title contents
        return sprintf('%1$s %3$s',
            /*$1%s*/ $item['email'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
  
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'comment_author'     => 'Name',
            'comment_author_email'    => 'Email',
            'reason'  => 'Reason',
            // 'action'=>'Actions'
        );
        return $columns;
    }

    function get_uscolumns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'email'     => 'Email',
            'reason'    => 'Reason',
            // 'reason'  => 'Reason',
            // 'action'=>'Actions'
        );
        return $columns;
    }


    function get_ussortable_columns() {
        $sortable_columns = array(
            'email'     => array('email',false),     //true means it's already sorted
            // 'reason'    => array('reason',true),
            // 'reason'  => array('reason',false)
        );
        return $sortable_columns;
    }

    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    

    function process_usbulk_action() {

        global $wpdb;
        $table_name = 'unsubscribers';
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {

            $ids = isset($_REQUEST['unsubsciber']) ? $_REQUEST['unsubsciber'] : array();
            // print_r($ids);die();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
               
            $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");

            wp_redirect(admin_url('admin.php?page=unsubscribed_emails'));
            }
            //wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }


    public function filter_table_data( $table_data, $search_key ) {
    $filtered_table_data = array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
        foreach( $row as $row_val ) {
            if( stripos( $row_val, $search_key ) !== false ) {
                return true;
            }               
        }           
    } ) );

    return $filtered_table_data;

    }

    function prepare_unsubscriber() {

        global $wpdb; //This is used only if making any database queries
        $results = $wpdb->get_results("SELECT * FROM unsubscribers ",ARRAY_A);

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        
        $user_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
        if( $user_search_key ) {
        $results = $this->filter_table_data( $results, $user_search_key );
        }
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_uscolumns();
        $hidden = array();
        $sortable = $this->get_ussortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_usbulk_action();
        
        
        
        
            array_walk($results, function (& $item) {
            $item['ID'] = $item['id'];
            unset($item['id']);
            });
       
            
        // echo "<pre>";print_r($results);exit;
        // echo "<pre>";print_r($this->example_data);exit;

        $data = $results;//$this->example_data;
                
        
        
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'email'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    
    }

    

}


 add_shortcode( 'email_unsubscribe','email_unsubscribe_shortcode');

 function email_unsubscribe_shortcode() {
        global $wpdb;
            // echo base64_decode($_GET['token']);
            if(isset($_POST['submit'])) {
                

                $singledata = $wpdb->get_row("SELECT * FROM unsubscribers WHERE email ='".base64_decode($_GET['token'])."'",OBJECT);
                // echo count($singledata);
                if(count($singledata)==1){
                    echo "<b style='color:red'>Already unsubscribed</b>";
                }else{
                    // echo "holla";
                    $wpdb->insert(
                    'unsubscribers', 
                    array( 
                    'email' => base64_decode($_GET['token']), 
                    'reason' => $_POST['reason'] 
                    ),
                    array( 
                    '%s', 
                    '%s' 
                    ) 
                    );
                    echo "<b style='color:green'>Thank you</b>";
                }
            }
                ?>
                    <form name="unsubscribeform" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <input type="text" name="reason" placeholder="Enter the reason for unsubscription">
                    <input type="submit" value="Submit" name="submit">
                    </form>
          <?php 
}
function unsubscribed_emails() {

 $testListTable = new TT_Example_List_Table2();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_unsubscriber();
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
         <h2>Emails Unsubscribers</h2>
        
       
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php 
            $testListTable->search_box( 'search', 'search_id' );
            $testListTable->display();
             ?>
        </form>
        
    </div>
    <?php
 }




