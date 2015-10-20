<?php

class ns_add_category_image{


	public function __construct(){

		add_action('admin_init', array($this, 'ns_init'));
		add_action('create_term', array($this, 'ns_save_taxonomy_image'));
		add_action('edit_term', array($this, 'ns_save_taxonomy_image'));

		// style the image in category list
		if ( strpos( $_SERVER['SCRIPT_NAME'], 'edit-tags.php' ) > 0 ) {
			add_action( 'admin_head', array($this, 'ns_add_style' ));
			add_action('quick_edit_custom_box', array($this, 'ns_quick_edit_custom_box'), 10, 3);
			add_filter('attribute_escape', array($this, 'ns_change_insert_button_text'), 10, 2);
		}
	}


	private function img_placeholder(){

		$default = fw_get_db_ext_settings_option('ns-category-features', 'default_placeholder', '');

		if(!empty($default)){
			$src = wp_get_attachment_image_src($default['attachment_id'], array(100,100));

			$img_src = $src[0];

		}else{
			$img_src = fw()->extensions->get('ns-category-features')->get_declared_URI('/static/images/image-placeholder.png');
		}

		return $img_src;
	}



	public function ns_init() {

		$taxonomies = get_taxonomies();

		if (is_array($taxonomies)) {

			$excluded_taxonomies = array_keys(fw_get_db_ext_settings_option('ns-category-features', 'excluded_taxonomies', array()));

			if (empty($excluded_taxonomies))
				$excluded_taxonomies = array();


		    foreach ($taxonomies as $ns_taxonomy) {

				if (in_array($ns_taxonomy, $excluded_taxonomies))
					continue;

		        add_action($ns_taxonomy.'_add_form_fields', array($this, 'ns_add_texonomy_field'));
				add_action($ns_taxonomy.'_edit_form_fields', array($this, 'ns_edit_texonomy_field'));
				add_filter( 'manage_edit-' . $ns_taxonomy . '_columns', array($this, 'ns_taxonomy_columns' ));
				add_filter( 'manage_' . $ns_taxonomy . '_custom_column', array($this, 'ns_taxonomy_column'), 10, 3 );
		    }
		}
	}



	// save our taxonomy image while edit or save term
	public function ns_save_taxonomy_image($term_id) {

	    if(isset($_POST['taxonomy_image']))
	        update_option('ns_taxonomy_image'.$term_id, $_POST['taxonomy_image']);
	}



	// change 'insert into post' to 'use this image'
	public function ns_change_insert_button_text($safe_text, $text) {
	    return str_replace("Insert into Post", "Use this image", $text);
	}


	// get attachment ID by image url
	public function ns_get_attachment_id_by_url($image_src) {

	    global $wpdb;

	    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid = '$image_src'";
	    $id = $wpdb->get_var($query);

	    return (!empty($id)) ? $id : NULL;
	}



	// Validating options
	public function ns_options_validate($input) {
		return $input;
	}



	// get taxonomy image url for the given term_id (Place holder image by default)
	public function ns_taxonomy_image_url($term_id = NULL, $size = NULL, $return_placeholder = FALSE) {

		if (!$term_id) {

			if (is_category())
				$term_id = get_query_var('cat');
			elseif (is_tax()) {
				$current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
				$term_id = $current_term->term_id;
			}
		}

	    $taxonomy_image_url = get_option('ns_taxonomy_image'.$term_id);
	    if(!empty($taxonomy_image_url)) {
		    $attachment_id = $this->ns_get_attachment_id_by_url($taxonomy_image_url);
		    if(!empty($attachment_id)) {
		    	if (empty($size))
		    		$size = 'full';
		    	$taxonomy_image_url = wp_get_attachment_image_src($attachment_id, $size);
			    $taxonomy_image_url = $taxonomy_image_url[0];
		    }
		}

	    if ($return_placeholder)
			return ($taxonomy_image_url != '') ? $taxonomy_image_url : $this->img_placeholder();
		else
			return $taxonomy_image_url;
	}



	// get taxonomy image for the given term_id
	public function ns_taxonomy_image($term_id = NULL, $size = 'full', $attr = NULL, $echo = TRUE) {

		if (!$term_id) {

			if (is_category())
				$term_id = get_query_var('cat');
			elseif (is_tax()) {
				$current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
				$term_id = $current_term->term_id;
			}
		}

	    $taxonomy_image_url = get_option('ns_taxonomy_image'.$term_id);

	    if(!empty($taxonomy_image_url)) {

		    $attachment_id = $this->ns_get_attachment_id_by_url($taxonomy_image_url);

		    if(!empty($attachment_id))
		    	$taxonomy_image = wp_get_attachment_image($attachment_id, $size, FALSE, $attr);
		    else {
		    	$image_attr = '';
		    	if(is_array($attr)) {
		    		if(!empty($attr['class']))
		    			$image_attr .= ' class="'.$attr['class'].'" ';
		    		if(!empty($attr['alt']))
		    			$image_attr .= ' alt="'.$attr['alt'].'" ';
		    		if(!empty($attr['width']))
		    			$image_attr .= ' width="'.$attr['width'].'" ';
		    		if(!empty($attr['height']))
		    			$image_attr .= ' height="'.$attr['height'].'" ';
		    		if(!empty($attr['title']))
		    			$image_attr .= ' title="'.$attr['title'].'" ';
		    	}
		    	$taxonomy_image = '<img src="'.$taxonomy_image_url.'" '.$image_attr.'/>';
		    }
		}

		if ($echo)
			echo $taxonomy_image;
		else
			return $taxonomy_image;
	}



	// add image field in add form
	public function ns_add_texonomy_field() {

		if (get_bloginfo('version') >= 3.5)
			wp_enqueue_media();
		else {
			wp_enqueue_style('thickbox');
			wp_enqueue_script('thickbox');
		}

		echo '<div class="form-field">
			<label for="taxonomy_image">' . __('Image', 'categories-images') . '</label>
			<input type="text" name="taxonomy_image" id="taxonomy_image" value="" />
			<br/>
			<button class="ns_upload_image_button button">' . __('Add image', 'categories-images') . '</button>
		</div>'.$this->ns_script();
	}



	// add image field in edit form
	public function ns_edit_texonomy_field($taxonomy) {

		if (get_bloginfo('version') >= 3.5)
			wp_enqueue_media();
		else {
			wp_enqueue_style('thickbox');
			wp_enqueue_script('thickbox');
		}

		if ($this->ns_taxonomy_image_url( $taxonomy->term_id, NULL, TRUE ) == $this->img_placeholder() )
			$image_text = "";
		else
			$image_text = $this->ns_taxonomy_image_url( $taxonomy->term_id, NULL, TRUE );
		echo '<tr class="form-field">
			<th scope="row" valign="top"><label for="taxonomy_image">' . __('Image', 'categories-images') . '</label></th>
			<td><img class="taxonomy-image" src="' . $this->ns_taxonomy_image_url( $taxonomy->term_id, NULL, TRUE ) . '"/><br/><input type="text" name="taxonomy_image" id="taxonomy_image" value="'.$image_text.'" /><br />
			<button class="ns_upload_image_button button">' . __('Upload/Add image', 'categories-images') . '</button>
			<button class="ns_remove_image_button button">' . __('Remove image', 'categories-images') . '</button>
			</td>
		</tr>'.$this->ns_script();
	}



	/**
	 * Thumbnail column added to category admin.
	 *
	 * @access public
	 * @param mixed $columns
	 * @return void
	 */
	public function ns_taxonomy_columns( $columns ) {
		$new_columns = array();
		$new_columns['cb'] = $columns['cb'];
		$new_columns['thumb'] = __('Image', 'categories-images');

		unset( $columns['cb'] );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Thumbnail column value added to category admin.
	 *
	 * @access public
	 * @param mixed $columns
	 * @param mixed $column
	 * @param mixed $id
	 * @return void
	 */
	public function ns_taxonomy_column( $columns, $column, $id ) {
		if ( $column == 'thumb' )
			$columns = '<span><img src="' . $this->ns_taxonomy_image_url($id, NULL, TRUE) . '" alt="' . __('Thumbnail', 'categories-images') . '" class="wp-post-image" /></span>';

		return $columns;
	}



	public function ns_add_style() {
		echo '<style type="text/css" media="screen">
			th.column-thumb {width:60px;}
			.form-field img.taxonomy-image {border:1px solid #eee;max-width:300px;max-height:300px;}
			.inline-edit-row fieldset .thumb label span.title {width:48px;height:48px;border:1px solid #eee;display:inline-block;}
			.column-thumb span {width:48px;height:48px;border:1px solid #eee;display:inline-block;}
			.inline-edit-row fieldset .thumb img,.column-thumb img {width:48px;height:48px;}
		</style>';
	}



	public function ns_quick_edit_custom_box($column_name, $screen, $name) {
		if ($column_name == 'thumb')
			echo '<fieldset>
			<div class="thumb inline-edit-col">
				<label>
					<span class="title"><img src="" alt="Thumbnail"/></span>
					<span class="input-text-wrap"><input type="text" name="taxonomy_image" value="" class="tax_list" /></span>
					<span class="input-text-wrap">
						<button class="ns_upload_image_button button">' . __('Upload/Add image', 'categories-images') . '</button>
						<button class="ns_remove_image_button button">' . __('Remove image', 'categories-images') . '</button>
					</span>
				</label>
			</div>
		</fieldset>';
	}



	// upload using wordpress upload
	public function ns_script() {

		return '<script type="text/javascript">
		    jQuery(document).ready(function($) {
				var wordpress_ver = "'.get_bloginfo("version").'", upload_button;
				$(".ns_upload_image_button").click(function(event) {
					upload_button = $(this);
					var frame;
					if (wordpress_ver >= "3.5") {
						event.preventDefault();
						if (frame) {
							frame.open();
							return;
						}
						frame = wp.media();
						frame.on( "select", function() {
							// Grab the selected attachment.
							var attachment = frame.state().get("selection").first();
							frame.close();
							if (upload_button.parent().prev().children().hasClass("tax_list")) {
								upload_button.parent().prev().children().val(attachment.attributes.url);
								upload_button.parent().prev().prev().children().attr("src", attachment.attributes.url);
							}
							else
								$("#taxonomy_image").val(attachment.attributes.url);
						});
						frame.open();
					}
					else {
						tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
						return false;
					}
				});

				$(".ns_remove_image_button").click(function() {
					$("#taxonomy_image").val("");
					$(this).parent().siblings(".title").children("img").attr("src","' . $this->img_placeholder() . '");
					$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");
					return false;
				});

				if (wordpress_ver < "3.5") {
					window.send_to_editor = function(html) {
						imgurl = $("img",html).attr("src");
						if (upload_button.parent().prev().children().hasClass("tax_list")) {
							upload_button.parent().prev().children().val(imgurl);
							upload_button.parent().prev().prev().children().attr("src", imgurl);
						}
						else
							$("#taxonomy_image").val(imgurl);
						tb_remove();
					}
				}

				$(".editinline").live("click", function(){
				    var tax_id = $(this).parents("tr").attr("id").substr(4);
				    var thumb = $("#tag-"+tax_id+" .thumb img").attr("src");
					if (thumb != "' . $this->img_placeholder() . '") {
						$(".inline-edit-col :input[name=\'taxonomy_image\']").val(thumb);
					} else {
						$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");
					}
					$(".inline-edit-col .title img").attr("src",thumb);
				    return false;
				});
		    });
		</script>';
	}

}