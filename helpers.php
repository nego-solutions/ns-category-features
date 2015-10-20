<?php if (!defined('FW')) die('Forbidden');


define(NS_CAT_FEAT_URL, plugin_dir_url(__FILE__));


function ns_get_cat_image_url($term_id="", $size="full"){

	if(!empty($term_id)){

		//fw()->extensions->get('ns-category-image')->ns_taxonomy_image_url($term_id, $size);

		$cat_img = new ns_category_image;

		return $cat_img->ns_taxonomy_image_url($term_id, $size);
	}

}
