<?php


class Uniqueux_Visitor_Rank_Table extends WP_List_Table {
	
	function __construct() {

		global $status, $page;

		parent::__construct(
			array(
				'singular'	=> 'movie',
				'plural'	=> 'movies',
				'ajax'		=> true
			)
		);
		
	}

	function column_default( $item, $column_name ) {

		global $wpdb;
			$array_column = array('visitor_id','os','browser','country','first_time');
			
			if(get_option('uniqueux_track_latest') == 1){
				array_push($array_column,'latest_time');
				array_push($array_column,'page_views');
			}
			
			$result_user_group = $wpdb->get_results("SELECT user_group_id FROM ".$wpdb->prefix."uniqueux_user_group");
				if($result_user_group){
					foreach($result_user_group as $row_user_group){
						array_push($array_column,'user_group_'.$row_user_group->user_group_id);
					}
				}
							
			if(in_array($column_name,$array_column)){
				return $item[$column_name];
			}else{
				return print_r( $item, true ) ;
			}
	}

	function get_columns() {

		global $wpdb;
			
		$columns = array(
			'visitor_id' => 'Visitor ID',
			'os'		=> 'OS',
			'browser' => 'Browser',
			'country' => 'Country',
			'first_time' => 'First visit'
		);
		
		if(get_option('uniqueux_track_latest') == 1){
			$columns['latest_time'] = 'Latest visit';
			$columns['page_views'] = 'Page Views';
		}
		
		$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."uniqueux_user_group");
		if($results){
			foreach($results as $row){
				$columns['user_group_'.$row->user_group_id] = $row->user_group_name;
			}
		}
		

		return $columns;
	}

	function get_sortable_columns() {

		global $wpdb;
			
			$sortable = array(
		    	'visitor_id' => array('visitor_id', false),
				'country' => array('country', false),
				'first_time' => array('first_time', false) 	
		    );
			
			if(get_option('uniqueux_track_latest') == 1){
				$sortable['latest_time'] = array('latest_time', false);
				$sortable['page_views'] = array('page_views', false);
			}
			
			$results = $wpdb->get_results("SELECT user_group_id FROM ".$wpdb->prefix."uniqueux_user_group");
			if($results){
				foreach($results as $row){
					$sortable['user_group_'.$row->user_group_id] = array('user_group_'.$row->user_group_id,false);
				}
			}
			
		    return $sortable;
	}
	
	function table_data($per_page = 5, $page_number = 1)
		{
		    global $wpdb;
		    $data = array();
			$data_array = array();
			
				$sql = "SELECT * FROM {$wpdb->prefix}uniqueux_visitors";

				  if ( ! empty( $_REQUEST['orderby'] ) ) {
					$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
					$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
				  }
				
				  $sql .= " LIMIT $per_page";
				
				  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
				  
				  $results = $wpdb->get_results($sql);
			
				//$results = $wpdb->get_results('SELECT * FROM `'.$wpdb->prefix.'uniqueux_visitors`');
				
				if($results){
					foreach($results as $row){
						$user_agent = uniqueux_getReturnBrowser($row->useragent);
						
						$data['visitor_id'] = $row->visitor_id;
						$data['os'] = $user_agent['platform'];
						$data['browser'] = $user_agent['name'];
						$data['country'] = $row->country;
						$data['first_time'] = date('d F Y H:i',strtotime($row->first_time));
						
						if(get_option('uniqueux_track_latest') == 1){
							$data['latest_time'] = date('d F Y H:i',strtotime($row->latest_time));
							$data['page_views'] = $row->page_views;
						}
						
						$result_user_group = $wpdb->get_results("SELECT user_group_id FROM ".$wpdb->prefix."uniqueux_user_group");
						if($result_user_group){
							foreach($result_user_group as $row_user_group){
								$visitors_num = $wpdb->get_var("SELECT user_group_".$row_user_group->user_group_id." FROM ".$wpdb->prefix."uniqueux_visitors WHERE visitor_id='".$row->visitor_id."' ");								$data['user_group_'.$row_user_group->user_group_id] = $visitors_num;
							}
						}
						
						array_push($data_array,$data);
					}
				}

		    return $data_array;
		}
		
		public static function record_count() {
		  global $wpdb;
		
		  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}uniqueux_visitors";
		
		  return $wpdb->get_var( $sql );
		}
		
		
	private function sort_data( $a, $b )
		{
		    $orderby = 'visitor_id';
		    $order = 'desc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}	

	function prepare_items() {

		global $wpdb; //This is used only if making any database queries

		$per_page = 10;
	
		$columns = $this->get_columns();
		
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$current_page = $this->get_pagenum();

		$total_items  = $this->record_count();

		$data = $this->table_data($per_page,$current_page);

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items'	=> $total_items,
				'per_page'	=> $per_page,
				'total_pages'	=> ceil( $total_items / $per_page ),
				'orderby'	=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'user_group_name',
				'order'		=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'asc'
			)
		);
	}

	function display() {
		wp_nonce_field( 'ajax-visitor-list-nonce', '_ajax_visitor_list_nonce' );
		echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';
		parent::display();
	}

	function ajax_response() {
		check_ajax_referer( 'ajax-visitor-list-nonce', '_ajax_visitor_list_nonce' );
		$this->prepare_items();
		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );
		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();

		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		die( json_encode( $response ) );
	}


}


class Uniqueux_Visitor_Stats_Table extends WP_List_Table
{
		public function prepare_items()
		{
		    $columns = $this->get_columns();
		    $hidden = $this->get_hidden_columns();
		    $sortable = $this->get_sortable_columns();

		    $data = $this->table_data();
		    usort( $data, array( &$this, 'sort_data' ) );

		    $perPage = 10;
		    $currentPage = $this->get_pagenum();
		    $totalItems = count($data);

		    $this->set_pagination_args( array(
		        'total_items' => $totalItems,
		        'per_page'    => $perPage
		    ) );

		    $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		    $this->_column_headers = array($columns, $hidden, $sortable);
		    $this->items = $data;
		}

		public function get_columns()
		{
		    $columns = array(
		        'user_group_id'		=> 'User Group ID',
		        'user_group_name'		=> 'User Group Name',
				'visitors' => 'Visitors'
		    );

		    return $columns;
		}

		public function get_hidden_columns()
		{
		    return array('user_group_id');
		}

		public function get_sortable_columns()
		{
		    return array(
		    	'user_group_id' => array('user_group_id', false),
		    	'user_group_name' => array('user_group_name', false),
				'visitors' => array('visitors', false)	    	
		    );
		}

		private function table_data()
		{
		    global $wpdb;
		    $data = array();

			$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group');
			
			if($results){
				foreach($results as $row){
					
					$visitors = $wpdb->get_var('SELECT SUM(user_group_'.$row->user_group_id.') as visitors_count FROM `'.$wpdb->prefix.'uniqueux_visitors`');
					
					$data[] = array(
						'user_group_id'		=>  $row->user_group_id,
						'user_group_name'		=>  '<a href="'.admin_url( 'admin.php?page=uniqueux_user_group&edit_group='.$row->user_group_id).'">'.$row->user_group_name.'</a>',
						'visitors' => $visitors
					);
				}
			}

		    return $data;
		}
		
		public function column_id($item)
		{
			return $item['user_group_id'];
		}

		public function column_default( $item, $column_name )
		{
		    switch( $column_name ) {
		        case 'user_group_id':
		        case 'user_group_name':
				case 'visitors':
		            return $item[ $column_name ];
		        default:
		            return print_r( $item, true ) ;
		    }
		}

		private function sort_data( $a, $b )
		{
		    $orderby = 'visitors';
		    $order = 'desc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}
		
}


class Uniqueux_Visitors_Country_Table extends WP_List_Table {
	
	function __construct() {

		global $status, $page;

		parent::__construct(
			array(
				'singular'	=> 'country',
				'plural'	=> 'countrys',
				'ajax'		=> true
			)
		);
		
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
		        case 'id':
		        case 'name_country':
		        case 'count_country':
		            return $item[ $column_name ];
		        default:
		            return print_r( $item, true ) ;
		    }
	}

	function get_columns() {

		$columns = array(
			'name_country'		=> 'Country',
			'count_country'		=> 'Count'
		);

		return $columns;
	}

	function get_sortable_columns() {

		return array(
		    	'name_country' => array('name_country', false),
		    	'count_country' => array('count_country', false)	    	
		    );
	}
	
	function table_data($per_page = 5, $page_number = 1)
		{
						
		    global $wpdb;
		    $data = array();
			$data_array = array();
			
				$sql = "SELECT COUNT(country) as count_country, country as name_country 
												FROM `".$wpdb->prefix."uniqueux_visitors` 
												GROUP BY country";

				  if ( ! empty( $_REQUEST['orderby'] ) ) {
					$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
					$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
				  }
				
				  $sql .= " LIMIT $per_page";
				
				  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
				  
				  $results = $wpdb->get_results($sql);
				
				$i = 1;			
				if($results){
					foreach($results as $row){
						$data['id'] = $i;
						$data['name_country'] = $row->name_country;
						$data['count_country'] = $row->count_country;						
						array_push($data_array,$data);
						$i++;
					}
				}

		    return $data_array;
		}
		
		public static function record_count() {
		  global $wpdb;
		  
		  $sql = "SELECT COUNT(*) FROM 
		  					(SELECT COUNT(country) FROM `".$wpdb->prefix."uniqueux_visitors` GROUP BY country) as m";
				
		  return $wpdb->get_var( $sql );
		}
		
		
	private function sort_data( $a, $b )
		{
		    $orderby = 'count_country';
		    $order = 'desc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}	

	function prepare_items() {

		global $wpdb; //This is used only if making any database queries

		$per_page = 10;
	
		$columns = $this->get_columns();
		
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		
		
		
		$current_page = $this->get_pagenum();

		$total_items  = $this->record_count();

		$data = $this->table_data($per_page,$current_page);

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items'	=> $total_items,
				'per_page'	=> $per_page,
				'total_pages'	=> ceil( $total_items / $per_page ),
				'orderby'	=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'user_group_name',
				'order'		=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'asc'
			)
		);
		
	}

	function display() {
		wp_nonce_field( 'ajax-country-list-nonce', '_ajax_country_list_nonce' );
		echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';
		parent::display();
	}

	function ajax_response() {
		check_ajax_referer( 'ajax-country-list-nonce', '_ajax_country_list_nonce' );
		$this->prepare_items();
		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );
		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();

		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		die( json_encode( $response ) );
	}


}


class Uniqueux_Visitors_Browser_Table extends WP_List_Table
{
		public function prepare_items()
		{
		    $columns = $this->get_columns();
		    $hidden = $this->get_hidden_columns();
		    $sortable = $this->get_sortable_columns();

		    $data = $this->table_data();
		    usort( $data, array( &$this, 'sort_data' ) );

		    $perPage = 10;
		    $currentPage = $this->get_pagenum();
		    $totalItems = count($data);

		    $this->set_pagination_args( array(
		        'total_items' => $totalItems,
		        'per_page'    => $perPage
		    ) );

		    $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		    $this->_column_headers = array($columns, $hidden, $sortable);
		    $this->items = $data;
		}

		public function get_columns()
		{
		    $columns = array(
		        'id'		=> 'ID',
		        'name_browser'		=> 'Browser',
		        'count_browser'		=> 'Count'
		    );

		    return $columns;
		}

		public function get_hidden_columns()
		{
		    return array('id');
		}

		public function get_sortable_columns()
		{
		    return array(
		    	'id' => array('id', false),
		    	'name_browser' => array('name_browser', false),
		    	'count_browser' => array('count_browser', false)	    	
		    );
		}
		
		private function table_data()
		{
		    global $wpdb;
		    $data = array();
										
			$os_array       =   array(
									'msie'     =>  'Internet Explorer',
									'firefox'     =>  'Mozilla Firefox',
									'chrome'     =>  'Google Chrome',
									'safari'     =>  'Apple Safari',
									'opera'     =>  'Opera',
									'netscape'     =>  'Netscape'
								);	
			$when = '';	
								
			foreach ($os_array as $regex => $value) { 
				$when .= " WHEN LOWER(useragent) REGEXP '".$regex."' THEN '".$value."' ";
			}  					

			$results = $wpdb->get_results("SELECT COUNT(bs.name_browser) as count_browser,name_browser FROM 
												(SELECT 
													CASE 
														".$when."
														ELSE 'Unknown Browser' 
														END as name_browser 
												FROM `".$wpdb->prefix."uniqueux_visitors` ) bs 
												GROUP BY bs.name_browser ");
			$i = 1;
			if($results){
				foreach($results as $row){
					$data[] = array(
						'id'		=>  $i,
						'name_browser'		=>  $row->name_browser,
						'count_browser'		=>  $row->count_browser
					);
					$i++;
				}
			}

		    return $data;
		}
		
		public function column_id($item)
		{
			return $item['id'];
		}

		public function column_default( $item, $column_name )
		{
		    switch( $column_name ) {
		        case 'id':
		        case 'name_browser':
		        case 'count_browser':
		            return $item[ $column_name ];
		        default:
		            return print_r( $item, true ) ;
		    }
		}

		private function sort_data( $a, $b )
		{
		    $orderby = 'count_browser';
		    $order = 'desc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}
				
}



class Uniqueux_Visitors_OS_Table extends WP_List_Table
{
		public function prepare_items()
		{
		    $columns = $this->get_columns();
		    $hidden = $this->get_hidden_columns();
		    $sortable = $this->get_sortable_columns();

		    $data = $this->table_data();
		    usort( $data, array( &$this, 'sort_data' ) );

		    $perPage = 20;
		    $currentPage = $this->get_pagenum();
		    $totalItems = count($data);

		    $this->set_pagination_args( array(
		        'total_items' => $totalItems,
		        'per_page'    => $perPage
		    ) );

		    $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		    $this->_column_headers = array($columns, $hidden, $sortable);
		    $this->items = $data;
		}

		public function get_columns()
		{
		    $columns = array(
		        'id'		=> 'ID',
		        'name_os'		=> 'OS',
		        'count_os'		=> 'Count'
		    );

		    return $columns;
		}

		public function get_hidden_columns()
		{
		    return array('id');
		}

		public function get_sortable_columns()
		{
		    return array(
		    	'id' => array('id', false),
		    	'name_os' => array('name_os', false),
		    	'count_os' => array('count_os', false)	    	
		    );
		}

		private function table_data()
		{
		    global $wpdb;
		    $data = array();
										
			$os_array       =   array(
									'iphone'             =>  'iPhone',
									'ipod'               =>  'iPod',
									'ipad'               =>  'iPad',
									'macintosh|mac os x' =>  'Mac',
									'mac_powerpc'        =>  'Mac',
									'windows nt 10'     =>  'Windows',
									'windows nt 6.3'     =>  'Windows',
									'windows nt 6.2'     =>  'Windows',
									'windows nt 6.1'     =>  'Windows',
									'windows nt 6.0'     =>  'Windows',
									'windows nt 5.2'     =>  'Windows',
									'windows nt 5.1'     =>  'Windows',
									'windows xp'         =>  'Windows',
									'windows nt 5.0'     =>  'Windows',
									'windows me'         =>  'Windows',
									'win98'              =>  'Windows',
									'win95'              =>  'Windows',
									'win16'              =>  'Windows',
									'windows phone'      =>  'Windows Phone',
									'windows (phone|ce)' =>  'Windows Phone',
									'linux'              =>  'Linux',
									'ubuntu'             =>  'Linux',
									'android'            =>  'Android',
									'blackberry'         =>  'Blackberry',
									'webos'              =>  'Mobile'
								);	
			$when = '';	
								
			foreach ($os_array as $regex => $value) { 
				$when .= " WHEN LOWER(useragent) REGEXP '".$regex."' THEN '".$value."' ";
			}  					

			$results = $wpdb->get_results("SELECT COUNT(os.name_os) as count_os,name_os FROM 
												(SELECT 
													CASE 
														".$when."
														ELSE 'Unknown OS Platform' 
														END as name_os 
												FROM `".$wpdb->prefix."uniqueux_visitors` ) os 
												GROUP BY os.name_os ");
			$i = 1;
			if($results){
				foreach($results as $row){
					$data[] = array(
						'id'		=>  $i,
						'name_os'		=>  $row->name_os,
						'count_os'		=>  $row->count_os
					);
					$i++;
				}
			}

		    return $data;
		}
		
		public function column_id($item)
		{
			return $item['id'];
		}

		public function column_default( $item, $column_name )
		{
		    switch( $column_name ) {
		        case 'id':
		        case 'name_os':
		        case 'count_os':
		            return $item[ $column_name ];
		        default:
		            return print_r( $item, true ) ;
		    }
		}

		private function sort_data( $a, $b )
		{
		    $orderby = 'count_os';
		    $order = 'desc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}
				
}



class Uniqueux_Content_Group_Table extends WP_List_Table
{
		public function prepare_items()
		{
		    $columns = $this->get_columns();
		    $hidden = $this->get_hidden_columns();
		    $sortable = $this->get_sortable_columns();

		    $data = $this->table_data();
		    usort( $data, array( &$this, 'sort_data' ) );

		    $perPage = 20;
		    $currentPage = $this->get_pagenum();
		    $totalItems = count($data);

		    $this->set_pagination_args( array(
		        'total_items' => $totalItems,
		        'per_page'    => $perPage
		    ) );

		    $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		    $this->_column_headers = array($columns, $hidden, $sortable);
		    $this->items = $data;
		}

		public function get_columns()
		{
		    $columns = array(
		        'content_group_id'		=> 'Content ID',
		        'content_group_name'		=> 'Content name',
		        'shortcode'	=> 'Short code'
		    );

		    return $columns;
		}

		public function get_hidden_columns()
		{
		    return array('content_group_id');
		}

		public function get_sortable_columns()
		{
		    return array(
		    	'content_group_id' => array('content_group_id', false),
		    	'content_group_name' => array('content_group_name', false)	    	
		    );
		}

		private function table_data()
		{
		    global $wpdb;
		    $data = array();

			$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_content_group');
			
			if($results){
				foreach($results as $row){
					$data[] = array(
						'content_group_id'		=>  $row->content_group_id,
						'content_group_name'		=>  '<a href="'.admin_url( 'admin.php?page=uniqueux_content_group&edit_content='.$row->content_group_id).'">'.$row->content_group_name.'</a>',
						'shortcode'	=> '[uniqueux_content id="'.$row->content_group_id.'"]'
					);
				}
			}

		    return $data;
		}
		
		public function column_id($item)
		{
			return $item['content_group_id'];
		}
		
		public function column_default( $item, $column_name )
		{
		    switch( $column_name ) {
		        case 'content_group_id':
		        case 'content_group_name':
		        case 'shortcode':
		            return $item[ $column_name ];
		        default:
		            return print_r( $item, true ) ;
		    }
		}

		private function sort_data( $a, $b )
		{
		    $orderby = 'content_group_name';
		    $order = 'asc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}
		
}


class Uniqueux_User_Group_Table extends WP_List_Table
{
		public function prepare_items()
		{
		    $columns = $this->get_columns();
		    $hidden = $this->get_hidden_columns();
		    $sortable = $this->get_sortable_columns();

		    $data = $this->table_data();
		    usort( $data, array( &$this, 'sort_data' ) );

		    $perPage = 20;
		    $currentPage = $this->get_pagenum();
		    $totalItems = count($data);

		    $this->set_pagination_args( array(
		        'total_items' => $totalItems,
		        'per_page'    => $perPage
		    ) );

		    $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		    $this->_column_headers = array($columns, $hidden, $sortable);
		    $this->items = $data;
		}

		public function get_columns()
		{
		    $columns = array(
		        'user_group_id'		=> 'Group ID',
		        'user_group_name'		=> 'Group name',
		        'country'		=> 'Country',
		        'points'		=> 'Points'
		    );

		    return $columns;
		}

		public function get_hidden_columns()
		{
		    return array('user_group_id');
		}

		public function get_sortable_columns()
		{
		    return array(
		    	'user_group_id' => array('user_group_id', false),
		    	'user_group_name' => array('user_group_name', false),
		    	'country' => array('country', false),
		    	'points' => array('points', false)		    	
		    );
		}

		private function table_data()
		{
		    global $wpdb;
		    $data = array();

			$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'uniqueux_user_group');
			
			if($results){
				foreach($results as $row){
					$data[] = array(
						'user_group_id'		=>  $row->user_group_id,
						'user_group_name'		=>  '<a href="'.admin_url( 'admin.php?page=uniqueux_user_group&edit_group='.$row->user_group_id).'">'.$row->user_group_name.'</a>',
						'country'		=>  $row->country,
						'points'	=> $row->points
					);
				}
			}

		    return $data;
		}
		
		public function column_id($item)
		{
			return $item['user_group_id'];
		}

		public function column_default( $item, $column_name )
		{
		    switch( $column_name ) {
		        case 'user_group_id':
		        case 'user_group_name':
		        case 'country':
		        case 'points':
		            return $item[ $column_name ];
		        default:
		            return print_r( $item, true ) ;
		    }
		}

		private function sort_data( $a, $b )
		{
		    $orderby = 'user_group_name';
		    $order = 'asc';

		    if(!empty($_GET['orderby']))
		    {
		        $orderby = $_GET['orderby'];
		    }

		    if(!empty($_GET['order']))
		    {
		        $order = $_GET['order'];
		    }

		    $result = strnatcmp( $a[$orderby], $b[$orderby] );

		    if($order === 'asc')
		    {
		        return $result;
		    }

		    return -$result;
		}
		
}


	
