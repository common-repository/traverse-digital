<?php
/*
 * Create 'Traverse' custom post type
 * Create City, Country taxonomies
 */
class TD_Custom_Types {
	function __construct() {
		add_action( 'init', array( $this, 'create_location_type' ) );
		add_action( 'init', array( $this, 'create_location_tax' ) );
	}

// Register Custom Post Type
	function create_location_type() {

		$labels = array(
			'name'               => _x( 'Traverse', 'Post Type General Name', 'traverse' ),
			'singular_name'      => _x( 'Location', 'Post Type Singular Name', 'traverse' )
		);
		$args   = array(
			'label'               => __( 'Location', 'traverse' ),
			'description'         => __( 'Locations', 'traverse' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', ),
			'taxonomies'          => array( 'city', 'country', 'post_tag', 'category' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'traverse', $args );

	}

	// Register Custom Taxonomy
	function create_location_tax() {

		$labels = array(
			'name'                       => _x( 'Cities', '', 'traverse' ),
			'singular_name'              => _x( 'City', '', 'traverse' )
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'city', array( 'traverse' ), $args );

		$labels = array(
			'name'                       => _x( 'Countries', '', 'traverse' ),
			'singular_name'              => _x( 'Country', '', 'traverse' )
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'country', array( 'traverse' ), $args );

	}

}