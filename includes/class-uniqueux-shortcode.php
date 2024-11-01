<?php

	function uniqueux_shortcode_country($atts){

		global $reader;

		$html = '';

		if(get_option('uniqueux_geoip_file') && uniqueux_get_client_ip() != '' && is_object($record) && get_option('uniqueux_geoip_phar_file')){

			$record = $reader->country(uniqueux_get_client_ip());

			$isoCode = $record->country->isoCode;

			$name = $record->country->names;

			$html .= $name['en'];
			
		}

		return $html;
	}

	function uniqueux_shortcode_browser($atts){

		$html = '';

		$browser = uniqueux_getBrowser();

		$html .= $browser['name'];

		return $html;
	}

	function uniqueux_shortcode_ip($atts){

		$html = '';

		$html .= uniqueux_get_client_ip();

		return $html;
	}

	function uniqueux_shortcode_searchengine($atts){

		$html = '';

		$html .= parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);

		return $html;
	}

	function uniqueux_shortcode_searchphrase($atts){

		$html = '';

		if(uniqueux_get_search_keyword() != ''){
			$html .= uniqueux_get_search_keyword();
		}else{
			$html .= $atts['default'];
		}

		return $html;
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


	function uniqueux_shortcode_func($atts){
		
		global $wpdb;
		global $cookie_val;

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
															WHERE PC.content_group_id="'.$atts['id'].'" 
															AND PC.user_group_id="'.esc_sql($user_group_id).'" 
															AND PS.date_start <= "'.$local_time.'" 
															AND PS.date_end >= "'.$local_time.'" ');										
		}else{

			$results = $wpdb->get_results('SELECT PC.content_id,PC.device_id,PC.content_html FROM '.$wpdb->prefix.'uniqueux_content PC 
															LEFT JOIN '.$wpdb->prefix.'uniqueux_schedule PS ON(PC.schedule_id=PS.schedule_id) 
															WHERE PC.content_group_id="'.$atts['id'].'" 
															AND PC.user_group_id="0" 
															AND PS.date_start <= "'.$local_time.'" 
															AND PS.date_end >= "'.$local_time.'" ');
		}
		
		if(count($results) == 0){
			if($user_group_id > 0){
				$results = $wpdb->get_results('SELECT content_id,device_id,content_html FROM '.$wpdb->prefix.'uniqueux_content 
																WHERE content_group_id="'.$atts['id'].'" 
																AND user_group_id="'.esc_sql($user_group_id).'" 
																AND schedule_id=0 ');						
			}else{
				$results = $wpdb->get_results('SELECT content_id,device_id,content_html FROM '.$wpdb->prefix.'uniqueux_content 
																WHERE content_group_id="'.$atts['id'].'" 
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
		
		return do_shortcode($html);
	}

	function uniqueux_get_search_keyword()
	{
	    $ref_keywords = '';
	  
	    $referrer = $_SERVER['HTTP_REFERER'];
	    if (!empty($referrer))
	    {
	        $parts_url = parse_url($referrer);
	 
	        $query = isset($parts_url['query']) ? $parts_url['query'] : '';
	        if($query)
	        {
	            parse_str($query, $parts_query);
	            $ref_keywords = isset($parts_query['q']) ? $parts_query['q'] : (isset($parts_query['query']) ? $parts_query['query'] : '' );
	        }
	    }
	    return $ref_keywords;
	}


	function uniqueux_getBrowser() 
	{ 
	    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "";

	    //First get the platform?
	    if (preg_match('/linux/i', $u_agent)) {
	        $platform = 'linux';
	    }
	    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	        $platform = 'mac';
	    }
	    elseif (preg_match('/windows|win32/i', $u_agent)) {
	        $platform = 'windows';
	    }
	    
	    // Next get the name of the useragent yes seperately and for good reason
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
	    
	    // finally get the correct version number
	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?<browser>' . join('|', $known) .
	    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    if (!preg_match_all($pattern, $u_agent, $matches)) {
	        // we have no matching number just continue
	    }
	    
	    // see how many we have
	    $i = count($matches['browser']);
	    if ($i != 1) {
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	            $version= $matches['version'][0];
	        }
	        else {
	            $version= $matches['version'][1];
	        }
	    }
	    else {
	        $version= $matches['version'][0];
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

?>
