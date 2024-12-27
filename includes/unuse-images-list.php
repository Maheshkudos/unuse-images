<?php
// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Extending class
class Images_List_Table extends WP_List_Table{
    // define $table_data property
    private $table_data;
    // Bind table with columns, data and all
    function prepare_items() {
        
        //data
        if ( isset($_POST['s']) ) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $primary  = 'id';
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();
        // check and process any actions such as bulk actions.
	    $this->handle_table_actions();

        
        /* pagination */
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);
 
        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));

        $this->items = $this->table_data;
    }

    // Define table columns
    function get_columns(){

        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'thumbnail_id'  => __('ID', 'unuse-images'),
            'thumbnail_name'      => __('Name', 'unuse-images'),
            'thumbnail_path'      => __('Path', 'unuse-images'),
            'number_of_thumbnail' => __('Thumbnail', 'unuse-images'),
            'trash'     => __('Trash', 'unuse-images'),
            'deleted'   => __('Delete', 'unuse-images')

        );
        return $columns;
    }

    // Get table data
    private function get_table_data( $search = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'unuse_image_cleanup';
        if ( !empty($search) ) {

            $search_res = $wpdb->get_results("SELECT * from {$table} WHERE thumbnail_name Like '%{$search}%' ", ARRAY_A );

            return $search_res;
            
        } else {
            $all_result = $wpdb->get_results("SELECT * from {$table}", ARRAY_A );

            return $all_result;
        }

    }

    function column_default($item, $column_name){
        switch ($column_name) {
            case 'thumbnail_id':
            case 'thumbnail_name':
            case 'thumbnail_path':
                // If the column is 'thumbnail', display the image
                if ($column_name === 'thumbnail_path') {
                    // Check if thumbnail exists
                    $thumbnail_url = $item[$column_name]; 
                    if (!empty($thumbnail_url)) {
                        $image_preview ='<a href="' . esc_url($thumbnail_url) . '" target="_blank"><img src="' . esc_url($thumbnail_url) . '" alt="Thumbnail" style="max-width: 100px;max-height: 100px;" /></a>';

                        return $image_preview;
                    } else {
                        return 'No Thumbnail';
                    }
                }
            return $item[$column_name];
            case 'number_of_thumbnail':
            case 'trash':
            case 'deleted':
            default:
                return $item[$column_name];
        }
    }
    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        // If the delete bulk action is triggered
        $action = $this->current_action();
        
        if( 'delete_all' === $action && isset( $_POST['imageid'] ) && is_array( $_POST['imageid'] ) && ! empty( $_POST['imageid'] )) {
           
            $delete_ids = $_POST['imageid'];
            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $delete_id ) {
                global $wpdb;
                $wpdb->query($wpdb->prepare( "DELETE FROM wp_unuse_image_cleanup WHERE thumbnail_id='".$delete_id."'"));
            }
            wp_redirect( esc_url( add_query_arg() ) );
            die;
        }
    }

    function column_cb($item){

        return sprintf(
            '<input id="cb-select-'.$item['thumbnail_id'].'" type="checkbox" name="imageid[]" value="%s" />',
            $item['thumbnail_id']
        );
    }

    protected function column_user_login( $item ) {		
        $admin_page_url =  admin_url( 'users.php' );
    
        // row action to view usermeta.
        $query_args_view_usermeta = array(
            'page'		=>  wp_unslash( $_REQUEST['page'] ),
            'action'	=> 'view_usermeta',
            'user_id'	=> absint( $item['ID']),
            '_wpnonce'	=> wp_create_nonce( 'view_usermeta_nonce' ),
        );
        $view_usermeta_link = esc_url( add_query_arg( $query_args_view_usermeta, $admin_page_url ) );		
        $actions['view_usermeta'] = '<a href="' . $view_usermeta_link . '">' . __( 'View Meta', $this->plugin_text_domain ) . '</a>';		
    
        // similarly add row actions for add usermeta.
    
        $row_value = '<strong>' . $item['thumbnail_id'] . '</strong>';
        return $row_value . $this->row_actions( $actions );
    }
      
    // To show bulk action dropdown
    function get_bulk_actions(){

        $actions = array(
            'delete_all'    => __('Delete', 'unuse-images')
        );
        return $actions;
    }

   
}


// Creating an instance
$table = new Images_List_Table();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Images List Table</h1>
    <button class="button button-primary primary-large unuse-image-scan">Scan</button>
    <div class="progress-bar">
        <div class="progress" style="width: 0%;"></div>
    </div>

    <form method="post" >
        <?php
            // Prepare table
            $table->prepare_items();
            // Search form
            $table->search_box('search', 'search_id');
            // Display table
            $table->display();
        ?>
    </form>
    
</div>