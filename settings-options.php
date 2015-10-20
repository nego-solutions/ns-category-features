<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


$z_taxs = array();
$disabled_taxonomies = array('nav_menu', 'link_category', 'post_format');

foreach (get_taxonomies() as $tax){

	if (in_array($tax, $disabled_taxonomies)) continue;

	$z_taxs[$tax] = $tax;
}


$options = array(

	'general-tab' => array(
		'title'   => 'General',
		'type'    => 'tab',
		'options' => array(
			'default_placeholder' => array(
				'label'   => __( 'Image placeholder', 'fw' ),
				'type'    => 'upload',
			),
			'visual_desc' => array(
				'label'   => __( 'Enable Visual Editor in description', 'fw' ),
				'type'    => 'radio',
				'choices' => array('no'=>'No', 'yes'=>'Yes'),
			),
			'excluded_taxonomies' => array(
				'label'   => __( 'What taxonomies don\'t need upload image option?', 'fw' ),
				'type'    => 'checkboxes',
				'choices' => $z_taxs,
			),
		)
	),


);