<?php

defined( 'ABSPATH' ) || exit;


add_action('admin_enqueue_scripts', 'CustomUserExport_admin_enqueue_scripts_fun', 10, 1);
function CustomUserExport_admin_enqueue_scripts_fun(){

    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );

    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_style( 'jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
}



add_action( 'admin_menu', 'CustomUserExport_admin_menu');
function CustomUserExport_admin_menu(){

    $title = "CustomUserExport";
    add_menu_page( $title, $title, 'manage_options', 'custom-user-export', 'CustomUserExport_add_menu_page_fun');

}



function CustomUserExport_add_menu_page_fun(){

    global $wpdb;

    $result = $wpdb->get_results("SELECT * FROM wp_terms WHERE term_id IN (SELECT term_id FROM wp_term_taxonomy WHERE  term_id !='42' AND  taxonomy = 'product_cat' )");




    ?>
    <div class="wrap">

        <h1 class="wp-heading-inline"><?php _e( 'Report', 'tmm-desred' ); ?></h1><hr class="wp-header-end">

        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">

            <input type="hidden" name="action" value="CustomUserExportAction">

            <input type="hidden" name="selected_category_name" id="selected_category_name" value="">

            <input type="text" name="CustomUserExport_date_from" id="CustomUserExport_date_from" placeholder="from">

            <input type="text" name="CustomUserExport_date_to" id="CustomUserExport_date_to" placeholder="to">

            <select name="CustomUserExport_select_name" id="CustomUserExport_select_name">
                <?php foreach( $result as $term ) { ?>
                    <option value='<?php echo $term->term_id; ?>'><?php echo $term->name; ?></option>
                <?php }; ?>
            </select>

            <button>Export</button>

        </form>

        <script type="text/javascript">
            jQuery(document).ready(function(){

                jQuery( "#CustomUserExport_date_from" ).datepicker({dateFormat: 'mm-dd-yy'}).attr('autocomplete', 'off');

                jQuery( "#CustomUserExport_date_to" ).datepicker({dateFormat: 'mm-dd-yy'}).attr('autocomplete', 'off');

         

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
        $a = "SELECT DISTINCT ID FROM wp_posts WHERE ID IN (SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_id FROM wp_terms WHERE term_id IN (".$cat.")  )) AND post_type='product' AND post_status='publish'";

        //Get Order ids by product
        $b = "SELECT DISTINCT order_id FROM wp_woocommerce_order_items WHERE order_item_id IN (SELECT order_item_id FROM wp_woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND meta_value IN (".$a.") )";


        //Get order by date 
        $date1 = explode('-', $_POST['CustomUserExport_date_from']);
        $date_from = $date1[2].'-'.$date1[0].'-'.$date1[1]; // converted y-m-d formate


        $date2 = explode('-', $_POST['CustomUserExport_date_to']);
        $date_to = $date2[2].'-'.$date2[0].'-'.$date2[1]; // converted y-m-d formate

       
        $post_status = implode("','", array('wc-processing', 'wc-completed') );

        $result = $wpdb->get_results( "SELECT DISTINCT ID FROM $wpdb->posts 
                    WHERE post_type = 'shop_order'
                    AND ID IN (".$b.")
                    AND post_status IN ('{$post_status}')
                    AND post_date BETWEEN '{$date_from} 00:00:00' AND '{$date_to} 23:59:59'
                ", ARRAY_A);


        $pppp1 = array();

        $c = 1;

        foreach ($result as $data) {

            $order_id = $data['ID'];

            $pppp2 = array();

            $country_code = get_post_meta( $order_id , '_billing_country', true);
            $state_code = get_post_meta( $order_id , '_billing_state', true);

            $_billing_country = WC()->countries->countries[ $country_code ];
            $states = WC()->countries->get_states( $country_code );
            $state  = ! empty( $states[ $state_code ] ) ? $states[ $state_code ] : '';

            $pppp2 = array(
                '0' => $c,
                '1' => get_post_meta( $order_id , '_billing_first_name', true),
                '2' => get_post_meta( $order_id , '_billing_last_name', true),
                '3' => get_post_meta( $order_id , '_billing_email', true), 
                '4' => get_post_meta( $order_id , '_billing_phone', true), 
                '5' => get_post_meta( $order_id , '_billing_city', true), 
                '6' => get_post_meta( $order_id , '_billing_postcode', true), 
                '7' => $_billing_country, 
                '8' => $state,
                '9' => get_post_meta( $order_id , '_billing_address_1', true),
                '10' => '#'.$order_id,
                '11' => get_the_date('Y-m-d', $order_id)
            );

            $pppp1[] = $pppp2;

            $c++;
        }


        $header_row = array(
                '0' => 'Sr. No.',
                '1' => 'FirstName',
                '2' => 'LastName',
                '3' => 'Email',
                '4' => 'Phone',
                '5' => 'City',
                '6' => 'ZipCode',
                '7' => 'Country / Region',
                '8' => 'State / County',
                '9' => 'Address',
                '10' => 'OrderId',
                '11' => 'Date'
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