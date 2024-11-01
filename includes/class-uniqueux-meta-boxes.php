<?php

	add_action( 'add_meta_boxes', 'uniqueux_user_group_add_meta_box' );
	add_action( 'save_post', 'uniqueux_user_group_save_meta_box_data' );
	
	function uniqueux_user_group_add_meta_box() {

		$screens = array( 'post', 'page' );

		foreach ( $screens as $screen ) {

			add_meta_box(
				'user_group_meta',
				__( 'User group', 'myplugin_textdomain' ),
				'uniqueux_user_group_meta_box_callback',
				$screen,
				'side'
			);
		}
	}

	function uniqueux_user_group_meta_box_callback( $post ) {

		global $wpdb;

		wp_nonce_field( 'uniqueux_user_group_save_meta_box_data', 'uniqueux_user_group_meta_box_nonce' );

		echo '<table>
					<tr>
						<th width="20%" style="text-align: center;">Main</th>
						<th width="20%" style="text-align: center;">Related</th>
						<th width="60%"></th>
					</tr>';

		$main_val = get_post_meta( $post->ID, 'user_group_main', true );
		$related_val = get_post_meta( $post->ID, 'user_group_related', true );		

		$rUserGroup = $wpdb->get_results('SELECT user_group_id,user_group_name FROM '.$wpdb->prefix.'uniqueux_user_group');
		foreach ($rUserGroup as $value) {

			echo '<tr>
						<td style="text-align: center;">
							<input type="checkbox" name="user_group_main[]" '.((strpos($main_val,','.$value->user_group_id.',') !== false) ? ' checked="checked" ':'').' value="'.$value->user_group_id.'" />
						</td>
						<td style="text-align: center;">
							<input type="checkbox" name="user_group_related[]" '.((strpos($related_val,','.$value->user_group_id.',') !== false) ? ' checked="checked" ':'').' value="'.$value->user_group_id.'" />
						</td>
						<td>
							<div style="margin-left: 10px;">'.$value->user_group_name.'</div>
						</td>
					</tr>';
		}			
		echo	'</table>';
	}

	function uniqueux_user_group_save_meta_box_data( $post_id ) {

		global $wpdb;

		if ( ! isset( $_POST['uniqueux_user_group_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['uniqueux_user_group_meta_box_nonce'], 'uniqueux_user_group_save_meta_box_data' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		if(count($_POST['user_group_main']) > 0){

			$user_group_main_val = ','.implode(",", $_POST['user_group_main']).',';

			update_post_meta( $post_id, 'user_group_main', $user_group_main_val);
		}else{
			delete_post_meta( $post_id, 'user_group_main');
		}	


		if(count($_POST['user_group_related']) > 0){

			$user_group_related_val = ','.implode(",", $_POST['user_group_related']).',';

			update_post_meta( $post_id, 'user_group_related', $user_group_related_val);
		}else{
			delete_post_meta( $post_id, 'user_group_related');
		}	
		
	}
	
?>