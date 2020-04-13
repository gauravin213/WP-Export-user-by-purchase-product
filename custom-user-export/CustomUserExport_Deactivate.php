<?php

if ( ! class_exists( 'CustomUserExport_Deactivate' ) ) {

	/**
	 * Class Deactivate
	 * @package App\Deactivate
	 */
	class CustomUserExport_Deactivate {

		function __construct(){
			//echo "Deactivate";
		}

		public static function deactivate() {
			/*global $wpdb;
			$wpdb->delete( 'wp_posts', [ 'post_name' => 'login' ] );
			$wpdb->delete( 'wp_posts', [ 'post_name' => 'registration' ] );
			$wpdb->delete( 'wp_posts', [ 'post_name' => 'new-property' ] );
			$wpdb->delete( 'wp_posts', [ 'post_name' => 'profile' ] );
			$wpdb->delete( 'wp_posts', [ 'post_name' => 'list-properties' ] );
			$wpdb->delete( 'wp_posts', [ 'post_name' => 'search-properties' ] );
			flush_rewrite_rules();*/

		}

	}

}

