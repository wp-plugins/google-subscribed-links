<?php
/*
Plugin Name: Google Subscribed Links
Plugin Author: Ankur Kothari
Plugin URI: http://lipidity.com/web/wordpress/google-subscribed-links/
Author URI: http://lipidity.com/
Version: 0.1
Description: Subscribers see your site's results first and highlighted in Google.
*/

function coop_feedsmith_compat(){
	if(isset($_REQUEST['coop'])) {
		/* feedsmith compatibility */
		remove_action('template_redirect', 'ol_feed_redirect');
		remove_action('init','ol_check_url');
		/* full text feed compatibility */
		remove_filter('the_content', 'restore_text');
		/* no. of posts */
		add_filter('post_limits','coop_post_limits');
	}
}
add_action('init', 'coop_feedsmith_compat', 1);

function coop_post_limits ($s) {
	if(is_feed() && isset($_REQUEST['limit'])) {
		$limit = (int) $_REQUEST['limit'];
		return (empty($limit)) ? $s : ($limit < 1) ? '' : "LIMIT $limit";
	}
	return $s;
}

function coop_google_ns(){
	echo 'xmlns:coop="http://www.google.com/coop/namespace"
	';
}
add_action('rss2_ns', 'coop_google_ns');

function coop_google_keywords(){
	ob_start();
	the_category_rss();
	$echoed = ob_get_contents();
	ob_end_clean();
	/* cheap but effective */
	echo str_replace(array('category>','dc:subject>'), 'coop:keyword>', $echoed);
}
add_action('rss2_item', 'coop_google_keywords');

function coop_content($text='') { // Fakes an excerpt if needed
	global $post;
	if ( '' == $text )
		$text = get_the_content('');
//	$text = apply_filters('the_content', $text);
	$text = str_replace(']]>', ']]&gt;', $text);
	$text = strip_tags($text);
	$excerpt_length = 55;
	$words = explode(' ', $text, $excerpt_length + 1);
	if (count($words) > $excerpt_length) {
		array_pop($words);
//		array_push($words, '[...]');
		$text = implode(' ', $words);
	}
	return $text;
}
add_filter('the_excerpt_rss', 'coop_content');
?>