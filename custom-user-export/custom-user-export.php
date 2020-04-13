<?php

/*
Plugin Name: Custom User Export
Description: This is the Custom User Export plugin
Author: Dev
Text Domain: custom-user-export
*/

//prefix: CustomUserExport

defined( 'ABSPATH' ) or die();

define( 'CustomUserExport_VERSION', '1.0.0' );
define( 'CustomUserExport_URL', plugin_dir_url( __FILE__ ) );
define( 'CustomUserExport_PATH', plugin_dir_path( __FILE__ ) );

require_once 'CustomUserExport-functgions.php';
require_once 'CustomUserExport_Activate.php';
require_once 'CustomUserExport_Deactivate.php';

if ( ! class_exists( 'CustomUserExport' ) ) {

    /**
     * Class AuctionProperty
     */
    final class CustomUserExport {

        public function activate() {
            CustomUserExport_Activate::activate();
        }

        public function deactivate() {
            CustomUserExport_Deactivate::deactivate();
        }

    }

}

if ( class_exists( 'CustomUserExport' ) ) {

    $auction = new CustomUserExport();
    register_activation_hook( __FILE__, [ $auction, 'activate' ] );
    register_deactivation_hook( __FILE__, [ $auction, 'deactivate' ] );

}