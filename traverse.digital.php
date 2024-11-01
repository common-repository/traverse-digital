<?php

/**
 * Plugin Name: Traverse Digital
 * Plugin URI: http://traverse.digital/
 * Description: Create maps using geo-location data for your image posts displaying where your pictures are taken. Take control over how you share your location.
 * Version: 0.1
 * Author: jackreichert
 * Author URI: http://www.jackreichert.com/
 * License: GPL3
 */
class Traverse_Digital {
	public $options;

	/*
	 * Class construct, the magic is all set up here
	 */
	function __construct() {
		$this->load_dependencies();
		$this->plugin_options();
		$this->load_actions();
		$this->register_custom_types();
	}

	/*
	 * Include all required files here
	 */
	private function load_dependencies() {
		require_once( plugin_dir_path( __FILE__ ) . '/inc/EXIFread.php' );
		require_once( plugin_dir_path( __FILE__ ) . '/inc/custom-post-tax.php' );
		require_once( plugin_dir_path( __FILE__ ) . '/inc/plugin-settings.php' );
	}

	/*
	 * Sets up the admin options page
	 */
	private function plugin_options() {
		$this->options = get_option( 'traverse' );
		if ( is_admin() ) {
			$this->TD_Settings = new TD_Settings( $this->options );
		}
	}

	/*
	 * WordPress actions and filters set up here
	 */
	private function load_actions() {
		if ( '' != $this->options['googlemaps_api_key'] ) {
			add_action( 'save_post', array( $this, 'process_posts' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_resources' ) );
			add_action( 'the_content', array( $this, 'add_map' ) );
		}
	}

	/*
	 * Create the 'traverse' post_type
	 */
	private function register_custom_types() {
		if ( 'yes' == $this->options['traverse_post_type'] ) {
			$this->TD_Custom_Types = new TD_Custom_Types();
		}
	}

	/*
	 * Adds map to post content
	 */
	function add_map( $content ) {
		global $post;

		$markers    = array();
		$latitudes  = get_post_meta( $post->ID, 'geo_latitude' );
		$longitudes = get_post_meta( $post->ID, 'geo_longitude' );
		foreach ( $latitudes as $i => $lat ) {
			$markers[ $i ]['lat'] = floatval( $lat );
			$markers[ $i ]['lng'] = floatval( $longitudes[ $i ] );
		}
		$m       = json_encode( $markers );
		$map_div = "<div class='traverse_map' data-markers='$m'></div>";
		$content = ( ( 'prepend' == $this->options['map_post_location'] ) ? $map_div : '' ) . $content . ( ( 'append' == $this->options['map_post_location'] ) ? $map_div : '' );

		return $content;
	}

	/*
	 * enqueue scripts and styles
	 */
	function enqueue_resources() {
		global $post;
		if ( 'traverse' == get_post_type( $post->ID ) || ( 'no' == $this->options['traverse_post_type'] && 'post' == get_post_type( $post->ID ) ) ) {
			wp_enqueue_style( 'traverse-style', plugins_url( '/css/traverse.css', __FILE__ ) );
			wp_enqueue_script( 'traverse-script', plugins_url( '/js/traverse.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'traverse-google-maps', sprintf( 'https://maps.googleapis.com/maps/api/js?key=%s&callback=traverseInitMap', $this->options["googlemaps_api_key"] ), array( 'traverse-script' ), '1.0.0', true );
		}
	}

	/*
	 * On save:
	 * - Extracts all images and saves exif geo data to postmeta
	 * - Calls google api/geocode saves cities and countries as taxonomy
	 */
	function process_posts( $post_id, $post ) {

		if ( 'traverse' == $post->post_type || ( 'no' == $this->options['traverse_post_type'] && 'post' == $post->post_type ) ) {
			$exifdata = array();
			$location = array( 'cities' => array(), 'countries' => array() );

			delete_post_meta( $post_id, 'geo_latitude' );
			delete_post_meta( $post_id, 'geo_longitude' );
			delete_post_meta( $post_id, 'geo_public' );

			$images = $this->get_images( $post->post_content );
			foreach ( $images as $i => $image ) {
				$exifdata[ $i ] = $this->get_exif( $image );
				if ( 0 != $exifdata[ $i ]['Latitude'] && 0 != $exifdata[ $i ]['Longitude'] ) {
					if ( 'yes' == $this->options['traverse_post_type'] ) {
						$l = $this->get_location_data( $exifdata[ $i ]['Latitude'], $exifdata[ $i ]['Longitude'] );

						if ( is_array( $location['cities'] ) && ! in_array( $l['city'], $location['cities'] ) ) {
							$location['cities'][] = $l['city'];
						}
						if ( is_array( $location['countries'] ) && ! in_array( $l['country'], $location['countries'] ) ) {
							$location['countries'][] = $l['country'];
						}
					}

					add_post_meta( $post_id, 'geo_latitude', $exifdata[ $i ]['Latitude'] );
					add_post_meta( $post_id, 'geo_longitude', $exifdata[ $i ]['Longitude'] );
				}
			}

			if ( 'yes' == $this->options['traverse_post_type'] ) {
				wp_set_post_terms( $post_id, $location['cities'], 'city' );
				wp_set_post_terms( $post_id, $location['countries'], 'country' );
			}

			add_post_meta( $post_id, 'geo_public', 1, true );
		}
	}

	/*
	 * Extracts images from content on save
	 */
	private function get_images( $content ) {
		$dom           = new DOMDocument();
		$dom->encoding = 'utf-8';
		$src           = array();
		$the_content   = utf8_decode( do_shortcode( $content ) );
		if ( '' != $the_content ) {
			libxml_use_internal_errors(true);
			$dom->loadHTML( $the_content );
			libxml_clear_errors();
			$dom->preserveWhiteSpace = false;
			$images                  = $dom->getElementsByTagName( 'img' );


			foreach ( $images as $i => $image ) {
				$img_id = $this->get_image_id_from_src( $image->getAttribute( 'src' ) );
				if ( $img_id ) {
					$src[] = get_attached_file( $img_id );
				}
			}
		}

		return $src;
	}

	/*
	 * Queries postmeta table to find attachment id
	 */
	private function get_image_id_from_src( $src ) {
		global $wpdb;
		$basename = basename( $src );
		$query    = $wpdb->prepare( "SELECT post_id FROM `$wpdb->postmeta` WHERE meta_value LIKE '%%%s%%'", $basename );

		return $wpdb->get_var( $query );
	}

	/*
	 * gets exif data
	 */
	private function get_exif( $image ) {
		$EXIFread = new EXIFread( $image );
		$exif     = $EXIFread->getGPS();

		return $exif;
	}

	/*
	 * api/geocode api call
	 */
	private function get_location_data( $lat, $lon ) {
		// with the api key it doesn't seem to work.
//		$url = sprintf( 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%f,%f&key=%s&sensor=true', $lat, $lon, $this->options["googlemaps_api_key"] );
		$url        = sprintf( 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%f,%f&sensor=true', $lat, $lon );
		$response   = wp_remote_get( $url, array( 'timeout' => 30, 'httpversion' => '1.1' ) );
		$taxonomies = array();
		if ( ! is_wp_error( $response ) ) {
			$location_data = json_decode( $response['body'] );
			foreach ( $location_data->results as $result ) {
				if ( isset( $result->address_components ) && 0 < count( $result->address_components ) ) {
					foreach ( $result->address_components as $component ) {
						if ( ! isset( $taxonomies['city'] ) && isset( $component->types ) && is_array( $component->types ) && in_array( 'locality', $component->types ) ) {
							$taxonomies['city'] = $component->long_name;
						}
						if ( ! isset( $taxonomies['country'] ) && isset( $component->types ) && is_array( $component->types ) && in_array( 'country', $component->types ) ) {
							$taxonomies['country'] = $component->long_name;
						}
					}
				}
			}
		}

		return $taxonomies;
	}

}

$Traverse_Digital = new Traverse_Digital();