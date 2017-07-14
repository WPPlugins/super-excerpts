<?php
/*
The settings page
*/

function wpse_menu_item() {
	global $wpse_settings_page_hook;
    $wpse_settings_page_hook = add_plugins_page(
        'Super Excerpts Settings',         			// The title to be displayed in the browser window for this page.
        'Super Excerpts',			        		// The text to be displayed for this menu item
        'administrator',            				// Which type of users can see this menu item  
        'wpse_settings',    						// The unique ID - that is, the slug - for this menu item
        'wpse_render_settings_page'     			// The name of the function to call when rendering this menu's page  
    );
}
add_action( 'admin_menu', 'wpse_menu_item' );

function wpse_scripts_styles($hook) {
	global $wpse_settings_page_hook;
	if( $wpse_settings_page_hook != $hook )
		return;
	wp_enqueue_style("options_panel_stylesheet", plugins_url( "static/css/options-panel.css" , __FILE__ ), false, "1.0", "all");
	wp_enqueue_script("options_panel_script", plugins_url( "static/js/options-panel.js" , __FILE__ ), false, "1.0");
	wp_enqueue_script('common');
	wp_enqueue_script('wp-lists');
	wp_enqueue_script('postbox');
}
add_action( 'admin_enqueue_scripts', 'wpse_scripts_styles' );

function wpse_render_settings_page() {
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"></div>
<h2>Super Excerpts</h2>
	<?php settings_errors(); ?>
	<div class="clearfix paddingtop20">
		<div class="first ninecol">
			<form method="post" action="options.php">
				<?php settings_fields( 'wpse_settings' ); ?>
				<?php do_meta_boxes('wpse_metaboxes','advanced',null); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			</form>
		</div>
		<div class="last threecol">
			<div class="side-block">
				Like the plugin? Give it a good rating on WordPress.org.
			</div>
		</div>
	</div>
</div>
<?php }

function wpse_create_options() {
	add_settings_section( 'general_settings_section', null, null, 'wpse_settings' );

	add_settings_field(
        'display_on', '', 'wpse_render_settings_field', 'wpse_settings', 'general_settings_section',
		array(
			'title' => 'Replace content with excerpt on',
			'desc' 	=> 'On these pages the content for each post will be replaced with the post excerpt',
			'id' 	=> 'display_on',
			'type' 	=> 'multicheckbox',
			'items' => array('category'=>'Category Archive', 'tag'=>'Tag Archive', 'date'=>'Date Archive', 'author'=>'Author Archive', 'search'=>'Search Results Page', 'home'=>'Blog Page'),
			'group' => 'wpse_all_settings'
		)
    );

    add_settings_field(
        'read_more_text', '', 'wpse_render_settings_field', 'wpse_settings', 'general_settings_section',
		array(
			'title' => 'Read more text',
			'desc' 	=> 'Text that you want to display as the read more link',
			'id' 	=> 'read_more_text',
			'type' 	=> 'text',
			'group' => 'wpse_all_settings'
		)
    );

    add_settings_field(
        'word_count', '', 'wpse_render_settings_field', 'wpse_settings', 'general_settings_section',
		array(
			'title' => 'Excerpt size',
			'desc' 	=> 'Word count of the excerpt',
			'id' 	=> 'word_count',
			'type' 	=> 'text',
			'group' => 'wpse_all_settings'
		)
    );

	register_setting('wpse_settings', 'wpse_all_settings', 'wpse_settings_validation');
}
add_action('admin_init', 'wpse_create_options');

function wpse_settings_validation($input){
	return $input;
}

function wpse_add_meta_boxes(){
	add_meta_box("wpse_general_settings_metabox", 'General Settings', "wpse_metaboxes_callback", "wpse_metaboxes", 'advanced', 'default', array('settings_section'=>'general_settings_section'));
}
add_action( 'admin_init', 'wpse_add_meta_boxes' );

function wpse_metaboxes_callback($post, $args){
	do_settings_fields( "wpse_settings", $args['args']['settings_section'] );
	submit_button('Save Changes', 'secondary', null, false);
}

function wpse_render_settings_field($args){
	$option_value = get_option($args['group']);
?>
	<div class="row clearfix">
		<div class="col colone"><?php echo $args['title']; ?></div>
		<div class="col coltwo">
	<?php if($args['type'] == 'text'): ?>
		<input type="text" id="<?php echo $args['id'] ?>" name="<?php echo $args['group'].'['.$args['id'].']'; ?>" value="<?php if(isset($option_value[$args['id']])){echo esc_attr($option_value[$args['id']]);} ?>">
	<?php elseif ($args['type'] == 'select'): ?>
		<select name="<?php echo $args['group'].'['.$args['id'].']'; ?>" id="<?php echo $args['id']; ?>">
			<?php foreach ($args['options'] as $key=>$option) { ?>
				<option <?php selected($option_value[$args['id']], $key); echo 'value="'.$key.'"'; ?>><?php echo $option; ?></option><?php } ?>
		</select>
	<?php elseif($args['type'] == 'checkbox'): ?>
		<input type="hidden" name="<?php echo $args['group'].'['.$args['id'].']'; ?>" value="0" />
		<input type="checkbox" name="<?php echo $args['group'].'['.$args['id'].']'; ?>" id="<?php echo $args['id']; ?>" value="1" <?php if(isset($option_value[$args['id']]))checked($option_value[$args['id']]); ?> />
	<?php elseif($args['type'] == 'textarea'): ?>
		<textarea name="<?php echo $args['group'].'['.$args['id'].']'; ?>" type="<?php echo $args['type']; ?>" cols="" rows=""><?php if ( $option_value[$args['id']] != "") { echo stripslashes(esc_textarea($option_value[$args['id']]) ); } ?></textarea>
	<?php elseif($args['type'] == 'multicheckbox'):
		foreach ($args['items'] as $key => $checkboxitem ):
	?>
		<div class="checkbox-item">
			<input type="hidden" name="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" value="0" />
			<input type="checkbox" name="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" id="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" value="1" <?php if(isset($option_value[$args['id']][$key])){checked($option_value[$args['id']][$key]);} ?> />
			<label for="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>"><?php echo $checkboxitem; ?></label>
		</div>
	<?php endforeach; ?>
	<?php elseif($args['type'] == 'multitext'):
		foreach ($args['items'] as $key => $textitem ):
	?>
		<label for="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>"><?php echo $textitem; ?></label><br/>
		<input type="text" id="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" name="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" value="<?php echo esc_attr($option_value[$args['id']][$key]); ?>"><br/>
	<?php endforeach; endif; ?>
		</div>
		<div class="col colthree"><small><?php echo $args['desc'] ?></small></div>
	</div>
<?php
}

?>