<?php
/*
Plugin Name: Super Excerpts
Plugin URI: http://wpgurus.net/
Description: Automatically inserts excerpts on selected parts of your site and allows you to change things like excerpt length and read more text.
Version: 1.0
Author: Hassan Akhtar
Author URI: http://wpgurus.net/
License: GPL2
*/

/**********************************************
*
* Core functionality
*
***********************************************/

function wpse_get_excerpt($settings){
	global $post;
	$excerpt_text 	= (strlen($post->post_excerpt))?($post->post_excerpt):($post->post_content);
	$more_text 		= (isset($settings['read_more_text']) && $settings['read_more_text'])?$settings['read_more_text']:'Read More';
	$more_text 		= '<p><a href="'.get_permalink().'">'.$more_text.'</a></p>';
	$word_count 	= (isset($settings['word_count']) && is_numeric($settings['word_count']))?$settings['word_count']:55;
	return wp_trim_words($excerpt_text, $word_count, $more_text);
}

add_filter('the_content', 'wpse_replace_content');
function wpse_replace_content($content) {
	$settings = get_option('wpse_all_settings');
	if($settings && ((isset($settings['display_on']['category']) && $settings['display_on']['category'] && is_category()) || (isset($settings['display_on']['tag']) && $settings['display_on']['tag'] && is_tag()) || (isset($settings['display_on']['date']) && $settings['display_on']['date'] && is_date()) || (isset($settings['display_on']['author']) && $settings['display_on']['author'] && is_author()) || (isset($settings['display_on']['search']) && $settings['display_on']['search'] && is_search()) || (isset($settings['display_on']['home']) && $settings['display_on']['home'] && is_home()))){
		return wpse_get_excerpt($settings);
	}
	return $content;
}

add_filter('the_excerpt', 'wpse_replace_excerpt');
function wpse_replace_excerpt(){
	$settings = get_option('wpse_all_settings');
	return wpse_get_excerpt($settings);
}

/**********************************************
*
* Include Options
*
***********************************************/

include('options-panel.php');