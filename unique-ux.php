<?php
/*
 * Plugin Name: UniqueUX
 * Version: 0.9.2
 * Plugin URI: http://onlyux.com/
 * Description: Unique UX allows wordpress sites to present unique, personalised content for each visitor.
 * Author: AWcode, PDSonline
 * Author URI: http://onlyux.com/
 * Requires at least: 4.0
 * Tested up to: 4.6.1
 *
 */
 
 // If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
	die;
}

$cookie_val = '';

if(get_option('uniqueux_geoip_file') != '' && file_exists(get_option('uniqueux_geoip_file')) && get_option('uniqueux_geoip_phar_file') != '' && file_exists(get_option('uniqueux_geoip_phar_file'))){
	require_once get_option('uniqueux_geoip_phar_file');
	$reader = new \GeoIp2\Database\Reader(get_option('uniqueux_geoip_file'));
}

define('ALLOW_UNFILTERED_UPLOADS', true);

function activate_uniqueux() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-activator.php';
	Uniqueux_Activator::activate();
}


function deactivate_uniqueux() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-deactivator.php';
	Uniqueux_Deactivator::deactivate();
}

function uniqueux_register_widgets() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-widget.php';
	register_widget( 'Uniqueux_Widget' );
}

register_activation_hook( __FILE__, 'activate_uniqueux' );
register_deactivation_hook( __FILE__, 'deactivate_uniqueux' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-includes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-ajax-callback.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-setting.php';

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-dashboard-table.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-uniqueux-meta-boxes.php';


add_action( 'widgets_init', 'uniqueux_register_widgets' );
add_shortcode( 'uniqueux_content', 'uniqueux_shortcode_func' );
add_shortcode( 'uniqueux_country', 'uniqueux_shortcode_country' );
add_shortcode( 'uniqueux_browser', 'uniqueux_shortcode_browser' );
add_shortcode( 'uniqueux_ip', 'uniqueux_shortcode_ip' );
add_shortcode( 'uniqueux_searchengine', 'uniqueux_shortcode_searchengine' );
add_shortcode( 'uniqueux_searchphrase', 'uniqueux_shortcode_searchphrase' );
add_action( 'wp_enqueue_scripts','uniqueux_styles' );
add_action( 'wp_enqueue_scripts','uniqueux_scripts' );

if(isset($_GET['page']) && ($_GET['page'] == 'uniqueux_content_group' || $_GET['page'] == 'uniqueux_settings')){
	add_action( 'admin_enqueue_scripts', 'uniqueux_admin_scripts' );
	add_action( 'admin_enqueue_scripts', 'uniqueux_admin_styles' );
}

add_action( 'wp_ajax_uniqueux', 'uniqueux_callback' );
add_action( 'wp_ajax_nopriv_uniqueux', 'uniqueux_callback' );
add_action('admin_menu', 'uniqueux_control_menu');

add_filter('widget_text', 'do_shortcode');

add_action( 'init', 'uniqueux_main_process' );

add_action('wp_footer', 'uniqueux_init');


add_filter('upload_mimes', 'uniqueux_pixert_upload_types');

add_action( 'wp_ajax_uniqueux_del_schedule', 'uniqueux_del_schedule_callback' );

add_action( 'wp_ajax_uniqueux_del_device', 'uniqueux_del_device_callback' );

add_action( 'wp_ajax_uniqueux_update_points', 'uniqueux_update_points_callback' );

add_action( 'wp_ajax_nopriv_uniqueux_update_points', 'uniqueux_update_points_callback' );


add_action('wp_ajax__ajax_fetch_country_list', '_ajax_fetch_country_list_callback');

add_action('wp_ajax__ajax_fetch_visitor_list', '_ajax_fetch_visitor_list_callback');

function _ajax_fetch_visitor_list_callback() {

	$wp_list_table = new Uniqueux_Visitor_Rank_Table();
	$wp_list_table->ajax_response();
}


function _ajax_fetch_country_list_callback() {

	$wp_list_table = new Uniqueux_Visitors_Country_Table();
	$wp_list_table->ajax_response();
}

function ajax_script() {
?>
<script type="text/javascript">
(function($) {
list_country = {
	init: function() {
		var timer;
		var delay = 500;
		$('#country-list .tablenav-pages a, #country-list .manage-column.sortable a, #country-list .manage-column.sorted a').on('click', function(e) {
			e.preventDefault();
			var query = this.search.substring( 1 );
			
			var data = {
				paged: list_country.__query( query, 'paged' ) || '1',
				order: list_country.__query( query, 'order' ) || 'asc',
				orderby: list_country.__query( query, 'orderby' ) || 'name_country'
			};
			list_country.update( data );
		});
		$('#country-list input[name=paged]').on('keyup', function(e) {
			if ( 13 == e.which )
				e.preventDefault();
			var data = {
				paged: parseInt( $('#country-list input[name=paged]').val() ) || '1',
				order: $('#country-list input[name=order]').val() || 'asc',
				orderby: $('#country-list input[name=orderby]').val() || 'title'
			};
			window.clearTimeout( timer );
			timer = window.setTimeout(function() {
				list_country.update( data );
			}, delay);
		});
	},
	update: function( data ) {
		$.ajax({
			url: ajaxurl,
			data: $.extend(
				{
					_ajax_country_list_nonce: $('#country-list #_ajax_country_list_nonce').val(),
					action: '_ajax_fetch_country_list',
				},
				data
			),
			success: function( response ) {
				var response = $.parseJSON( response );
				if ( response.rows.length )
					$('#country-list #the-list').html( response.rows );
				if ( response.column_headers.length )
					$('#country-list thead tr,#country-list tfoot tr').html( response.column_headers );
				if ( response.pagination.bottom.length )
					$('#country-list .tablenav.top .tablenav-pages').html( response.pagination.top );
				if ( response.pagination.top.length )
					$('#country-list .tablenav.bottom .tablenav-pages').html( response.pagination.bottom );
				list_country.init();
			}
		});
	},
	__query: function( query, variable ) {

		var vars = query.split("&");
		for ( var i = 0; i <vars.length; i++ ) {
			var pair = vars[ i ].split("=");
			if ( pair[0] == variable )
				return pair[1];
		}
		return false;
	},
}
list_country.init();
})(jQuery);


(function($) {
list_visitor = {
	init: function() {
		var timer;
		var delay = 500;
		$('#visitor-list .tablenav-pages a, #visitor-list .manage-column.sortable a, #visitor-list .manage-column.sorted a').on('click', function(e) {
			e.preventDefault();
			var query = this.search.substring( 1 );
			
			var data = {
				paged: list_visitor.__query( query, 'paged' ) || '1',
				order: list_visitor.__query( query, 'order' ) || 'asc',
				orderby: list_visitor.__query( query, 'orderby' ) || 'visitor_id'
			};
			list_visitor.update( data );
		});
		$('#visitor-list input[name=paged]').on('keyup', function(e) {
			if ( 13 == e.which )
				e.preventDefault();
			var data = {
				paged: parseInt( $('#visitor-list input[name=paged]').val() ) || '1',
				order: $('#visitor-list input[name=order]').val() || 'asc',
				orderby: $('#visitor-list input[name=orderby]').val() || 'title'
			};
			window.clearTimeout( timer );
			timer = window.setTimeout(function() {
				list_visitor.update( data );
			}, delay);
		});
	},
	update: function( data ) {
		$.ajax({
			url: ajaxurl,
			data: $.extend(
				{
					_ajax_visitor_list_nonce: $('#visitor-list #_ajax_visitor_list_nonce').val(),
					action: '_ajax_fetch_visitor_list',
				},
				data
			),
			success: function( response ) {
				var response = $.parseJSON( response );
				if ( response.rows.length )
					$('#visitor-list #the-list').html( response.rows );
				if ( response.column_headers.length )
					$('#visitor-list thead tr,#visitor-list tfoot tr').html( response.column_headers );
				if ( response.pagination.bottom.length )
					$('#visitor-list .tablenav.top .tablenav-pages').html( response.pagination.top );
				if ( response.pagination.top.length )
					$('#visitor-list .tablenav.bottom .tablenav-pages').html( response.pagination.bottom );
				list_visitor.init();
			}
		});
	},
	__query: function( query, variable ) {

		var vars = query.split("&");
		for ( var i = 0; i <vars.length; i++ ) {
			var pair = vars[ i ].split("=");
			if ( pair[0] == variable )
				return pair[1];
		}
		return false;
	},
}
list_visitor.init();
})(jQuery);


</script>
<?php
}
add_action('admin_footer', 'ajax_script');

function uniqueux_update_points_callback() {
	global $wpdb;
	global $cookie_val;
	$data = array();
	if(sanitize_text_field($_COOKIE['uniqueux_visitor']) == '') return;
	$_COOKIE['uniqueux_visitor'] = sanitize_text_field($_COOKIE['uniqueux_visitor']);
	
	$group = trim(sanitize_text_field($_POST['group']));
	
	if($_POST['type'] == 'addgroupPoints'){
				
		if($group!=''){
		
		$group_points = $wpdb->get_var('SELECT user_group_'.esc_sql($group).' FROM '.$wpdb->prefix.'uniqueux_visitors 
															WHERE visitor_cookie="'.esc_sql($_COOKIE['uniqueux_visitor']).'" ');
		
		if(isset($group_points) && $group_points != ''){
			$points = intval($_POST['points']);
				if($points){
					$group_points = $group_points + $points;
				
				
					$wpdb->update( 
						$wpdb->prefix.'uniqueux_visitors', 
						array( 
							'user_group_'.esc_sql($group) => $group_points
						), 
						array( 'visitor_cookie' => esc_sql($_COOKIE['uniqueux_visitor']) )
					);
				}
			}
		}
				
	}else if($_POST['type'] == 'removegroupPoints'){
		
		if($group!=''){
		
			$group_points = $wpdb->get_var('SELECT user_group_'.esc_sql($group).' FROM '.$wpdb->prefix.'uniqueux_visitors 
														WHERE visitor_cookie="'.esc_sql($_COOKIE['uniqueux_visitor']).'" ');
			
			if(isset($group_points) && $group_points != ''){
								
				$group_points = $group_points - $_POST['points'];
				
				$wpdb->update( 
						$wpdb->prefix.'uniqueux_visitors', 
						array( 
							'user_group_'.esc_sql($group) => $group_points
						), 
						array( 'visitor_cookie' => esc_sql($_COOKIE['uniqueux_visitor']) )
					);
			}
		
		}
		
	}else if($_POST['type'] == 'removegroup'){
		
		if($group!=''){
		
			$group_points = $wpdb->get_var('SELECT user_group_'.esc_sql($group).' FROM '.$wpdb->prefix.'uniqueux_visitors 
													WHERE visitor_cookie="'.esc_sql($_COOKIE['uniqueux_visitor']).'" ');
			
			if(isset($group_points) && $group_points != ''){
		
				$group_points = 0;
				
				$wpdb->update( 
						$wpdb->prefix.'uniqueux_visitors', 
						array( 
							'user_group_'.esc_sql($group) => $group_points
						), 
						array( 'visitor_cookie' => esc_sql($_COOKIE['uniqueux_visitor']) )
					);
			}
		
		}
		
	}
	wp_die();
}


function uniqueux_del_schedule_callback() {
	global $wpdb;
	$schedule_id = intval($_POST['schedule_id']);
	if($schedule_id){
		$wpdb->delete( $wpdb->prefix.'uniqueux_schedule', array( 'schedule_id' => esc_sql($schedule_id) ) );	
		$wpdb->delete( $wpdb->prefix.'uniqueux_content', array( 'schedule_id' => esc_sql($schedule_id) ) );		
	}
	wp_die();
}

function uniqueux_del_device_callback() {
	global $wpdb;
	$device_id = intval($_POST['device_id']);
	if($device_id){
		$wpdb->delete( $wpdb->prefix.'uniqueux_devices', array( 'device_id' => esc_sql($device_id) ) );	
		$wpdb->delete( $wpdb->prefix.'uniqueux_content', array( 'device_id' => esc_sql($device_id) ) );		
	}
	wp_die();
}


function uniqueux_pixert_upload_types($existing_mimes=array()){
	$existing_mimes['mmdb'] = 'mmdb';
	return $existing_mimes;
}


function uniqueux_init(){

	global $wpdb;
	global $post;
	global $cookie_val;
	
	if(isset($_COOKIE['uniqueux_visitor'])){
		$cookie_val = sanitize_text_field($_COOKIE['uniqueux_visitor']);
	}

	$main = get_post_meta( $post->ID, 'user_group_main', true );
	$related = get_post_meta( $post->ID, 'user_group_related', true );

	$main_array = array();
	$related_array = array();

	if($main != ''){
		$main_val = substr($main, 1, -1);
		if(strlen($main_val) == 1){
			array_push($main_array, $main_val);
		}else{
			$main_array = explode(',',$main_val);
		}
	}

	if($related != ''){
		$related_val = substr($related, 1, -1);
		if(strlen($related_val) == 1){
			array_push($related_array, $related_val);
		}else{
			$related_array = explode(',',$related_val);
		}
	}

	
		$results = $wpdb->get_results('SELECT user_group_id FROM '.$wpdb->prefix.'uniqueux_user_group');

		if(count($results) > 0){
			foreach ($results as $value) {
					
					$group_points = $wpdb->get_var('SELECT user_group_'.$value->user_group_id.' FROM '.$wpdb->prefix.'uniqueux_visitors WHERE visitor_cookie="'.esc_sql($cookie_val).'" ');
					
					$output_val = $group_points;

					if(in_array($value->user_group_id , $main_array)){
						$output_val = $output_val+get_option('uniqueux_main_points');
					}

					if(in_array($value->user_group_id , $related_array)){
						$output_val = $output_val+get_option('uniqueux_related_points');
					}
					
					if($_COOKIE['uniqueux_track_latest'] == 1){
						$wpdb->update( 
							$wpdb->prefix.'uniqueux_visitors', 
							array( 
								'user_group_'.$value->user_group_id => $output_val,
								'latest_time' => date('Y-m-d H:i:s')
							), 
							array( 'visitor_cookie' => esc_sql($cookie_val) )
						);
					}else{
						$wpdb->update( 
							$wpdb->prefix.'uniqueux_visitors', 
							array( 
								'user_group_'.$value->user_group_id => $output_val 
							), 
							array( 'visitor_cookie' => esc_sql($cookie_val) )
						);
					}
			}

		}
		
		
		if(!is_admin() && !is_404()){
			if($_COOKIE['uniqueux_track_latest'] == 1){
				
				$page_view = $wpdb->get_var("SELECT page_views FROM ".$wpdb->prefix."uniqueux_visitors WHERE visitor_cookie='".esc_sql($cookie_val)."' ");
							
				$wpdb->update( 
							$wpdb->prefix.'uniqueux_visitors', 
							array( 
								'page_views' => intval($page_view)+1
							), 
							array( 'visitor_cookie' => esc_sql($cookie_val) )
						);
			}
		}
		
}

function uniqueux_main_process(){

	global $wpdb;
	global $cookie_val;
	global $reader;
	
	//if(sanitize_text_field($_COOKIE['uniqueux_visitor']) == '') return; //
	$_COOKIE['uniqueux_visitor'] = sanitize_text_field($_COOKIE['uniqueux_visitor']);
	
	
	if(strpos($_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php') !== false) return;
	
	if(strlen($_SERVER['HTTP_USER_AGENT']) < 25) return;
	
	if(preg_match("/googlebot|slurp|msnbot|uptimerobot|wordpress/i", strtolower($_SERVER['HTTP_USER_AGENT']))) return;
	
	$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
	
	if(strpos($http_user_agent,'Googlebot') > 0 || strpos($http_user_agent,'UptimeRobot') > 0 || strpos($http_user_agent,'msnbot') > 0 || strpos($http_user_agent,'slurp') > 0) return;
	
	if(isset($_COOKIE['uniqueux_visitor'])){
		//get database
		$count_visitors = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."uniqueux_visitors WHERE visitor_cookie='".esc_sql($_COOKIE['uniqueux_visitor'])."' ");
		if($count_visitors == 0){
			
			if(get_option('uniqueux_geoip_file') && uniqueux_get_client_ip() != ''){
				try {
					$record = $reader->country(uniqueux_get_client_ip());			
							
							if($record->country->isoCode != ''){
								$rRecord = $wpdb->get_results('SELECT user_group_id,points FROM '.$wpdb->prefix.'uniqueux_user_group WHERE country="'.$record->country->isoCode.'" ');
								if(count($rRecord) > 0){
									foreach ($rRecord as $value) {
										$geoGroupId = $value->user_group_id;
										$geoPoint = $value->points;
									}
								}
							}
							
							$name = $record->country->names;
							$country_name = $name['en'];
											
				} catch (Exception $e) {
					$country_name = 'Unknown';
				}
				
	
			}
			
			unset($_COOKIE['uniqueux_visitor']);

		}
	}
	
		if((!isset($_COOKIE['uniqueux_visitor'])) || $_COOKIE['uniqueux_visitor'] == ''){
						
			
			$cookie = md5(strtotime(date('Y-m-d H:i:s')));
			$cookie_val = $cookie;
	
			$data = array();
	
			$geoGroupId = 0;
			$geoPoint = 0;
					
			if(get_option('uniqueux_geoip_file') && uniqueux_get_client_ip() != ''  && is_object($record) && get_option('uniqueux_geoip_phar_file')){
				try {
					$record = $reader->country(uniqueux_get_client_ip());			
							
							if($record->country->isoCode != ''){
								$rRecord = $wpdb->get_results('SELECT user_group_id,points FROM '.$wpdb->prefix.'uniqueux_user_group WHERE country="'.$record->country->isoCode.'" ');
								if(count($rRecord) > 0){
									foreach ($rRecord as $value) {
										$geoGroupId = $value->user_group_id;
										$geoPoint = $value->points;
									}
								}
							}
							
							$name = $record->country->names;
							$country_name = $name['en'];
											
				} catch (Exception $e) {
					$country_name = 'Unknown';
				}
				
	
			}
			
			setcookie('uniqueux_visitor', $cookie, time()+(3600*24*365*100), '/' );
			
			if(get_option('uniqueux_track_latest') == 1){
				setcookie('uniqueux_track_latest', 1, time()+(3600*24*365*100), '/' );
			}else{
				setcookie('uniqueux_track_latest', 0, time()+(3600*24*365*100), '/' );
			}
					
			$wpdb->insert(
						$wpdb->prefix.'uniqueux_visitors', 
						array( 
							'visitor_cookie' => $cookie,
							'useragent' => $_SERVER['HTTP_USER_AGENT'],
							'first_time' => date('Y-m-d H:i:s'),
							'latest_time' => date('Y-m-d H:i:s'),
							'country' => ($country_name) ? $country_name : 'Unknown'
						)
					);
					
	
			$results = $wpdb->get_results('SELECT user_group_id FROM '.$wpdb->prefix.'uniqueux_user_group');
	
			if(count($results) > 0){
				foreach ($results as $value) {
					if($geoGroupId == $value->user_group_id){
						$wpdb->update(
							$wpdb->prefix.'uniqueux_visitors', 
							array( 
								'user_group_'.$value->user_group_id => $geoPoint
							),
							array( 
								'visitor_cookie' => esc_sql($cookie)
							)
						);
					}else{
						$wpdb->update(
							$wpdb->prefix.'uniqueux_visitors', 
							array( 
								'user_group_'.$value->user_group_id => 0
							),
							array( 
								'visitor_cookie' => esc_sql($cookie)
							)
						);
					}
				}
			}
				
		}

}


function uniqueux_getReturnBrowser($u_agent) 
	{ 
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "";	
		$ub = "";

	    if (preg_match('/linux/i', $u_agent)) {
	        $platform = 'Linux';
	    }
	    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	        $platform = 'Mac';
	    }
	    elseif (preg_match('/windows|win32/i', $u_agent)) {
	        $platform = 'Windows';
	    }
	    
	    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
	    { 
	        $bname = 'Internet Explorer'; 
	        $ub = "MSIE"; 
	    } 
	    elseif(preg_match('/Firefox/i',$u_agent)) 
	    { 
	        $bname = 'Mozilla Firefox'; 
	        $ub = "Firefox"; 
	    } 
	    elseif(preg_match('/Chrome/i',$u_agent)) 
	    { 
	        $bname = 'Google Chrome'; 
	        $ub = "Chrome"; 
	    } 
	    elseif(preg_match('/Safari/i',$u_agent)) 
	    { 
	        $bname = 'Apple Safari'; 
	        $ub = "Safari"; 
	    } 
	    elseif(preg_match('/Opera/i',$u_agent)) 
	    { 
	        $bname = 'Opera'; 
	        $ub = "Opera"; 
	    } 
	    elseif(preg_match('/Netscape/i',$u_agent)) 
	    { 
	        $bname = 'Netscape'; 
	        $ub = "Netscape"; 
	    } 
	    
	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?<browser>' . join('|', $known) .
	    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    if (!preg_match_all($pattern, $u_agent, $matches)) {
	    }
	    
	    $i = count($matches['browser']);
	    if ($i != 1) {
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				if(isset($matches['version'][0])){
	            	$version = $matches['version'][0];
				}
	        } else {
				if(isset($matches['version'][1])){
	            	$version = $matches['version'][1];
				}
	        }
	    }
	    else {
			if(isset($matches['version'][0])){
	        	$version = $matches['version'][0];
			}
	    }
	    
	    // check if we have a number
	    if ($version==null || $version=="") {$version="?";}
	    
	    return array(
	        'userAgent' => $u_agent,
	        'name'      => $bname,
	        'version'   => $version,
	        'platform'  => $platform,
	        'pattern'    => $pattern
	    );
} 



function uniqueux_get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    if(!filter_var($ipaddress, FILTER_VALIDATE_IP)){
    	$ipaddress = '';
    }

    return $ipaddress;
}


?>
