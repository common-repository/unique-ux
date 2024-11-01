<?php
/**
 * Fired during plugin activation
 *
 * @link       http://awcode.com
 * @since      0.0.1
 *
 * @package    uniqueux
 * @subpackage uniqueux/includes
 */
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    uniqueux
 * @subpackage uniqueux/includes
 * @author     AWcode<m@awcode.com>
 */
class Uniqueux_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();


		$table_name = $wpdb->prefix . "uniqueux_user_group"; 
		$sql1 = "CREATE TABLE $table_name (
				  `user_group_id` int(11) NOT NULL AUTO_INCREMENT,
				  `user_group_name` varchar(100) NOT NULL,
				  `country` varchar(2) NOT NULL, 
				  `points` int(3) NOT NULL, 
				  PRIMARY KEY  user_group_id (user_group_id) 
				) $charset_collate;";
		
		dbDelta( $sql1 );

		$table_name = $wpdb->prefix . "uniqueux_content_group"; 		
		$sql2 = "CREATE TABLE $table_name (
					  `content_group_id` int(11) NOT NULL AUTO_INCREMENT,
					  `content_group_name` varchar(100) NOT NULL,
					  PRIMARY KEY  content_group_id (content_group_id) 
					) $charset_collate;";
		
		dbDelta( $sql2 );

		$table_name = $wpdb->prefix . "uniqueux_content"; 		
		$sql3 = "CREATE TABLE $table_name (
					  `content_id` int(11) NOT NULL AUTO_INCREMENT,
					  `content_html` text NOT NULL,
					  `content_group_id` int(11) NOT NULL, 
					  `user_group_id` int(11) NOT NULL, 
					  `schedule_id` int(11) NOT NULL,
					  `device_id` int(11) NOT NULL,
					  PRIMARY KEY  content_id (content_id) 
					) $charset_collate;";
		
		dbDelta( $sql3 );

		$table_name = $wpdb->prefix . "uniqueux_visitors"; 		
		$sql4 = "CREATE TABLE $table_name (
					  `visitor_id` int(11) NOT NULL AUTO_INCREMENT, 
					  `visitor_cookie` varchar(255) NOT NULL, 
					  `useragent` varchar(255) NOT NULL, 
					  `first_time` datetime NOT NULL, 
					  `latest_time` datetime NOT NULL,
					  `country` varchar(100) NOT NULL,
					  `page_views` int(11) NOT NULL, 
					  PRIMARY KEY  visitor_id (visitor_id) 
					) $charset_collate;";
		
		dbDelta( $sql4 );
		
		
		$table_name = $wpdb->prefix . "uniqueux_schedule"; 		
		$sql5 = "CREATE TABLE $table_name (
					  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
					  `schedule_name` varchar(255) NOT NULL,
					  `content_group_id` int(11) NOT NULL,
					  `date_start` datetime NOT NULL,
					  `date_end` datetime NOT NULL,
					  PRIMARY KEY  schedule_id (schedule_id) 
					) $charset_collate;";
					
		dbDelta( $sql5 );
		
		
		$table_name = $wpdb->prefix . "uniqueux_devices"; 		
		$sql6 = "CREATE TABLE $table_name (
					  `device_id` int(11) NOT NULL AUTO_INCREMENT,
					  `device_name` varchar(255) NOT NULL,
					  `device_list` varchar(50) NOT NULL,
					  `content_group_id` int(11) NOT NULL, 
					  PRIMARY KEY (`device_id`)
					) $charset_collate;";
					
		dbDelta( $sql6 );
		
	}
}
