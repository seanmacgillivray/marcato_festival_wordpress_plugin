<?php
/*
 * Plugin Name: Marcato XML Importer
 * Description: Imports artists, venues, shows, and workshops from Marcato Festival XML Feeds.
 * Author: Marcato Digital Solutions
 * Plugin URI: http://github.com/morgancurrie/marcato_festival_wordpress_plugin
 * Version: 1.0
 * =======================================================================
*/

//Initialize the plugin
$marcatoxml = new marcatoxml_plugin();
class marcatoxml_plugin {
	
	public $importer;

	function marcatoxml_plugin(){
		$this->importer = new marcatoxml_importer();
		add_action('admin_menu',array($this, 'build_menus'));
		add_filter('posts_where', array($this, 'posts_where'));
		add_action('init',array($this, 'register_custom_post_types')); 
		wp_enqueue_style("marcato",plugins_url("",__FILE__)."/css/marcato.css");
		register_activation_hook(__FILE__, array($this,'flush_rewrites'));
		register_deactivation_hook(__FILE__, array($this, 'flush_rewrites'));
		register_activation_hook(__FILE__, array($this, 'schedule_updates'));
		register_deactivation_hook(__FILE__, array($this, 'unschedule_updates'));
		add_action('marcato_update', array($this,'import_all'));
	}
	
	public function flush_rewrites(){
		flush_rewrite_rules();
	}
	public function register_custom_post_types(){
		register_post_type("marcato_artist", array(
			"label"=>"Artists", "has_archive"=>"artists", 
			"labels"=>array("name"=>"Artists","singular_name"=>"Artist"), 
			"public"=>true, 
			"rewrite"=>array("slug"=>"artists", "with_front"=>false),
			"supports"=>array("title","editor","thumbnail"),
			"menu_icon"=>plugin_dir_url(__FILE__)."/images/wp_marcato_logo.png"
			)
		);
		register_post_type("marcato_venue", array(
			"label"=>"Venues", "has_archive"=>"venues",
			"labels"=>array("name"=>"Venues","singular_name"=>"Venue"),
			"public"=>true,
			"rewrite"=>array("slug"=>"venues", "with_front"=>false),
			"supports"=>array("title","editor","thumbnail"),
			"menu_icon"=>plugin_dir_url(__FILE__)."/images/wp_marcato_logo.png"
			)
		);
		register_post_type("marcato_show", array(
			"label"=>"Shows", "has_archive"=>"shows",
			"labels"=>array("name"=>"Shows","singular_name"=>"Show"),
			"public"=>true,
			"rewrite"=>array("slug"=>"shows", "with_front"=>false),
			"supports"=>array("title","editor","thumbnail"),
			"menu_icon"=>plugin_dir_url(__FILE__)."/images/wp_marcato_logo.png"
			)
		);
		register_post_type("marcato_workshop", array(
			"label"=>"Workshops", "has_archive"=>"workshops",
			"labels"=>array("name"=>"Workshops","singular_name"=>"Workshop"),
			"public"=>true,
			"rewrite"=>array("slug"=>"workshops", "with_front"=>false),
			"supports"=>array("title","editor","thumbnail"),
			"menu_icon"=>plugin_dir_url(__FILE__)."/images/wp_marcato_logo.png"
			)
		);
	}
		
	public function import($field){
		return $this->importer->import($field);
	}
	
	public function import_all(){
		return $this->importer->import_all();
	}
	
	public function schedule_updates(){
		if (!wp_next_scheduled('marcato_update')){
			wp_schedule_event( time(), 'hourly', 'marcato_update' );
		}
	}
	public function unschedule_updates(){
		wp_clear_scheduled_hook('marcato_update');
	}
	
	public function posts_where($where){
		global $wpdb;
		if (isset($_GET['artist_name']) && !empty($_GET['artist_name'])){
			$where = " AND {$wpdb->posts}.post_type = 'marcato_artist' AND {$wpdb->posts}.post_status = 'publish' AND LOWER({$wpdb->posts}.post_title) = LOWER('{$_GET['artist_name']}')";
		}
		else if (isset($_GET['artist_id']) && !empty($_GET['artist_id'])){
			$where = " AND {$wpdb->posts}.post_type = 'marcato_artist' AND {$wpdb->posts}.post_status = 'publish' AND EXISTS (SELECT * FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = 'marcato_artist_id' AND {$wpdb->postmeta}.meta_value = '{$_GET['artist_id']}' AND {$wpdb->postmeta}.post_id = {$wpdb->posts}.id)";
		}
		else if (isset($_GET['venue_name']) && !empty($_GET['venue_name'])){
			$where = " AND {$wpdb->posts}.post_type = 'marcato_venue' AND {$wpdb->posts}.post_status = 'publish' AND LOWER({$wpdb->posts}.post_title) = LOWER('{$_GET['venue_name']}')";
		}
		else if (isset($_GET['venue_id']) && !empty($_GET['venue_id'])){
			$where = " AND {$wpdb->posts}.post_type = 'marcato_venue' AND {$wpdb->posts}.post_status = 'publish' AND EXISTS (SELECT * FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = 'marcato_venue_id' AND {$wpdb->postmeta}.meta_value = '{$_GET['venue_id']}' AND {$wpdb->postmeta}.post_id = {$wpdb->posts}.id)";
		}
		else if (isset($_GET['show_id']) && !empty($_GET['show_id'])){
			$where = " AND {$wpdb->posts}.post_type = 'marcato_show' AND {$wpdb->posts}.post_status = 'publish' AND EXISTS (SELECT * FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = 'marcato_show_id' AND {$wpdb->postmeta}.meta_value = '{$_GET['show_id']}' AND {$wpdb->postmeta}.post_id = {$wpdb->posts}.id)";
		}
		else if (isset($_GET['workshop_id']) && !empty($_GET['workshop_id'])){
			$where = " AND {$wpdb->posts}.post_type = 'marcato_workshop' AND {$wpdb->posts}.post_status = 'publish' AND EXISTS (SELECT * FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = 'marcato_workshop_id' AND {$wpdb->postmeta}.meta_value = '{$_GET['workshop_id']}' AND {$wpdb->postmeta}.post_id = {$wpdb->posts}.id)";
		}
		return $where;
	}
	public function build_menus(){
		// add_object_page("Marcato XML Importer","Marcato","import","marcatoxmlsettings",array($this,'admin_page'),plugin_dir_url(__FILE__)."/images/wp_marcato_logo.png");
		add_options_page('Marcato XML Options','Marcato','manage_options','marcatoxml-options',array($this,'admin_page'),plugins_url().'/marcato/images/wp_marcato_logo.png');
	}
	public function admin_page(){
		$field = 'marcato_organization_id';
		$field_value = get_option('marcato_organization_id');
		if (!current_user_can('manage_options')){
			wp_die( __('You do not have sufficient permissions to access this page.'));
		}
		if( isset($_POST['marcato_submit_hidden']) && $_POST['marcato_submit_hidden'] == 'Y'){
			$field_value = $_POST[$field];
			update_option($field, $field_value);
			$this->importer->marcato_organization_id = $field_value;
			if (isset($_POST['Submit'])){
				?>
				<div class="updated"><p><strong><?php _e('settings saved.','marcatoxml-options'); ?></strong></p></div>
				<?php
			}
			if (isset($_POST['marcato_import'])){
				$results = $this->import_all();
				$errors = array();
				foreach($results as $result){
					if(is_string($result))
					$errors[] = $result;
				}
				?>
				<div class="updated"><p><strong>
					<?php if (!empty($errors)) {
							foreach($errors as $error){
								echo $error."<br/>";
							}
						}else{
							_e('Marcato XML Feed Imported','marcatoxml-options'); 
						}
					?>
				</strong></p></div>
				<?php
			}	
		}
		echo '<div class="wrap">';
		echo "<h2>" . __('Marcato XML Plugin Settings', 'marcatoxml-options') . "</h2>";
		?>
		<form name="marcatoxmlsettings" method="post" action="">
			<input type="hidden" name="marcato_submit_hidden" value="Y">
			<p>
				Marcato Organization ID
				<input type="text" name="<?php echo $field ?>" value="<?php echo $field_value ?>">
			</p>
			<hr />
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
				<input type="submit" name="marcato_import" class="button-secondary" value="<?php esc_attr_e('Import Now') ?>" />
			</p>
		</form>
	<?php
	}
}
class marcatoxml_importer {		

	public $marcato_organization_id;
	public $fields = array("artists","venues","shows","workshops");
	public $marcato_xml_url = "http://marcatoweb.com/xml";
		
	function marcatoxml_importer(){
		$this->marcato_organization_id = get_option('marcato_organization_id');
	}	
	
	public function import_all(){
		$org_id = $this->marcato_organization_id;
		if (empty($org_id)){
			return array("Organization ID is not set.");
		}
		$results = array();
		foreach($this->fields as $field){
			$results[] = $this->import($field);
		}
		if($this->generate_schedule_page()){
			$results[]="Schedule page generated successfully.";
		};
		return $results;
	}
	
	public function import($field) {
		$org_id = $this->marcato_organization_id;
		if (empty($org_id)){
			return "Error importing {$field}: Organization ID is not set";
		}
		$result;
		$errors = array();
		if ($posts = $this->get_posts($field)){
			foreach ($posts as $post){
				if(!$this->import_post($post)){
					$errors[0] = $post['post_title'];					
				}
			}
		}else{
			return "Error importing {$field}: Error loading xml file.";
		}
		return "{$field} Imported.\n" . implode("\n", $errors);
	}
	
	private function get_xml_location($field){
		return $this->marcato_xml_url . '/' . $field . '_' . $this->marcato_organization_id . '.xml';
	}
	private function get_posts($field) {
		$xml = @simplexml_load_file($this->get_xml_location($field));
		if ($xml){
			if($field == 'artists'){
				return $this->parse_artists($xml);
			}if($field == 'venues'){
				return $this->parse_venues($xml);
			}else if ($field == "shows"){
				return $this->parse_shows($xml);
			}else if ($field == "workshops"){
				return $this->parse_workshops($xml);
			}else{
				return array();
			}
			wp_import_cleanup($xml['id']);
		}else{
			return false;
		}		
	}

	private function import_post($post){
	 	global $wpdb;
		extract($post);
		$meta = $wpdb->get_row("SELECT * FROM $wpdb->postmeta WHERE meta_key = '{$post_type}_id' AND meta_value = '$post_marcato_id'");
		if ($meta) {
			$existing_post_id = $meta->post_id;
			//If exists, update;
			$post['ID'] = $existing_post_id;
			if ($updated_post_id = wp_update_post($post)){
				return $existing_post_id;
			}else{
				return "Error updating {$post_title}.";
			}
		} else {
 			//If it doesn't exist create;
			$post['post_status'] = "pending";
			$post['comment_status'] = 'closed';
			if($post_id = wp_insert_post($post)){
				add_post_meta($post_id, "{$post_type}_id", $post_marcato_id, true);
				return $post_id;
			}else{
				return "Error creating {$post_title}.";
			}
		}
	}
	
	private function parse_artists($xml){
		global $wpdb;
   	$index = 0;
		$posts = array();
		foreach ($xml->artist as $artist) {
			$post_title = $artist->name;
			$post_content = "";
			$post_content .= "<div class='artist_homebase'>" . $artist->homebase . "</div>";
			if (!empty($artist->web_photo_url)){
				$post_content .= "<img src='".$artist->web_photo_url."' class='artist_photo'>";
			}
			$post_content .= "<div class='artist_bio'>" . $artist->bio_public . "</div>";
			$post_content .= "<div class='artist_websites'>";
			foreach($artist->websites as $website){
				$post_content .= "<a class='artist_website' href='".$website->website->url."'>".$website->website->name."</a>";
			}
			$post_content .= "</div>";
			$post_type = "marcato_artist";
			$post_marcato_id = intval($artist->id);
			$posts[$index] = compact('post_content', 'post_title','post_type', 'post_marcato_id','post_status');
			$index++;
		}
		return $posts;
	}
	
	private function parse_venues($xml){
		global $wpdb;
   	$index = 0;
		$posts = array();
		foreach ($xml->venue as $venue) {
			$post_title = $venue->name;
			$post_content = "";
			$post_content .= "<div class='venue_community'>" . $venue->community . "</div>";
			if (!empty($venue->photo_url)){
				$post_content .= "<img src='".$venue->photo_url."' class='venue_photo'>";
			}
			$post_content .= "<div class='venue_address'>";
			$post_content .= "<span class='city'>" . $venue->city . "</span>";
			$post_content .= "<span class='province_state'>" . $venue->province_state . "</span>";
			$post_content .= "<span class='country'>" . $venue->country . "</span>";
			$post_content .= "<span class='postal_code'>" . $venue->postal_code . "</span>";
			$post_content .= "</div>";
			$post_content .= "<div class='venue_phone'>" . $venue->primary_phone_number . "</div>";
			$post_type = "marcato_venue";
			$post_marcato_id = intval($venue->id);
			$posts[$index] = compact('post_content', 'post_title', 'post_type', 'post_marcato_id', 'post_status');
			$index++;
		}
		return $posts;
	}
	
	private function parse_shows($xml){
		global $wpdb;
   	$index = 0;
		$posts = array();
		foreach ($xml->show as $show) {
			$post_title = $show->name;			
			$post_content = "";
			$post_content .= "<div class='show_time'>";
			$post_content .= "<span class='date'>" .date_i18n(get_option('date_format'), strtotime($show->date)) . "</span>";
			$post_content .= "<span class='show_time'><span class='start_time'>".date_i18n(get_option('time_format'), strtotime($show->date . ' ' . $show->formatted_start_time))."</span>";
			if (!empty($show->formatted_end_time)){
				$post_content .= "<span class='time_divider'>-</span><span class='end_time'>".date_i18n(get_option('time_format'), strtotime($show->date . ' ' . $show->formatted_end_time))."</span>";
			}
			$post_content .= "</div>";
			$venue_name = (string)$show->venue_name;
			$post_content .= "<div class='show_venue'><a class='show_venue_link' href='".add_query_arg('venue_name',$venue_name,get_post_type_archive_link('marcato_venue'))."'>" . $show->venue_name . "</a></div>";
			if (!empty($show->poster_url)){
				$post_content .= "<img src='".$show->poster_url."' class='show_photo'>";
			}
			$post_content .= "<div class='show_ticket_info'>";
			$post_content .= "<span class='price'>" . $show->price . "</span>";
			$post_content .= "<span class='ticket_info'>" . $show->ticket_info . "</span>";
			$post_content .= "<a class='ticket_link' href='" . $show->ticket_link . "'>".$show->ticket_link."</a>";
			$post_content .= "</div>";
			$post_content .= "<div class='show_description'>" . $show->description_web . "</div>";
			$post_content .= "<table class='show_lineup'>";
			foreach ($show->performances as $performances){
				foreach($performances->performance as $performance){
					$post_content .= "<tr class='performance'>";
					$artist_name = (string)$performance->artist;
					$post_content .= "<td class='performance_time'><span class='performance_start'>".date_i18n(get_option('time_format'), strtotime($show->date . ' ' . $performance->start))."</span>";
					if (!empty($performance->end)){
						$post_content .= "<span class='time_divider'>-</span><span class='performance_end'>".date_i18n(get_option('time_format'), strtotime($show->date . ' ' . $performance->end))."</span>";
					}
					$post_content .= "</td>";
					$post_content .= "<td class='artist'><a class='performance_artist_link' href='".add_query_arg('artist_name',$artist_name,get_post_type_archive_link('marcato_artist'))."'>" .$performance->artist . "</a></td>";
					$post_content .= "</tr>";
				}
			}
			$post_content .= "</table>";
			$post_type = "marcato_show";
			$post_marcato_id = intval($show->id);
			$posts[$index] = compact('post_content', 'post_title', 'post_type', 'post_marcato_id');
			$index++;
		}
		return $posts;
	}
	
	private function parse_workshops($xml){
		global $wpdb;
   	$index = 0;
		$posts = array();
		foreach ($xml->workshop as $workshop) {
			$post_title = $workshop->name;
			$post_content = "";
			$post_content .= "<div class='workshop_time'>";
			$post_content .= "<span class='date'>" .date_i18n(get_option('date_format'), strtotime($workshop->date)). "</span>";
			$post_content .= "<span class='start_time'>".date_i18n(get_option('time_format'), strtotime($workshop->date . ' ' . $workshop->formatted_start_time)) . "</span>";
			if (!empty($workshop->formatted_end_time)){
				$post_content .= "<span class='time_divider'>-</span><span class='end_time'>".date_i18n(get_option('time_format'), strtotime($workshop->date . ' ' . $workshop->formatted_end_time))."</span>";
			}
			$post_content .= "</div>";
			$venue_name = (string)$workshop->venue_name;
			$post_content .= "<div class='workshop_venue'><a class='workshop_venue_link' href='".add_query_arg('venue_name',$venue_name,get_post_type_archive_link('marcato_venue'))."'>" . $workshop->venue_name . "</a></div>";
			if (!empty($workshop->poster_url)){
				$post_content .= "<img src='".$workshop->poster_url."' class='workshop_photo'>";
			}
			$post_content .= "<div class='workshop_ticket_info'>";
			$post_content .= "<span class='price'>" . $workshop->price . "</span>";
			$post_content .= "<span class='ticket_info'>" . $workshop->ticket_info . "</span>";
			$post_content .= "<a class='ticket_link' href='" . $workshop->ticket_link . "'>".$workshop->ticket_link."</a>";
			$post_content .= "</div>";
			$post_content .= "<div class='workshop_description'>" . $workshop->description_web . "</div>";
			
			$post_content .= "<div class='workshop_types'>";
			foreach ($workshop->workshop_type as $type){
				$post_content .= "<span class='workshop_type'>".$type->name."</span>";
			}
			$post_content .= "</div>";

			$post_content .= "<table class='workshop_lineup'>";
			foreach ($workshop->presentations as $presentations){
				foreach($presentations->presentation as $presentation){
					$post_content .= "<tr class='presentation'>";
					$post_content .= "<td class='presentation_time'><span class='presentation_start'>".date_i18n(get_option('time_format'), strtotime($workshop->date . ' ' . $presentation->start))."</span>";
					if(!empty($presentation->end)){
						$post_content .= "<span class='time_divider'>-</span><span class='presentation_end'>".date_i18n(get_option('time_format'), strtotime($workshop->date . ' ' . $presentation->end))."</span>";
					}
					$post_content .= "</td>";
					if ($presentation->presenter_type == "artist"){
						$artist_name = (string)$presentation->presenter;
						$post_content .= "<td class='presenter'><a class='presentation_presenter_link' href='".add_query_arg('artist_name',$artist_name,get_post_type_archive_link('marcato_artist'))."'>".$presentation->presenter."</a></td>";
					}else{
						$post_content .= "<td class='presenter'>".$presentation->presenter."</td>";
					}
					$post_content .= "</tr>";
				}
			}
			$post_content .= "</table>";
			$post_type = "marcato_workshop";
			$post_marcato_id = intval($workshop->id);
			$posts[$index] = compact('post_content', 'post_title', 'post_type', 'post_marcato_id');
			$index++;
		}
		return $posts;
	}
	
	public function generate_schedule_page(){
		$workshop_xml = @simplexml_load_file($this->get_xml_location('workshops'));
		$show_xml = @simplexml_load_file($this->get_xml_location('shows'));
		if(!$workshop_xml && !$show_xml){return false;}
		if(!$workshop_xml){ $workshop_xml = array(); }
		if(!$show_xml) { $show_xml = array(); }
	
		$post_title = "Schedule";
		$post_content = "";
		$events = array();
		foreach($workshop_xml->workshop as $workshop){ 
			$events[] = $workshop; 
		}
		foreach($show_xml->show as $show){ 
			$events[]= $show; 
		}
		function sort_by_datetime($a, $b){
			$a_date = strtotime($a->date . ' ' . $a->formatted_start_time);
			$b_date = strtotime($b->date . ' ' . $b->formatted_start_time);
			if ($a_date == $b_date){return 0;}
			return ($a_date < $b_date) ? -1 : 1;
		}
		usort($events, 'sort_by_datetime');
		foreach($events as $event){
			if ($event->performances){
				$types = 'performances';
				$type = 'performance';
				$person = "artist";
				$archive_link_type = "marcato_show";
				$link_query = "show_id";
			}else if ($event->presentations){
				$types = 'presentations';
				$type = 'presentation';
				$person = "presenter";
				$archive_link_type = "marcato_workshop";
				$link_query = "workshop_id";
			}
			$post_content .= "<div class='schedule_event'>";
			$post_content .= "<div class='schedule_event_title'><a href='".add_query_arg($link_query,(string)$event->id,get_post_type_archive_link($archive_link_type))."'>".$event->name."</a></div>";
			$post_content .= "<div class='schedule_time'>";
			$post_content .= "<span class='date'>".date_i18n(get_option('date_format'), strtotime($event->date))."</span>";
			$post_content .= "<span class='start_time'>".date_i18n(get_option('time_format'), strtotime($event->date . ' ' . $event->formatted_start_time))."</span>";
			if (!empty($event->formatted_end_time)){
				$post_content .= "<span class='time_divider'>-</span><span class='end_time'>".date_i18n(get_option('time_format'), strtotime($event->date . ' ' . $event->formatted_end_time))."</span>";
			}
			$post_content .= "</div>";
			$venue_name = (string)$event->venue_name;
			$post_content .= "<div class='schedule_venue'><a class='schedule_venue_link' href='".add_query_arg('venue_name',$venue_name,get_post_type_archive_link('marcato_venue'))."'>".$venue_name."</a></div>";
			$post_content .= "<table class='schedule_timeslots'>";
			foreach($event->$types as $slots){
				foreach($slots->$type as $timeslot){
					if($person == "artist" || ($person=="presenter" && (string)$timeslot->presenter_type=="artist")){
						$post_content .= "<tr><td class='time'>".date_i18n(get_option('time_format'), strtotime($event->date . ' ' . $timeslot->start))."</td><td class='artist'><a href='".add_query_arg('artist_name',(string)$timeslot->$person,get_post_type_archive_link('marcato_artist'))."'>".$timeslot->$person."</a></td></tr>";
					}else{
						$post_content .= "<tr><td class='time'>".date_i18n(get_option('time_format'), strtotime($event->date . ' ' . $timeslot->start))."</td><td class='artist'>".$timeslot->$person."</td></tr>";
					}
				}
			}
			$post_content .= "</table>";
			$post_content .= "</div>";
		}
		$post_type = 'page';
		$post_name = 'schedule';
		$page = compact('post_content', 'post_title', 'post_type','post_name');
		$post_marcato_id = 'marcato_schedule';
		global $wpdb;
		$meta = $wpdb->get_row("SELECT * FROM $wpdb->postmeta WHERE meta_key = '{$post_type}_id' AND meta_value = '$post_marcato_id'");
		if ($meta){
			$existing_page_id = $meta->post_id;
			$page['ID'] = $existing_page_id;
			wp_update_post($page);
		}else{
			$page['post_status'] = 'pending';
			$page_id = wp_insert_post($page, true);
			add_post_meta($page_id, "{$post_type}_id", $post_marcato_id, true);
		}
		return true;
	}
}
?>