<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( '(NS) Category Features', 'fw' );
$manifest['description'] = __( 'Includes: visual description editor & upload custom image. Use <code>ns_get_cat_image_url($term_id, $size)</code> to get category image url.', 'fw' );
$manifest['version'] = '1.0.0';
$manifest['display'] = true;
$manifest['standalone'] = true;
$manifest['thumbnail'] = NS_EXT_IMG.'ns-avatar.png';

$manifest['github_update'] = 'nego-solutions/ns-category-features';
