<?php


if (!defined('FW')) die('Forbidden');


class FW_Extension_ns_category_features extends FW_Extension{


	public function _init(){

		new ns_add_category_image;

		$visual_editor = fw_get_db_ext_settings_option('ns-category-features', 'visual_desc', '');

		//enable visual editor
		if($visual_editor == 'yes'){

			add_action( 'wp_loaded', array($this, 'visual_term_description_editor'), 999 );
		}


	}


	/**
	 * Instantiates the class to work on all of the registered taxonomies
	 *
	 * @since 1.0
	 */
	function visual_term_description_editor() {

		/* Retrieve an array of registered taxonomies */
		$taxonomies = get_taxonomies( '', 'names' );
		$taxonomies = apply_filters( 'visual_term_description_taxonomies', $taxonomies );

		/* Initialize the class */
		$plugin = new Visual_Term_Description_Editor( $taxonomies );
		$plugin->run();

		/* Make class accessible to other plugins */
		add_filter( 'visual_term_description_editor', $plugin );
	}


}