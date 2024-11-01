<?php

class Uniqueux_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'uniqueux_widget', // Base ID
			__( 'Uniqueux content', 'text_domain' ), // Name
			array( 'description' => __( 'Uniqueux content', 'text_domain' ), ) // Args
		);
	}
	
	function uniqueux_getOS() { 
			
		$user_agent     =   strtolower($_SERVER['HTTP_USER_AGENT']);
	
		$os_platform    =   "Unknown OS Platform";
							
		$os_array       =   array(
								'/windows nt 10/i'     =>  'windows',
								'/windows nt 6.3/i'     =>  'windows',
								'/windows nt 6.2/i'     =>  'windows',
								'/windows nt 6.1/i'     =>  'windows',
								'/windows nt 6.0/i'     =>  'windows',
								'/windows nt 5.2/i'     =>  'windows',
								'/windows nt 5.1/i'     =>  'windows',
								'/windows xp/i'         =>  'windows',
								'/windows nt 5.0/i'     =>  'windows',
								'/windows me/i'         =>  'windows',
								'/win98/i'              =>  'windows',
								'/win95/i'              =>  'windows',
								'/win16/i'              =>  'windows',
								'/windows phone/i'      =>  'windowsphone',
								'/windows (phone|ce)/i' =>  'windowsphone',
								'/macintosh|mac os x/i' =>  'mac',
								'/mac_powerpc/i'        =>  'mac',
								'/linux/i'              =>  'linux',
								'/ubuntu/i'             =>  'linux',
								'/iphone/i'             =>  'iphone',
								'/ipod/i'               =>  'ipod',
								'/ipad/i'               =>  'ipad',
								'/android/i'            =>  'android',
								'/blackberry/i'         =>  'blackberry',
								'/webos/i'              =>  'mobile'
							);					
	
		foreach ($os_array as $regex => $value) { 
	
			if (preg_match($regex, $user_agent)) {
				$os_platform    =   $value;
			}
	
		}   
		return $os_platform;
	}

	public function widget( $args, $instance ) {

		global $wpdb;
		global $cookie_val;

		echo $args['before_widget'];

		$html = '';
		$user_group_id = 0;
		$data_array = array();

		if(!isset($_COOKIE['uniqueux_visitor'])){
			$_COOKIE['uniqueux_visitor'] = sanitize_text_field($cookie_val);
		}
		
		if(isset($_COOKIE['uniqueux_visitor'])){
			$result_user_group = $wpdb->get_results('SELECT user_group_id FROM '.$wpdb->prefix.'uniqueux_user_group');
			if(count($result_user_group) > 0){
				foreach($result_user_group as $value) {
					$user_group_point = $wpdb->get_var('SELECT user_group_'.$value->user_group_id.' FROM '.$wpdb->prefix.'uniqueux_visitors 
																WHERE visitor_cookie="'.esc_sql($_COOKIE['uniqueux_visitor']).'" ');
																																
					if($user_group_point > 0){
								for($m=1;$m <= $user_group_point;$m++){
									array_push($data_array, $value);
								}
							}
				}
			}
			if(count($data_array)){	
				$rand = array_rand($data_array);
						
				$random = $data_array[$rand];
				$user_group_id = $random->user_group_id;	
			}		
		}
		
		$dbValue = date('Y-m-d H:i:s'); 
		$timestamp = strtotime($dbValue) - (((get_option('gmt_offset')*-1)*60)*60);
		$local_time = date("Y-m-d H:i:s", $timestamp);	

		
		if($user_group_id > 0){
			$results = $wpdb->get_results('SELECT PC.content_id,PC.device_id,PC.content_html FROM '.$wpdb->prefix.'uniqueux_content PC 
															LEFT JOIN '.$wpdb->prefix.'uniqueux_schedule PS ON(PC.schedule_id=PS.schedule_id) 
															WHERE PC.content_group_id="'.esc_sql($instance['content_id']).'" 
															AND PC.user_group_id="'.esc_sql($user_group_id).'" 
															AND PS.date_start <= "'.$local_time.'" 
															AND PS.date_end >= "'.$local_time.'" ');
		}else{

			$results = $wpdb->get_results('SELECT PC.content_id,PC.device_id,PC.content_html FROM '.$wpdb->prefix.'uniqueux_content PC 
															LEFT JOIN '.$wpdb->prefix.'uniqueux_schedule PS ON(PC.schedule_id=PS.schedule_id) 
															WHERE PC.content_group_id="'.esc_sql($instance['content_id']).'" 
															AND PC.user_group_id="0" 
															AND PS.date_start <= "'.$local_time.'" 
															AND PS.date_end >= "'.$local_time.'" ');
															
															
		}
		
		if(count($results) == 0){
			if($user_group_id > 0){
				$results = $wpdb->get_results('SELECT content_id,device_id,content_html FROM '.$wpdb->prefix.'uniqueux_content 
																WHERE content_group_id="'.esc_sql($instance['content_id']).'" 
																AND user_group_id="'.esc_sql($user_group_id).'" 
																AND schedule_id=0 ');	
			}else{
				$results = $wpdb->get_results('SELECT content_id,device_id,content_html FROM '.$wpdb->prefix.'uniqueux_content 
																WHERE content_group_id="'.esc_sql($instance['content_id']).'" 
																AND user_group_id="0" 
																AND schedule_id=0 ');
			}
		}
		
		if(count($results) > 0){
			foreach ($results as $value) {
				if($value->device_id > 0){
						$result_2 = $wpdb->get_results('SELECT PC.content_html FROM '.$wpdb->prefix.'uniqueux_content PC
																LEFT JOIN '.$wpdb->prefix.'uniqueux_devices PD ON(PD.device_id=PC.device_id) 
																WHERE content_id='.$value->content_id.' 
																AND device_list LIKE "%'.uniqueux_getOS().'%" ');
																
						if(count($result_2) > 0){
							$html = stripslashes($result_2[0]->content_html);
							break;
						}
				}else{
					$default_html = stripslashes($value->content_html);
				}
			}
			
			if($html == ''){
				$html = $default_html;
			}
		}else{
			$html = '';
		}

		echo do_shortcode($html);
		
		echo $args['after_widget'];

	}

	public function form( $instance ) {
		$content_id = ! empty( $instance['content_id'] ) ? $instance['content_id'] : __( 'Uniqueux content', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'content_id' ); ?>"><?php _e( 'Content ID :' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'content_id' ); ?>" name="<?php echo $this->get_field_name( 'content_id' ); ?>" type="number" value="<?php echo esc_attr( $content_id ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['content_id'] = ( ! empty( $new_instance['content_id'] ) ) ? strip_tags( $new_instance['content_id'] ) : '';
		return $instance;
	}

}
