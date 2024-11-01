<?php

function uniqueux_control_menu() {
	$name = get_uniqueux_content_name();
  add_menu_page( $name, $name, 'manage_options', 'uniqueux', 'uniqueux_dashboard', plugins_url( 'unique-ux/images/icon.png' ), 6 );
  add_submenu_page('uniqueux', 'User group', 'User group', 'manage_options', 'uniqueux_user_group', 'uniqueux_user_group');
  add_submenu_page('uniqueux', 'Content group', 'Content group', 'manage_options', 'uniqueux_content_group', 'uniqueux_content_group');
  add_submenu_page('uniqueux', 'Settings', 'Settings', 'manage_options', 'uniqueux_settings', 'uniqueux_settings');
}


function uniqueux_dashboard(){
	$product_name = get_uniqueux_content_name();
	echo '<div class="wrap" style="float: left;">';
	echo '<h1>'.$product_name.'</h1>';
	
	echo '<div style="width:45%;float:left;">';
	$userGroupTable = new Uniqueux_Content_Group_Table();
	$userGroupTable->prepare_items();
	echo '<h4>Content group</h4>';
	$userGroupTable->display();
	echo '</div>';
	
	echo '<div style="width:45%;float:right;">';
	$userGroupTable = new Uniqueux_Visitor_Stats_Table();
	$userGroupTable->prepare_items();
	echo '<h4>Visitor stats</h4>';
	$userGroupTable->display();
	echo '</div>';
	
	echo '<div style="width:100%;float:left;">&nbsp;</div>';
	
	echo '<div style="width:30%;margin-right:5%;float:left;">';
	$userGroupTable = new Uniqueux_Visitors_OS_Table();
	$userGroupTable->prepare_items();
	echo '<h4>Visitor OS</h4>';
	$userGroupTable->display();
	echo '</div>';
	
	
	echo '<div style="width:30%;float:left;">';
	$userGroupTable = new Uniqueux_Visitors_Browser_Table();
	$userGroupTable->prepare_items();
	echo '<h4>Visitor Browser</h4>';
	$userGroupTable->display();
	echo '</div>';
	
	echo '<div id="country-list" style="width:30%;float:right;">';
	$userGroupTable = new Uniqueux_Visitors_Country_Table();
	$userGroupTable->prepare_items();
	echo '<h4>Visitor Country</h4>';
	//$userGroupTable->display();
	echo '<form id="country-filter" method="get">
			'.$userGroupTable->display().'
		</form>';
	echo '</div>';
	
	
	echo '<div id="visitor-list" style="width:100%;float:left;">';
	$userGroupTable = new Uniqueux_Visitor_Rank_Table();
	$userGroupTable->prepare_items();
	echo '<h4>Visitor Rank</h4>';
	echo '<form id="movies-filter" method="get">
			'.$userGroupTable->display().'
		</form>
		</div>';
		
	
	echo '</div>';
}

function get_uniqueux_content_name(){
	$name = apply_filters('get_uniqueux_content_product_name', 'Unique Ux');
	return $name;
}

function uniqueux_settings(){
	
	if(isset($_POST['main_points'])) $main_points = intval($_POST['main_points']);

	if(isset($main_points)){
		update_option( 'uniqueux_main_points', $main_points );
	}
	
	if(isset($_POST['related_points'])) $related_points = intval($_POST['related_points']);

	if(isset($related_points)){
		update_option( 'uniqueux_related_points', $related_points );
	}
	
	if(isset($_POST['track_latest'])) $track_latest = intval($_POST['track_latest']);
	
	if(isset($_POST['form_update'])){
		update_option('uniqueux_track_latest', (isset($track_latest)) ? $track_latest : 0);
	}

	if(isset($_POST['geoip_file']) && $_POST['geoip_file'] != ''){
		
		$upload_dir = wp_upload_dir();
		
		$geoip_file = sanitize_text_field($_POST['geoip_file']);
		
		if($_POST['geoip_file'] == $geoip_file){
			$path = explode('uploads', $geoip_file);
			$mmdbPath = $upload_dir['basedir'].$path[count($path)-1];
			update_option( 'uniqueux_geoip_file', $mmdbPath );
		}
	}
	
	if(isset($_POST['geoip_phar_file']) && $_POST['geoip_phar_file'] != ''){
		
		$upload_dir = wp_upload_dir();
		
		$geoip_phar_file = sanitize_text_field($_POST['geoip_phar_file']);

		if($_POST['geoip_phar_file'] == $geoip_phar_file){
			$path = explode('uploads', $_POST['geoip_phar_file']);
	
			$pharPath = $upload_dir['basedir'].$path[count($path)-1];
	
			update_option( 'uniqueux_geoip_phar_file', $pharPath );
		}
	}

	echo '<div class="wrap">';
	echo '<h2>Settings</h2>';
	echo '<form method="post" action="'.admin_url( 'admin.php?page=uniqueux_settings').'">';
			echo '<table class="form-table">
						<tr>
							<th scope="row">Main points</th>
							<td>
								<input type="number" placeholder="0" name="main_points" value="'.get_option('uniqueux_main_points').'" />
							</td>
						</tr>
						<tr>
							<th scope="row">Related points</th>
							<td>
								<input type="number" placeholder="0" name="related_points" value="'.get_option('uniqueux_related_points').'" />
							</td>
						</tr>
						<tr>
							<th scope="row">GeoIP DB File (Upload)</th>
							<td>';
							if(get_option('uniqueux_geoip_file')){
								echo	'<span style="color:green;" id="geoip_file_name">Uploaded</span>';
							}else{
								echo	'<span style="color:red;" id="geoip_file_name">Please upload GeoIP DB File</span>';
							}
			echo				'<input type="hidden" name="geoip_file" id="geoip_file" value="" />
            					<button class="set_custom_images button">Upload file</button>
            					<br />
            					<span><a href="http://dev.maxmind.com/geoip/geoip2/geolite2/" target="_blank">http://dev.maxmind.com/geoip/geoip2/geolite2/</a></span><br />
								<span>GeoLite2 Country</span><br />
								<span>MaxMind DB version.</span>
							</td>
						</tr>
						
						<tr>
							<th scope="row">GeoIP Phar File (Upload)</th>
							<td>';
							if(get_option('uniqueux_geoip_phar_file')){
								echo	'<span style="color:green;" id="geoip_phar_file_name">Uploaded</span>';
							}else{
								echo	'<span style="color:red;" id="geoip_phar_file_name">Please upload GeoIP Phar File</span>';
							}
			echo				'<input type="hidden" name="geoip_phar_file" id="geoip_phar_file" value="" />
            					<button class="set_custom_images_phar button">Upload file</button>
            					<br />
            					<span><a href="https://github.com/maxmind/GeoIP2-php/releases" target="_blank">https://github.com/maxmind/GeoIP2-php/releases</a></span><br />
							</td>
						</tr>
						
						<tr>
							<th scope="row">Track latest visit time and page views</th>
							<td>
								<input type="checkbox" placeholder="0" name="track_latest" value="1" '.(get_option('uniqueux_track_latest') == 1 ? 'checked="checked"' : '').' />&nbsp;
								Can cause performance decrease on busy sites
							</td>
						</tr>
						<tr>
							<th scope="row">
							<input type="hidden" name="form_update" value="1" />
							<input type="submit" value="Save Changes" class="button button-primary" />
							</th>
							<td>
							</td>
						</tr>
				</table>';
	echo '</div>';
	
}


function uniqueux_content_group(){
		global $wpdb;
		
		$schedule_id;

		if(isset($_POST['schedule_id']) && $_POST['schedule_id'] != ''){ 
			$schedule_id = intval($_POST['schedule_id']);
		}else{ 
			$schedule_id = 0;
		}
		
		if(isset($_POST['edit_id'])){		
		
			$edit_id = intval($_POST['edit_id']);
			
			$content_group_name = sanitize_text_field($_POST['content_group_name']);
			
			$device_name = sanitize_text_field($_POST['device_name']);
			
			$schedule_name = sanitize_text_field($_POST['schedule_name']);
			
			$content_default_html = $_POST['content_default_html'];
			
			$content_group_id = intval($_POST['content_group_id']);
		
			if($edit_id > 0){

				$wpdb->update( 
					$wpdb->prefix.'uniqueux_content_group', 
					array( 
						'content_group_name' => esc_sql($content_group_name)
					), 
					array( 'content_group_id' => esc_sql($edit_id) )
				);
				
				
				if(isset($_POST['devices']) && count($_POST['devices'])){
						if($_POST['device_id'] >= 0){
							
							$wpdb->update( 
											$wpdb->prefix.'uniqueux_devices', 
											array( 
												'device_name' => esc_sql($device_name),
												'device_list' => implode(',',$_POST['devices']),
												'content_group_id' => esc_sql($edit_id)	
											), 
											array( 
												'device_id' => intval($_POST['device_id']) 
											)
										);
							$device_id = intval($_POST['device_id']);			
							
						}else if($_POST['device_id'] == '-1'){
							
							$wpdb->insert( 
									$wpdb->prefix.'uniqueux_devices', 
									array( 
										'device_name' => esc_sql($device_name),
										'device_list' => implode(',',$_POST['devices']),
										'content_group_id' => esc_sql($edit_id)
									)
								);
								
							$device_id = intval($wpdb->insert_id);		
								
						}
					}
					
				if(!isset($_POST['device_id']) && !isset($device_id)){ 
					$device_id = 0;	
				}else if(!isset($device_id)){
					if(isset($_POST['device_id'])){ 
						$device_id = intval($_POST['device_id']);
					}else{
						$device_id = 0;	
					}
				}
				
				
				if($schedule_id >= 0){
					if($content_default_html != ''){
												
						$checkContent = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."uniqueux_content WHERE user_group_id=0 
													AND content_group_id='".esc_sql($edit_id)."' 
													AND schedule_id='".esc_sql($schedule_id)."' 
													AND device_id='".($device_id == '-1' ? 0 : $device_id)."' ");
						if($checkContent > 0){
							$wpdb->update( 
											$wpdb->prefix.'uniqueux_content', 
											array( 
												'content_html' => $content_default_html	
											), 
											array( 
												'user_group_id' => 0,
												'content_group_id' => esc_sql($edit_id),
												'schedule_id' => esc_sql($schedule_id),
												'device_id' => ($device_id == '-1' ? 0 : esc_sql($device_id))
											)
										);
						}else{
							$wpdb->insert( 
									$wpdb->prefix.'uniqueux_content', 
									array( 
										'content_html' => $content_default_html,
										'user_group_id' => 0,
										'content_group_id' => esc_sql($edit_id),
										'schedule_id' => esc_sql($schedule_id),
										'device_id' => ($device_id == '-1' ? 0 : esc_sql($device_id)) 	
									)
								);
						}
					}
					
					if(isset($_POST['date_start'])){
						$date_start = date('Y-m-d',strtotime(str_replace('/','-',$_POST['date_start']))).' '.$_POST['time_start'];
					}
					
					if(isset($_POST['date_end'])){
						$date_end = date('Y-m-d',strtotime(str_replace('/','-',$_POST['date_end']))).' '.$_POST['time_end'];
					}
										
					$wpdb->update( 
						$wpdb->prefix.'uniqueux_schedule', 
						array( 
							'schedule_name' => esc_sql($schedule_name),
							'content_group_id' => esc_sql($content_group_id),
							'date_start' => esc_sql($date_start),
							'date_end' => esc_sql($date_end)
						), 
						array( 'schedule_id' => esc_sql($schedule_id) )
					);
		
					$rUserGroup = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group');
					foreach ($rUserGroup as $value) {
	
						if(isset($_POST['content_html'][$value->user_group_id])){
							
							$content_check = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."uniqueux_content 
															WHERE user_group_id='".esc_sql($value->user_group_id)."' 
															AND schedule_id='".esc_sql($schedule_id)."' 
															AND device_id='".($device_id == '-1' ? 0 : esc_sql($device_id))."' " );
							if($content_check > 0){
								$wpdb->update( 
											$wpdb->prefix.'uniqueux_content', 
											array( 
												'content_html' => $_POST['content_html'][$value->user_group_id]
											), 
											array( 
												'user_group_id' => esc_sql($value->user_group_id),
												'content_group_id' => esc_sql($edit_id),
												'schedule_id' => esc_sql($schedule_id),
												'device_id' => ($device_id == '-1' ? 0 : esc_sql($device_id)) 
											)
										);
							}else{
								$wpdb->insert( 
									$wpdb->prefix.'uniqueux_content', 
									array( 
										'content_html' => $_POST['content_html'][$value->user_group_id],
										'user_group_id' => esc_sql($value->user_group_id),
										'content_group_id' => esc_sql($edit_id),
										'schedule_id' => esc_sql($schedule_id),
										'device_id' => ($device_id == '-1' ? 0 : esc_sql($device_id)) 	
									)
								);
							}
						}
	
					}
					
					
				}else if($schedule_id == '-1'){
					
					if(isset($_POST['date_start'])){
						$date_start = date('Y-m-d',strtotime(str_replace('/','-',$_POST['date_start']))).' '.$_POST['time_start'];
					}
					
					if(isset($_POST['date_end'])){
						$date_end = date('Y-m-d',strtotime(str_replace('/','-',$_POST['date_end']))).' '.$_POST['time_end'];
					}
					
					
					
					$wpdb->insert(
							$wpdb->prefix.'uniqueux_schedule', 
							array( 
								'schedule_name' => esc_sql($schedule_name),
								'content_group_id' => esc_sql($edit_id),
								'date_start' => esc_sql($date_start),
								'date_end' => esc_sql($date_end)
							)
						);
											
					$schedule_id = $wpdb->insert_id;						
					
					if($content_default_html != ''){
						$wpdb->insert( 
								$wpdb->prefix.'uniqueux_content', 
								array( 
									'content_html' => $content_default_html,
									'user_group_id' => 0,
									'content_group_id' => esc_sql($edit_id),
									'schedule_id' => esc_sql($schedule_id)	
								)
							);
					}
	
					$rUserGroup = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group');
					foreach ($rUserGroup as $value) {
	
						if(isset($_POST['content_html'][$value->user_group_id])){
							$wpdb->insert( 
									$wpdb->prefix.'uniqueux_content', 
									array( 
										'content_html' => $_POST['content_html'][$value->user_group_id],
										'user_group_id' => esc_sql($value->user_group_id),
										'content_group_id' => esc_sql($edit_id),
										'schedule_id' => esc_sql($schedule_id)	
									)
								);
						}
	
					}
				}

			}else if($_POST['edit_id'] == 0){

				$wpdb->insert( 
					$wpdb->prefix.'uniqueux_content_group', 
					array( 
						'content_group_name' => esc_sql($content_group_name)
					)
				);

				$content_group_id = intval($wpdb->insert_id);
				
				
				if(isset($_POST['schedule_id']) && $_POST['schedule_id'] == '-1'){
					if(isset($_POST['date_start'])){
						$date_start = date('Y-m-d',strtotime(str_replace('/','-',$_POST['date_start']))).' '.$_POST['time_start'];
					}
					
					if(isset($_POST['date_end'])){
						$date_end = date('Y-m-d',strtotime(str_replace('/','-',$_POST['date_end']))).' '.$_POST['time_end'];
					}
					
					$wpdb->insert(
							$wpdb->prefix.'uniqueux_schedule', 
							array( 
								'schedule_name' => esc_sql($schedule_name),
								'content_group_id' => esc_sql($content_group_id),
								'date_start' => esc_sql($date_start),
								'date_end' => esc_sql($date_end)
							)
						);
				}
				
				
				
				
				
				if($content_default_html != ''){
					$wpdb->insert( 
								$wpdb->prefix.'uniqueux_content', 
								array( 
									'content_html' => $content_default_html,
									'user_group_id' => 0,
									'content_group_id' => esc_sql($content_group_id),
									'schedule_id' => esc_sql($schedule_id)	
								)
							);
				}

				$rUserGroup = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group');
				foreach ($rUserGroup as $value) {

					if(isset($_POST['content_html'][$value->user_group_id])){
						$wpdb->insert( 
									$wpdb->prefix.'uniqueux_content', 
									array( 
										'content_html' => $_POST['content_html'][$value->user_group_id],
										'user_group_id' => esc_sql($value->user_group_id),
										'content_group_id' => esc_sql($content_group_id),
										'schedule_id' => esc_sql($schedule_id) 	
									)
								);
					}


				}
			}
		}
		
		if(isset($_GET['del'])) $del = intval($_GET['del']);

		if(isset($del)){
			$wpdb->delete( $wpdb->prefix.'uniqueux_content_group', array( 'content_group_id' => esc_sql($del) ) );
			$wpdb->delete( $wpdb->prefix.'uniqueux_content', array( 'content_group_id' => esc_sql($del) ) );
			$wpdb->delete( $wpdb->prefix.'uniqueux_schedule', array( 'content_group_id' => esc_sql($del) ) );
			$wpdb->delete( $wpdb->prefix.'uniqueux_devices', array( 'content_group_id' => esc_sql($del) ) );
		}

		echo '<div class="wrap">';

		if(isset($_GET['edit_content'])){
			
			$edit_content = intval($_GET['edit_content']);
			
			if(isset($_GET['schedule_id'])) $schedule_id = intval($_GET['schedule_id']);
			else $schedule_id = 0;

			$content_group_id = 0;
			$content_group_name = '';

			$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_content_group 
													WHERE content_group_id='.esc_sql($edit_content));
			if(count($results) > 0){
				$content_group_id = $results[0]->content_group_id;
				$content_group_name = $results[0]->content_group_name;
			}
			
			$rSchedule = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_schedule WHERE content_group_id='.esc_sql($edit_content));
			
			echo "<script>
					jQuery(document).ready(function(){
						jQuery('.date_start').datepicker({
							format:'dd/mm/yyyy'
						});	
						jQuery('.date_end').datepicker({
							format:'dd/mm/yyyy'
						});	
						jQuery('.time_start').timepicker({
							showMeridian:false
						});
						jQuery('.time_end').timepicker({
							showMeridian:false
						});
					});

					
					function delSchedule(){
						var r = confirm('Are you sure you want to delete this schedule?');
						if (r == true) {
							var data = {
								'action': 'uniqueux_del_schedule',
								'schedule_id': jQuery('#schedule_id').val()
							};
							jQuery.post(ajaxurl, data, function(response) {
								window.location = '".admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'])."';
							});
						}
					}
					
					function delDevice(){
						var r = confirm('Are you sure you want to delete this device?');
						if (r == true) {
							var data = {
								'action': 'uniqueux_del_device',
								'device_id': jQuery('#device_id').val()
							};
							jQuery.post(ajaxurl, data, function(response) {
								window.location = '".admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'])."';
							});
						}
					}
					
				</script>";

			echo '<h2>Content group</h2>';
			echo '<form method="post" action="'.admin_url( 'admin.php?page=uniqueux_content_group'.(isset($_GET['edit_content']) ? '&edit_content='.$_GET['edit_content'] : '').(isset($_GET['schedule_id']) ? '&schedule_id='.$_GET['schedule_id'] : '').(isset($_GET['device_id']) ? '&device_id='.$_GET['device_id'] : '')).'">';
			echo '<table class="form-table">
						<tr>
							<th scope="row">Content group name</th>
							<td>
								<input type="text" placeholder="Name" name="content_group_name" value="'.$content_group_name.'" />
							</td>
						</tr>';
						
			echo '<tr>
					<td colspan="2">
						<div class="uniqueux_schedule_tabs">';			
			if(count($rSchedule) > 0){
				echo '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content']).(isset($_GET['device_id']) ? '&device_id='.$_GET['device_id'] : '').'" class="page-title-action" '.(isset($_GET['schedule_id']) && $_GET['schedule_id'] == 0 ? 'style="background: #00a0d2;color: #fff;"' : '').' >Default Schedule</a>';
			}
			
			foreach ($rSchedule as $value) {
				echo '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'].'&schedule_id='.$value->schedule_id.(isset($_GET['device_id']) ? '&device_id='.$_GET['device_id'] : '')).'" '.(isset($_GET['schedule_id']) && $_GET['schedule_id'] == $value->schedule_id ? 'style="background: #00a0d2;color: #fff;"' : '').' class="page-title-action" >'.$value->schedule_name.'</a>';
			}
			
							
			echo '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'].'&schedule_id=-1').'" class="page-title-action" '.(isset($_GET['schedule_id']) && $_GET['schedule_id'] == '-1' ? 'style="background: #00a0d2;color: #fff;"' : '').' >+ Add Schedule</a>';
			
			$rScheduleDetail = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_schedule 
													WHERE schedule_id='.$schedule_id);
													
			if(count($rScheduleDetail) > 0){
				$schedule_name = $rScheduleDetail[0]->schedule_name;
				$date_start = date('d/m/Y',strtotime($rScheduleDetail[0]->date_start));
				$time_start = date('H:i',strtotime($rScheduleDetail[0]->date_start));
				$date_end = date('d/m/Y',strtotime($rScheduleDetail[0]->date_end));
				$time_end = date('H:i',strtotime($rScheduleDetail[0]->date_end));
				echo "<script>
							jQuery(document).ready(function(){
								jQuery('#date_start').val('".$date_start."');
								jQuery('#time_start').timepicker('setTime', '".$time_start."');
								jQuery('#date_end').val('".$date_end."');
								jQuery('#time_end').timepicker('setTime', '".$time_end."');
							});
							
							function checkDeviceAll(){
								jQuery('.devices').attr('checked','checked');
							}
							
							function unCheckDeviceAll(){
								jQuery('.devices').removeAttr('checked');
							}
					 </script>";
			}
			
			echo	'<div>
								<table class="form-table" id="schedule_form" '.(isset($_GET['schedule_id']) && ($_GET['schedule_id'] > 0 || $_GET['schedule_id'] == '-1') ? '' : 'style="display:none;"').' >
									<tr>
										<th scope="row" style="text-align: right;">Schedule Name</th>
										<td>
											<input type="text" id="schedule_name" name="schedule_name" value="'.(isset($schedule_name) ? $schedule_name : '').'" />
										</td>
										<th scope="row" style="text-align: right;">Start Date</th>
										<td>
											<input type="text" id="date_start" name="date_start" class="date_start" />
										</td>
										<th scope="row" style="text-align: right;">Start Time</th>
										<td>
											<input type="text" id="time_start" name="time_start" class="time_start" />
										</td>
										<th scope="row" style="text-align: right;">End Date</th>
										<td>
											<input type="text" id="date_end" name="date_end" class="date_end" />
										</td>
										<th scope="row" style="text-align: right;">End Time</th>
										<td>
											<input type="text" id="time_end" name="time_end" class="time_end" />
										</td>
										<th scope="row">&nbsp;</th>';
								echo	'<td>
											<input type="hidden" id="content_group_id" name="content_group_id" value="'.$_GET['edit_content'].'" />
											<input type="hidden" id="schedule_id" name="schedule_id" value="'.(isset($_GET['schedule_id']) ? $_GET['schedule_id'] : '').'" />';
											if($schedule_id > 0){
												echo '<input type="button" id="del_schedule" onClick="delSchedule();" class="button" style="background-color: #D20505;color: #fff;" value="Delete Schedule" />';
											}
										echo '</td>
									</tr>
								</table>
							</div>
						</div>
					</td>
				</tr>';	
				
			echo '<tr>
					<td colspan="2">
						<div id="uniqueux_device_tab">';
						
			$rDevices = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_devices 
														WHERE content_group_id='.esc_sql($edit_content));
														
			if(count($rDevices) > 0){
				
				echo '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'].(isset($_GET['schedule_id']) ? '&schedule_id='.$_GET['schedule_id'] : '')).'" class="page-title-action" '.(isset($_GET['device_id']) && $_GET['device_id'] == 0 ? 'style="background: #00a0d2;color: #fff;"' : '').' >Default Device</a>';
				
				foreach ($rDevices as $value) {
					echo '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'].(isset($_GET['schedule_id']) ? '&schedule_id='.$_GET['schedule_id'] : '').'&device_id='.$value->device_id).'" '.(isset($_GET['device_id']) && $_GET['device_id'] == $value->device_id ? 'style="background: #00a0d2;color: #fff;"' : '').' class="page-title-action" >'.$value->device_name.'</a>';
				}
				
			}
				
				echo '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$_GET['edit_content'].(isset($_GET['schedule_id']) ? '&schedule_id='.$_GET['schedule_id'] : '').'&device_id=-1').'" class="page-title-action" '.(isset($_GET['device_id']) && $_GET['device_id'] == '-1' ? 'style="background: #00a0d2;color: #fff;"' : '').' >+ Add Device</a>';
				
				if(isset($_GET['device_id']) && $_GET['device_id'] > 0){
					
					$device_id = intval($_GET['device_id']);
					
					$deviceDetail = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_devices 
														WHERE device_id='.esc_sql($device_id));
														
					$device_name = $deviceDetail[0]->device_name;
					$device_list = explode(',',$deviceDetail[0]->device_list);
				}else{
					$device_name = '';
					$device_list = array();
				}
				
				
					echo '<div>
							<table class="form-table" id="device_form" '.(isset($_GET['device_id']) && ($_GET['device_id'] > 0 || $_GET['device_id'] == '-1') ? '' : 'style="display:none;"').' >
									<tr>
										<th>Device name</th>
										<td>
											<input type="text" name="device_name" value="'.$device_name.'" />
										</td>
									</tr>
									<tr>
										<td>
											<a href="#" onClick="checkDeviceAll();">Check all</a><br />
											<a href="#" onClick="unCheckDeviceAll();">Un Check all</a>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="windows" '.(in_array('windows',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">Windows</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="mac" '.(in_array('mac',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">Mac</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="linux" '.(in_array('linux',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">Linux</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="android" '.(in_array('android',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">Android</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="iphone" '.(in_array('iphone',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">iPhone</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="ipad" '.(in_array('ipad',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">iPad</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="windowsphone" '.(in_array('windowsphone',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">Windows Phone</label>
										</td>
										<td>
											<input type="checkbox" class="devices" name="devices[]" value="blackberry" '.(in_array('blackberry',$device_list) ? ' checked="checked" ' : '').' />
											<label style="margin-left: 5px;margin-top: 5px;">BlackBerry</label>
										</td>
										<td style="text-align: right;">
											<input type="hidden" id="device_id" name="device_id" value="'.(isset($_GET['device_id']) ? $_GET['device_id'] : '').'" />
											<input type="button" id="del_device" onClick="delDevice();" class="button" style="background-color: #D20505;color: #fff;" value="Delete Device" />
										</td>
									</tr>
								</table>
								</div>';
					
					echo '</div>
					</td>
				</tr>';	


			if(isset($_GET['device_id']) && ($_GET['device_id'] > 0 || $_GET['device_id'] == '-1')) $device_id = intval($_GET['device_id']);
			else $device_id = 0;				 


			$settings = array(
									'textarea_name'=>'content_default_html',
									'editor_height' => 450, 
    								'textarea_rows' => 20
									);			

			$rUserGroup = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group');	
			
			
			$getDefaultContent = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_content 
													WHERE content_group_id='.esc_sql($edit_content).' 
													AND user_group_id=0 
													AND schedule_id='.esc_sql($schedule_id).' 
													AND device_id='.esc_sql($device_id));
			if(count($getDefaultContent) > 0){
				$content_default_html = stripslashes($getDefaultContent[0]->content_html);
			}else{
				$content_default_html = '';
			}

			echo '
			<tr>
			<td colspan="2">
			<div class="wrap uniqueux_tabs">
			<h2 class="nav-tab-wrapper">
				    <a href="#1" class="nav-tab nav1 nav-tab-active'.(($content_default_html != '') ? ' green':' red').'" onClick="changeTabs(1);">Default Html</a>';

			$i = 2;	    
			foreach ($rUserGroup as $value) {
				$html_var = $wpdb->get_var('SELECT content_html FROM '.$wpdb->prefix.'uniqueux_content 
												WHERE user_group_id='.esc_sql($value->user_group_id).' 
												AND content_group_id='.esc_sql($content_group_id).' 
												AND schedule_id='.esc_sql($schedule_id).' 
												AND device_id='.esc_sql($device_id));

				echo '<a href="#'.$i.'" class="nav-tab nav'.$i.''.(($html_var != '') ? ' green':' red').'" onClick="changeTabs('.$i.');" >'.$value->user_group_name.' Html</a>';
				$i++;
			}

			echo	'</h2>
					<div class="tabs_detail tabs1">';
			echo	wp_editor( stripslashes($content_default_html), 'content_default_html', $settings );
			echo 	'</div>';

			$i = 2;	    
			foreach ($rUserGroup as $value) {

				$html = $wpdb->get_var('SELECT content_html FROM '.$wpdb->prefix.'uniqueux_content 
												WHERE user_group_id='.esc_sql($value->user_group_id).' 
												AND content_group_id='.esc_sql($content_group_id).' 
												AND schedule_id='.esc_sql($schedule_id).' 
												AND device_id='.esc_sql($device_id));

				$settings = array(
									'textarea_name'=>'content_html['.$value->user_group_id.']',
									'editor_height' => 450, 
    								'textarea_rows' => 20
									);
				echo '<div class="tabs_detail tabs'.$i.'">';
				echo	wp_editor( stripslashes($html), 'content_html_'.$value->user_group_id, $settings );
				echo '</div>';
				$i++;
			}

			echo '</div>
					</td>
					</tr>
					<tr>
							<th scope="row">
							<input type="submit" value="Save Changes" class="button button-primary" />
							</th>
							<td>
								<input type="hidden" name="edit_id" value="'.$content_group_id.'" />
							</td>
						</tr>
				</table>
				</form>';
		}else{
			$contentGroupTable = new Uniqueux_Content_Group_Table();
	    	$contentGroupTable->prepare_items();
	    	echo '<h2>Content group <a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content=0').'" class="add-new-h2">Add New</a></h2>';
	    	$contentGroupTable->display();
		}
		echo '</div>';
}

function uniqueux_user_group(){
	global $country_array;
	global $wpdb;
	
	if(isset($_POST['edit_id'])) $edit_id = intval($_POST['edit_id']);
	if(isset($_POST['user_group_name'])) $user_group_name = sanitize_text_field($_POST['user_group_name']);
	if(isset($_POST['country'])) $country = sanitize_text_field($_POST['country']);
	if(isset($_POST['points'])) $points = intval($_POST['points']);

	if(isset($_POST['edit_id'])){
		if($edit_id > 0){

			$wpdb->update( 
				$wpdb->prefix.'uniqueux_user_group', 
				array( 
					'user_group_name' => esc_sql($user_group_name),	
					'country' => esc_sql($country),
					'points' => esc_sql($points)
				), 
				array( 'user_group_id' => esc_sql($edit_id) )
			);

		}else if($edit_id == 0){

			$wpdb->insert( 
				$wpdb->prefix.'uniqueux_user_group', 
				array( 
					'user_group_name' => esc_sql($user_group_name), 
					'country' => esc_sql($country), 
					'points' => esc_sql($points)  
				)
			);
			
			
			$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'uniqueux_visitors` ADD  `user_group_'.$wpdb->insert_id.'` INT NOT NULL');			

		}
	}

	if(isset($_GET['del'])){
		
		$del = intval($_GET['del']);
		
		$wpdb->delete( $wpdb->prefix.'uniqueux_user_group', array( 'user_group_id' => esc_sql($del) ) );
		
		$wpdb->query('ALTER TABLE  `'.$wpdb->prefix.'uniqueux_visitors` DROP  `user_group_'.$_GET['del'].'` ');		
	}

	echo '<div class="wrap">';
	if(isset($_GET['edit_group'])){

		$edit_group = intval($_GET['edit_group']);
		$user_group_id = 0;
		$user_group_name = '';
		$country = '';

		$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group WHERE user_group_id='.$edit_group);
		if(count($results) > 0){
			$user_group_id = $results[0]->user_group_id;
			$user_group_name = $results[0]->user_group_name;
			$country = $results[0]->country;
			$points = $results[0]->points;
		}

		echo '<h2>User group</h2>';
		echo '<form method="post" action="'.admin_url( 'admin.php?page=uniqueux_user_group').'">';
		echo '<table class="form-table">
					<tr>
						<th scope="row">User group name</th>
						<td>
							<input type="text" placeholder="Name" name="user_group_name" value="'.$user_group_name.'" />
						</td>
					</tr>
					<tr>
						<th scope="row">Country</th>
						<td>
							<select name="country">';
							foreach ($country_array as $key => $value) {
								if($country == $key) echo '<option selected value="'.$key.'">'.$value.'</option>';
								else echo '<option value="'.$key.'">'.$value.'</option>';
							}
		echo				'</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Location points</th>
						<td>
							<input type="number" placeholder="0" name="points" value="'.$points.'" />
						</td>
					</tr>
					<tr>
						<th scope="row">
						<input type="submit" value="Save Changes" class="button button-primary" />
						</th>
						<td>
							<input type="hidden" name="edit_id" value="'.$user_group_id.'" />
						</td>
					</tr>
			</table>
			</form>';
	}else{
		$userGroupTable = new Uniqueux_User_Group_Table();
    	$userGroupTable->prepare_items();
    	echo '<h2>User group <a href="'.admin_url( 'admin.php?page=uniqueux_user_group&edit_group=0').'" class="add-new-h2">Add New</a></h2>';
    	$userGroupTable->display();
	}
	echo '</div>';
}

?>
