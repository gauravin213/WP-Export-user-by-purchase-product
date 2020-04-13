<?php

defined( 'ABSPATH' ) || exit;


add_action('admin_enqueue_scripts', 'CustomUserExport_admin_enqueue_scripts_fun', 10, 1);
function CustomUserExport_admin_enqueue_scripts_fun(){

    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
}



add_action( 'admin_menu', 'CustomUserExport_admin_menu');
function CustomUserExport_admin_menu(){

    $title = "CustomUserExport";
    add_menu_page( $title, $title, 'manage_options', 'custom-user-export', 'CustomUserExport_add_menu_page_fun');

    $title = "Test category";
    add_menu_page( $title, $title, 'manage_options', 'custom-user-export2', 'CustomUserExport_add_menu_page_fun2');
    
}

function CustomUserExport_add_menu_page_fun2(){

    if (isset($_POST['cate_id'])) {
       
        $categories = get_term_children( $_POST['cate_id'], 'product_cat' ); 

        if ( $categories && ! is_wp_error( $category ) ) {

          foreach($categories as $category){
              $term[] = get_term( $category, 'product_cat' );
          }

        }

        echo "<pre>"; print_r($term); echo "</pre>";


        }

    ?>
    <div class="wrap">

        <h1 class="wp-heading-inline"><?php _e( 'Cate', 'tmm-desred' ); ?></h1><hr class="wp-header-end">

        <form method="post" action="">

            <input type="text" name="cate_id"  value="">
            <button>Export</button>

        </form>
    <?php
}

function CustomUserExport_add_menu_page_fun(){

    global $wpdb;

    /*$result = $wpdb->get_results("SELECT * FROM wp_terms WHERE term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE parent != '0' AND (term_id !='42' AND  taxonomy = 'product_cat') )");*/

    $result = $wpdb->get_results("SELECT * FROM wp_terms WHERE term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE  term_id !='42' AND  taxonomy = 'product_cat' )");

    ?>
    <div class="wrap">

        <h1 class="wp-heading-inline"><?php _e( 'Report', 'tmm-desred' ); ?></h1><hr class="wp-header-end">


        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">

            <input type="hidden" name="action" value="CustomUserExportAction">

            <input type="hidden" name="selected_category_name" id="selected_category_name" value="">

            <select name="CustomUserExport_select_name" id="CustomUserExport_select_name">
                <?php foreach( $result as $term ) { ?>
                    <option value='<?php echo $term->term_id; ?>'><?php echo $term->name; ?></option>
                <?php }; ?>
            </select>

            <button>Export</button>

        </form>

        <script type="text/javascript">
            jQuery(document).ready(function(){

                jQuery('#CustomUserExport_select_name').select2({
                    width: '25%',
                    placeholder : "select me" 
                });


                var selected_category_name = jQuery('#CustomUserExport_select_name').find(':selected').text();
                jQuery('#selected_category_name').val(selected_category_name);
              
                jQuery(document).on('change', '#CustomUserExport_select_name', function(){

                    var target = jQuery(this);

                    var selected_category_name = target.find(':selected').text();

                    jQuery('#selected_category_name').val(selected_category_name);

                });
                
            });
        </script>

    </div>
    <?php
}


add_action( 'admin_post_CustomUserExportAction', 'CustomUserExportAction_fun' );
add_action( 'admin_post_nopriv_CustomUserExportAction', 'CustomUserExportAction_fun' );
function CustomUserExportAction_fun(){

    if ( ! current_user_can( 'manage_options' ) )
        return;

        global $wpdb;

        $term_id = $_POST['CustomUserExport_select_name']; 

        $sub_category = get_term_children( $term_id, 'product_cat' ); 

        if (!empty($sub_category)) {
            $categories = array_merge( $sub_category, array($term_id) );
        }else{
            $categories = array($term_id);
        }


        $cat = implode(', ', $categories);


       
        //Get product ids by category
        $a = "SELECT ID FROM wp_posts WHERE ID IN (SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_id FROM wp_terms WHERE term_id IN (".$cat.")  )) AND post_type='product' AND post_status='publish'";

        //Get Order ids by product
        $b = "SELECT order_id FROM wp_woocommerce_order_items WHERE order_item_id IN (SELECT order_item_id FROM wp_woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND meta_value IN (".$a.") )";

        //Get customer id by order
        $c = "SELECT meta_value FROM wp_postmeta WHERE post_id IN (".$b.") AND meta_key = '_customer_user'";

        //Get user ids by customer id
        $q = "SELECT * FROM wp_users WHERE ID IN (".$c.")";

       
        $result = $wpdb->get_results($q, ARRAY_A);

        $pppp1 = array();

        foreach ($result as $data) {

            $p_name_s = get_product_ppppppp($data['ID']);

            $p_name_s_i = implode(' | ', $p_name_s);

            $pppp2 = array();

            $pppp2 = array(
                '0' => $data['ID'],
                '1' => get_user_meta( $data['ID'] , 'first_name', true),
                '2' => get_user_meta( $data['ID'] , 'last_name', true),
                '3' => $data['user_email'],
                '4' => $p_name_s_i
            );

            $pppp1[] = $pppp2;
        }


        $header_row = array(
                '0' => 'UserID',
                '1' => 'FirstName',
                '2' => 'LastName',
                '3' => 'Email',
                '4' => 'OrderId'
            );

        $data_rows = $pppp1;

        $filename = 'users-'.$_POST['selected_category_name'].'.csv';
            
        $fh = @fopen( 'php://output', 'w' );
        fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-type: text/csv' );
        header( "Content-Disposition: attachment; filename={$filename}" );
        header( 'Expires: 0' );
        header( 'Pragma: public' );
        fputcsv( $fh, $header_row );
        foreach ( $data_rows as $data_row ) {
            fputcsv( $fh, $data_row );
        }
        fclose( $fh );
        die();
}


function get_product_ppppppp($user_id){

    global $wpdb;

    $q = "SELECT * FROM wp_postmeta WHERE meta_value = '".$user_id."' AND meta_key = '_customer_user'";

    $result = $wpdb->get_results($q);

    foreach ($result as $data ) { 

        $p_name[] = '#'.$data->post_id;
        
        /*$b = "SELECT order_item_name FROM wp_woocommerce_order_items WHERE order_id = '".$data->post_id."'";

        $result_b = $wpdb->get_results($b);

        foreach ($result_b as $data) {
            //echo "<pre>"; print_r($data->order_item_name); echo "</pre>";

            $p_name[] = $data->order_item_name;
        }*/

        
    }

    return $p_name;
}