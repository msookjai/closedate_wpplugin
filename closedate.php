<?php
/*
Plugin Name: WP Order Me
Plugin URI: http://
Description: Order anything to me.
Version: 0.0.1
Author: Macren Sookjai
Author URI: http://
License: GPLv2 or later
*/

$instance = new WPCloseDate();
$instance->execute();

/**
 * Class WPOrderMe
 */

class WPCloseDate
{
    /**
     * plugin text domain
     * @string
     */
    public $textdomain = 'cd';
    /**
     * @var string
     */
    public $post_type = 'close_date';

    public function execute()
    {
        // Initialize Custom post type.
        add_action('init', array($this, 'init'));
        add_action('save_post', array($this, 'save_date_meta'));
        add_action('add_meta_boxes', array($this, 'date_meta_box'));
        add_shortcode('close_date', array($this, 'close_date_shortcode'));
    }

    /**
     * Used while execute
     */
    public function init()
    {
        $labels = array(
            'name' => __('Close date', $this->textdomain),
            'singular_name' => __('Close date', $this->textdomain),
            'add_new' => __('Add New', $this->textdomain),
            'add_new_item' => __('Add New Close date', $this->textdomain),
            'edit_item' => __('Edit Close date', $this->textdomain),
            'new_item' => __('New Close date', $this->textdomain),
            'all_items' => __('All Close date', $this->textdomain),
            'view_item' => __('View Close date', $this->textdomain),
            'search_items' => __('Search Close date', $this->textdomain),
            'not_found' => __('No Close date found', $this->textdomain),
            'not_found_in_trash' => __('No Close date found in Trash', $this->textdomain),
            'parent_item_colon' => '',
            'menu_name' => __('Close date', $this->textdomain)
        );
        $args = array(
            'labels' => $labels,
            'description' => __('Manage Close date.', $this->textdomain),
            'public' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            // TODO Create an original menu icon
            //'menu_icon' => ???,
            'rewrite' => array('slug' => 'close_date'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'author')
        );
        register_post_type($this->post_type, $args);
    }
	public function date_meta_box() {
		add_meta_box(
			'close_date',
			__('Close Date', $this->textdomain),
			array($this, 'date_meta_box_content'),
			$this->post_type,
			'normal',
			'default',
			'date'
		);
		add_meta_box(
			'reason',
			__('Reason', $this->textdomain),
			array($this, 'date_meta_box_content'),
			$this->post_type,
			'normal',
			'default',
			'reason'
		);
	}
	public function save_date_meta(){
		global $post;
		if ( !wp_verify_nonce($_POST['datemeta_noncename'], plugin_basename(__FILE__) )) {
			return $post->ID;
		}
		if ( !current_user_can( 'edit_post', $post->ID )){
			return $post->ID;
		}
		$date_meta['_date'] = $_POST['_date'];
		$date_meta['_reason'] = $_POST['_reason'];
		foreach ($date_meta as $key => $value) { // Cycle through the $events_meta array!
			if($post->post_type == 'revision') return; // Don't store custom data twice
			//$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}

	}
	public function date_meta_box_content($post,$callback_args) {
		echo '<input type="hidden" name="datemeta_noncename" id="datemeta_noncename" value="'. 
			wp_create_nonce(plugin_basename(__FILE__)).'"/>';
		switch($callback_args['args']){
			case 'date':
				$date = get_post_meta($post->ID, '_date', true);
				echo '<input type="date" name="_date" value="'.$date.'" class="widefat" />';
				break;
			case 'reason':
				$reason = get_post_meta($post->ID, '_reason', true);
				echo '<input type="text" name="_reason" value="'.$reason.'" class="widefat" />';
				break;
		}
	}
	private function display_close_date(){
		$dates = get_posts(array(
			'post_type'   => 'close_date',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids'
			)
		);
		echo "<table>";
		echo "<tr><th>Date</th><th>Reason</th></tr>";
		foreach($dates as $key => $value){
			echo "<tr><td>";
			echo get_post_meta($value,"_date",true);
			echo "</td><td>";
			echo get_post_meta($value,"_reason",true);
			echo "</td></tr>";
		}
		echo "</table>";
	}
	public function close_date_shortcode(){
		$this->display_close_date();
	}
}
