<?php
/*
 * Sets up admin settings page
 */
class TD_Settings {
	public static $options = array();

	/*
	 * Constructs admin settings page
	 */
	public function __construct( $options = array() ) {
		$this->init_options( $options );
		$this->load_actions();
	}

	/*
	 * Loads WordPress actions and filters
	 */
	private function load_actions() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_error_notice' ) );
	}

	/*
	 * Sets up plugin options
	 */
	private function init_options( $options ) {
		$this->options = ( 0 < count( $options ) ) ? $options : get_option( 'traverse' );
	}

	/*
	 * Nag banner if there's no Google Maps API key
	 */
	public function admin_error_notice(){
		if ( '' == $this->options['googlemaps_api_key'] ) {
			$class = "update-nag";
			$message = "It looks like you haven't set up the settings for <a href='".admin_url('/options-general.php?page=traverse-digital-settings')."'>Traverse Digital</a> yet.";
			echo"<div class=\"$class\">$message</div>";
		}
	}

	/*
	 * Creates plugin page in Settings menu
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'Traverse Digital Settings',       // Page title
			'Traverse Digital',          // Menu title
			'manage_options',       // Capability
			'traverse-digital-settings', // Menu slug
			array( $this, 'create_admin_page' ) // Function
		);
	}

	/*
	 * Admin settings page HTML
	 */
	public function create_admin_page() { ?>
		<div class="wrap">
			<h2>Traverse Digital Settings</h2>

			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'traverse_option_group' );
				do_settings_sections( 'traverse-digital-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/*
	 * Register plugin settings
	 */
	public function page_init() {
		register_setting(
			'traverse_option_group', // Option group
			'traverse', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		/*
		 * Google Maps settings section
		 */
		add_settings_section(
			'googlemaps_api_settings', // ID
			'Google Maps Api', // Title
			array( $this, 'googlemaps_api_settings_info' ), // Callback
			'traverse-digital-settings' // Page
		);

		add_settings_field(
			'googlemaps_api_key', // ID
			'Google Maps API key', // Title
			array( $this, 'googlemaps_api_key_callback' ), // Callback
			'traverse-digital-settings', // Page
			'googlemaps_api_settings' // Section
		);

		add_settings_field(
			'map_post_location', // ID
			'Where would you like the map?', // Title
			array( $this, 'map_post_location_callback' ), // Callback
			'traverse-digital-settings', // Page
			'googlemaps_api_settings' // Section
		);

		add_settings_field(
			'traverse_post_type', // ID
			'Would you like a special Post Type (Traverse) to be created for your location posts?', // Title
			array( $this, 'traverse_post_type_callback' ), // Callback
			'traverse-digital-settings', // Page
			'googlemaps_api_settings' // Section
		);

	}

	/*
	 * Section description
	 */
	function googlemaps_api_settings_info() {
	}

	/*
	 * Field callbacks
	 */
	function googlemaps_api_key_callback() {
		printf(
			'<input type="text" id="googlemaps_api_key" name="traverse[googlemaps_api_key]" value="%s" />',
			isset( $this->options['googlemaps_api_key'] ) ? esc_attr( $this->options['googlemaps_api_key'] ) : ''
		);
		echo "<p>In order for Google Maps to work, you'll need a Google Maps API Key. The key is free for roughly 25k map generations/day. For more information on obtaining an API Key, visit <a href='https://developers.google.com/maps/documentation/javascript/tutorial'>Google's developer resources</a>.</p>";
	}

	function map_post_location_callback() { ?>
		<select id="map_post_location" name="traverse[map_post_location]">
			<option value="prepend" <?php echo selected( 'prepend', $this->options['map_post_location'] ); ?>>Before the
				post content
			</option>
			<option value="append" <?php echo selected( 'append', $this->options['map_post_location'] ); ?>>After the
				post content
			</option>
		</select>
		<?php
	}

	function traverse_post_type_callback() { ?>
		<select id="traverse_post_type" name="traverse[traverse_post_type]">
			<option value="yes" <?php echo selected( 'yes', $this->options['traverse_post_type'] ); ?>>Yes
			</option>
			<option value="no" <?php echo selected( 'no', $this->options['traverse_post_type'] ); ?>>No
			</option>
		</select>
		<p><b>Note:</b> If 'No' is selected the functionality will be applied to posts.</p>
		<?php
	}

	/*
	 * Sanitize
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['googlemaps_api_key'] ) ) {
			$new_input['googlemaps_api_key'] = sanitize_text_field( $input['googlemaps_api_key'] );
		}

		if ( isset( $input['map_post_location'] ) ) {
			$new_input['map_post_location'] = sanitize_text_field( $input['map_post_location'] );
		}

		if ( isset( $input['traverse_post_type'] ) ) {
			$new_input['traverse_post_type'] = sanitize_text_field( $input['traverse_post_type'] );
		}

		return $new_input;
	}
}