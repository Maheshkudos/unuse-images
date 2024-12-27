<?php
/**
*
* @link              https://www,.exadmin.com
* @since             1.0.0
* @package           Unuse_Images
*
* @wordpress-plugin
* Plugin Name:       Unuse Images
* Plugin URI:        https://www.examle.com
* Description:       A plugin to remove unused media from WordPress and free up space on hosting.
* Version:           1.0.0
* Author:            admin
* Author URI:        https://www,.exadmin.com/
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       unuse-images
* Domain Path:       /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Rename this for your plugin and update it as you release new versions.
*/
define( 'UNUSE_IMAGES_VERSION', '1.0.0' );
define( 'UNUSE_IMAGES_DIR', plugin_dir_path( __FILE__ ) );
define( 'UNUSE_IMAGES_URL', plugin_dir_url( __FILE__ ));


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-unuse-images-activator.php
 */
function activate_unuse_images() {
	// require_once plugin_dir_path( __FILE__ ) . 'includes/class-unuse-images-activator.php';
	// Unuse_Images_Activator::activate();
    global $wpdb;
    $table_name = $wpdb->prefix . "unuse_image_cleanup";

    $charset_collate = $wpdb->get_charset_collate();
    //ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    $sql = "CREATE TABLE $table_name ( 
        
        thumbnail_id MEDIUMINT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        thumbnail_name VARCHAR(200) NOT NULL ,
        thumbnail_path VARCHAR(250) NOT NULL ,
        number_of_thumbnail INT NULL DEFAULT NULL ,
        trash BOOLEAN NULL DEFAULT NULL ,
        deleted BOOLEAN NULL DEFAULT NULL 
        ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-unuse-images-deactivator.php
 */
function deactivate_unuse_images() {
	// require_once plugin_dir_path( __FILE__ ) . 'includes/class-unuse-images-deactivator.php';
	// Unuse_Images_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_unuse_images' );
register_deactivation_hook( __FILE__, 'deactivate_unuse_images' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
// require plugin_dir_path( __FILE__ ) . 'includes/class-unuse-images.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
// function run_unuse_images() {

// 	$plugin = new Unuse_Images();
// 	$plugin->run();

// }
// run_unuse_images();
function unues_image_script(){
    wp_enqueue_style( 'unuse-image-css', UNUSE_IMAGES_URL.'admin/css/unuse-images-admin.css', '1.0', true );
    wp_enqueue_script( 'unuse-image-script', UNUSE_IMAGES_URL.'admin/js/unuse-images-admin.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'unuse-image-script', 'ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}
add_action( 'admin_enqueue_scripts', 'unues_image_script' );


add_action('admin_menu', 'my_menu_pages');
function my_menu_pages(){
    add_submenu_page('upload.php', 'Unuse Images', 'Unuse Images', 'manage_options', 'unuse_images', 'unuse_images_callback' );
}

function unuse_images_callback(){
    // echo "hello";
    // require UNUSE_IMAGES_DIR.'includes/unuse-images.php';

    require UNUSE_IMAGES_DIR.'includes/unuse-images-list.php';
}


add_action( 'wp_ajax_unuse_image_scan', 'unuse_image_scan');

function unuse_image_scan(){ 

    $query_images_args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        // 'paged'          => $paged,
        'orderby'        => 'ID',
        'order'          => 'DESC', //ASC
    );

    $query_images = new WP_Query( $query_images_args );

    $images = array();
    $count = 1;
    if ($query_images->have_posts()) :
        foreach ( $query_images->posts as $image ) {
        
            $unused_img = array();
            $search_image_id = $image->ID;
            $search_name = $image->post_name;
            $image_url = $image->guid;

            
            $Image_Pathinfo = pathinfo($image_url);
            $imageName = $Image_Pathinfo['filename'];//.'.'.$Image_Pathinfo['extension'];

            echo '<br/>---------<br/>';
            echo "Image: $imageName"." and Image ID: ".$search_image_id;

            global $wpdb;
            $results_name = $wpdb->get_results(
                            "SELECT DISTINCT wp_posts.ID FROM wp_posts 
                            INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )  
                            WHERE 1=1 
                            AND wp_posts.post_type != 'attachment' 
                            AND (
                                (wp_posts.post_title LIKE '%".$imageName."%') 
                            OR (wp_posts.post_excerpt LIKE '%".$imageName."%') 
                            OR (wp_posts.post_content LIKE '%".$imageName."%')
                            ) 
                            OR wp_postmeta.meta_value LIKE '%".$imageName."%'
                            AND wp_posts.post_type != 'attachment' 
                            ORDER BY wp_posts.ID DESC"
                            , ARRAY_N );

                            // print_r($results_name);
                            // die;
            
            $results_ids = $wpdb->get_results(
                        "SELECT DISTINCT wp_posts.ID FROM wp_posts 
                        INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )  
                        WHERE 1=1
                        AND wp_posts.post_type != 'attachment'
                        AND ((
                            (wp_posts.post_title LIKE '%".$search_image_id."%') 
                        OR (wp_posts.post_excerpt LIKE '%".$search_image_id."%') 
                        OR (wp_posts.post_content LIKE '%".$search_image_id."%'))) 
                        OR wp_postmeta.meta_value LIKE '%".$search_image_id."%'
                        AND wp_posts.post_type != 'attachment'  
                        ORDER BY wp_posts.ID DESC"
                        , ARRAY_N );
                        // print_r($results_ids[0]);

            $term_ids = $wpdb->get_results(
                        "SELECT DISTINCT wp_termmeta.term_id FROM wp_termmeta 
                        WHERE 1=1
                        AND ( wp_termmeta.meta_value = $search_image_id 
                        OR wp_termmeta.meta_value LIKE '%".$imageName."%'
                        ) 
                        ORDER BY wp_termmeta.term_id DESC"
                        , ARRAY_N );
                        // print_r($term_ids);
            
            // $options_ids = $wpdb->get_results(
            //             "SELECT DISTINCT wp_options.option_id FROM wp_options
            //             WHERE 1=1
            //             AND  wp_options.option_value LIKE '%".$imageName."%'
            //             ORDER BY wp_options.option_id DESC"
            //             , ARRAY_N );
            //             // print_r($options_ids);
            //             // die;

            $postsWith_Image = array_unique(array_merge($results_name,$results_ids,$term_ids)); 
            // ,$options_ids

            if(!empty($postsWith_Image[0])){
                echo '  => This image is used<br/>';
                print_r($postsWith_Image[0]);
            }else{
                echo '  => Delete this image<br/>';
                echo "$image->guid<br/>";
                DB_INSERT_CUSTOM($search_image_id, $imageName , $image_url);
            }
            $count++;
        }
    endif;
    die;
}

function DB_INSERT_CUSTOM($thumbnail_id, $thumbnail_name, $thumbnail_img){
    global $wpdb;
    $table_name = $wpdb->prefix . 'unuse_image_cleanup';
	
    $wpdb->insert( 
        $table_name, 
        array( 
            'thumbnail_id' => $thumbnail_id, 
            'thumbnail_name' => $thumbnail_name, 
            'thumbnail_path' => $thumbnail_img,
            'number_of_thumbnail' => '',
            'trash' => '1',
            'deleted' => '1', 
        ) 
    );
}