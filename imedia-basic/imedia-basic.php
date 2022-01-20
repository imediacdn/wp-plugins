<?php

/**
* Plugin Name: -- iMedia Basic 
* Plugin URI: https://imediaone.com
* Description: CF Flexible SSL + Google,Bing,Clarity,Webmonitor,GA Verification + AUB + CF JQuery + Tag Cloud Font + Header & Footer Inject + Custom Login + Post Mails + Disable Feeds + Remove Bloat + TinyMCE + Protect schema.org markup
* Version: 1.6.8
* Author: iMedia One
* Author URI: https://imediaone.com/
**/

require __DIR__ . '/../plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://imediaone.com/library/wp-plugins/imedia-basic.json',
    __FILE__,
    'imedia-basic'
);

defined( 'ABSPATH' ) or die( '' );
function imedia_basic_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=imedia_basic">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'imedia_basic_settings_link' );

add_action('init', 'init_imedia_basic');
function init_imedia_basic() {
	add_action('admin_init', 'imedia_basic_register');
	add_action('admin_menu', 'imedia_basic_register_options_page');
	if( get_option('jquery_cdn_check')  !== 'no') add_action('wp_enqueue_scripts', 'jquery_enqueue', 11);

	add_action('wp_head', 'imedia_basic_head_start_html', 0);
	add_action('wp_footer', 'imedia_basic_footer_start_html', 0);
	add_action('wp_head', 'imedia_basic_head_end_html', 1000);
	add_action('wp_footer', 'imedia_basic_footer_end_html', 1000);


	if( get_option('google_site_verification_id') != '')  add_action('wp_head', 'imedia_basic_google_site_verification', 2);
	if( get_option('bing_site_verification_id') != '')  add_action('wp_head', 'imedia_basic_bing_site_verification', 2);
	if( get_option('microsoft_clarity_verification_id')  != '')  add_action('wp_head', 'imedia_basic_microsoft_clarity_verification', 2);
	if( get_option('webmonitor_content_id') != '')  add_action('wp_head', 'imedia_basic_webmonitor_content', 2);
	if( get_option('google_analytics_id') != '')  add_action('wp_footer', 'imedia_basic_google_analytics', 1100);
	if( get_option('enable_adunblock_check') == 'yes')  add_action('wp_footer', 'imedia_enable_adunblock', 1100);
	if( get_option('disqus_ads_status') == 'no')  add_action('wp_footer', 'imedia_basic_disqus_ads', 1100);

	if( get_option('author_publish_email_check') !== 'no') add_action( 'publish_post', 'author_publish_notice', 10 ,2 );
	if( get_option('admin_publish_email_check') !== 'no') add_action( 'publish_post', 'admin_publish_notice', 10 ,2 );
	if( get_option('author_pending_email_check') !== 'no') add_action( 'pending_post', 'author_pending_notice', 10 ,2 );
	if( get_option('admin_pending_email_check') !== 'no') add_action( 'pending_post', 'admin_pending_notice', 10 ,2 );

	if( get_option('disable_feeds_check') !== 'no') {
		Disable_Feeds::get_instance();;
		remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1);
		remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1);
		remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1);
		remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1);
		remove_action( 'wp_head', 'feed_links_extra', 3 ); // Removes the links to the extra feeds such as category feeds
		remove_action( 'wp_head', 'feed_links', 2 ); // Removes links to the general feeds: Post and Comment Feed
		remove_action( 'wp_head', 'rsd_link'); // Removes the link to the Really Simple Discovery service endpoint, EditURI link
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links');
	}

	if( get_option('remove_head_links_check')  !== 'no') {
		remove_action( 'wp_head', 'index_rel_link'); // Removes the index link
		remove_action( 'wp_head', 'parent_post_rel_link_wp_head', 10, 0); // Removes the prev link
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0); // Removes the relational links for the posts adjacent to the current post.
		remove_action( 'wp_head', 'wp_generator'); // Removes the WordPress version i.e. - WordPress 2.8.4
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 ); // Removes the shortlink link
		remove_action( 'wp_head', 'start_post_rel_link');
		remove_action( 'wp_head', 'wc_generator_tag' );
		remove_action( 'wp_head', 'rest_output_link_wp_head');
		remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
		remove_action( 'wp_head', 'wlwmanifest_link'); // Removes the link to the Windows Live Writer manifest file.
		add_filter('aioseop_prev_link', '__return_empty_string' );
		add_filter('aioseop_next_link', '__return_empty_string' );
		wp_deregister_script('wp-embed');
	}

	if( get_option('remove_head_emoji_check')  !== 'no') {
		remove_action( 'wp_head', 'wp_resource_hints', 2, 99 ); 
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	if( get_option('remove_cf7_scripts_styles_check')  !== 'no') {
		add_action( 'wp_print_scripts', 'deregister_cf7_javascript', 5 );
		add_action( 'wp_print_styles', 'deregister_cf7_styles', 5 );
	}

	if( get_option('remove_cf7_scripts_styles_check')  !== 'no') {
		add_action('wp_enqueue_scripts', 'wpcf7_recaptcha_no_refill', 15, 0);
		function wpcf7_recaptcha_no_refill() {
		  wp_add_inline_script('contact-form-7', 'wpcf7.cached = 0;', 'before' );
		}
	}

	if( get_option('remove_extra_scripts_styles_check')  !== 'no') {
		add_action( 'wp_print_styles', 'deregister_wppagenavi_styles', 17 );
		add_filter( 'w3tc_can_print_comment', '__return_false', 10, 1 );
		add_filter('show_admin_bar', '__return_false');
	}

	if( get_option('disable_heartbeat_check')  !== 'no') add_action( 'init', 'my_deregister_heartbeat', 1 );
	if( get_option('set_image_editor_gd_check')  !== 'no') add_filter( 'wp_image_editors', 'ms_image_editor_default_to_gd' );
	if( get_option('set_text_editor_tinymce_check')  !== 'no') add_filter( 'tiny_mce_before_init', 'tsm_tinymce_init' );
	if( get_option('tag_cloud_count')>0 ) add_filter( 'widget_tag_cloud_args', 'prefix_widget_tag_cloud_args', 10, 1 );
	if( get_option('hide_recaptcha_badge') !== 'no' ) add_action('wp_head', 'hide_recaptcha_badge_head');
	if( get_option('recaptcha_threshold') !== '0.3' ) add_filter( 'wpcf7_recaptcha_threshold', get_option('recaptcha_threshold'),  10, 1 );


}

function prefix_widget_tag_cloud_args( $args ) {
    $args['largest']  = get_option('tag_cloud_largest');
    $args['smallest'] = get_option('tag_cloud_smallest');
    $args['unit']     = 'px';
    $args['number']   = get_option('tag_cloud_count');
    return $args;
}

/** functions for echoing the HTML */
function imedia_basic_register() {
    $sanitize_yes = array(
	            'type' => 'string', 
	            'sanitize_callback' => 'sanitize_text_field',
	            'default' => 'yes',
    );

    $sanitize_no = array(
	            'type' => 'string', 
	            'sanitize_callback' => 'sanitize_text_field',
	            'default' => 'no',
    );

    $sanitize_blank = array(
	            'type' => 'string', 
	            'sanitize_callback' => 'sanitize_text_field',
	            'default' => '',
    );
            
	//register our settings
	register_setting('imedia_basic_group', 'allow_cloudflare_flexible_check', $sanitize_yes );

	register_setting('imedia_basic_group', 'google_site_verification_id', $sanitize_blank );
	register_setting('imedia_basic_group', 'bing_site_verification_id', $sanitize_blank );
	register_setting('imedia_basic_group', 'microsoft_clarity_verification_id', $sanitize_blank );
	register_setting('imedia_basic_group', 'webmonitor_content_id', $sanitize_blank );
	register_setting('imedia_basic_group', 'google_analytics_id', $sanitize_blank );
	register_setting('imedia_basic_group', 'enable_adunblock_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'enable_adunblock_url', $sanitize_blank );
	register_setting('imedia_basic_group', 'enable_adunblock_opacity', array('type' => 'number', 'sanitize_callback' => 'sanitize_text_field', 'default' => '90')  );

	register_setting('imedia_basic_group', 'jquery_cdn_check',  $sanitize_yes );
	register_setting('imedia_basic_group', 'jquery_cdn_version',  array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '1.12.4') );
	register_setting('imedia_basic_group', 'jquery_cdn_location', array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'head') );

	register_setting('imedia_basic_group', 'tag_cloud_count' ,  array('type' => 'number', 'sanitize_callback' => 'sanitize_text_field', 'default' => '25') );
	register_setting('imedia_basic_group', 'tag_cloud_smallest' , array('type' => 'number', 'sanitize_callback' => 'sanitize_text_field', 'default' => '15') );
	register_setting('imedia_basic_group', 'tag_cloud_largest' , array('type' => 'number', 'sanitize_callback' => 'sanitize_text_field', 'default' => '13') );

	register_setting('imedia_basic_group', 'disqus_ads_status',  array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'no') );
	register_setting('imedia_basic_group', 'disqus_attribute_type' ,  array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'title') );
	register_setting('imedia_basic_group', 'disqus_attribute_value' , array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'Disqus') );
	register_setting('imedia_basic_group', 'disqus_frame_numbers' , array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '1,3') );
	register_setting('imedia_basic_group', 'disqus_check_interval' , array('type' => 'number', 'sanitize_callback' => 'sanitize_text_field', 'default' => '5') );

	register_setting('imedia_basic_group', 'imedia_basic_head_start');
	register_setting('imedia_basic_group', 'imedia_basic_head_end');
	register_setting('imedia_basic_group', 'imedia_basic_footer_start');
	register_setting('imedia_basic_group', 'imedia_basic_footer_end');
	register_setting('imedia_basic_group', 'author_publish_email_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'admin_publish_email_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'author_pending_email_check', $sanitize_no );
	register_setting('imedia_basic_group', 'admin_pending_email_check', $sanitize_no );
	register_setting('imedia_basic_group', 'custom_login_page_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'disable_feeds_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'remove_head_links_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'remove_head_emoji_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'disable_heartbeat_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'remove_cf7_scripts_styles_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'remove_cf7_refill_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'remove_extra_scripts_styles_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'set_image_editor_gd_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'set_text_editor_tinymce_check', $sanitize_yes );
	register_setting('imedia_basic_group', 'hide_recaptcha_badge', $sanitize_yes );
	register_setting('imedia_basic_group', 'recaptcha_threshold', array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '0.3') );
}

            
/** Registers the option page */
function imedia_basic_register_options_page() {
	add_options_page('iMedia Basic', 'iMedia Basic', 'administrator', 'imedia_basic', 'imedia_basic_build_settings_page', 1);
}

/** Builds the admin settings page */
function imedia_basic_build_settings_page() {
	?>
	<style>
	.form-table th {padding: 10px 0px 0px 0px;}
	.form-table td{padding: 10px 10px 0px 10px;}
	.form-table textarea {max-width:800px;}
	.form-table textarea {width: 440px;height: 100px;}
	label{ font-style: italic; font-size:0.8em;font-weight:bold;}
	.inline_block{display: inline-block;}
	</style>
	<div class="wrap">
	<h2><?php _e('iMedia Basic', 'imedia_basic'); ?></h2>
	<p><?php _e('Here you can inject HTML directly into various useful spots within your theme. This is useful for custom meta tags in the header or for loading custom scripts into your page.', 'imedia_basic'); ?></p>
	<form method="post" action="options.php">
	    <?php settings_fields( 'imedia_basic_group' ); ?>
	    <?php do_settings_sections( 'imedia_basic_group' ); ?>
	    <table class="form-table" cellpadding="0" cellspacing="0">
	        <tr valign="top">
		        <th scope="row"><?php _e('Cloudflare Flexible', 'imedia_basic'); ?></th>
				<td>
					<label for="allow_cloudflare_flexible_check">Allow</label><br>
					<select name="allow_cloudflare_flexible_check" >
					  <option value="yes" <?php if( get_option('allow_cloudflare_flexible_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('allow_cloudflare_flexible_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Google Webmaster', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="google_site_verification_id">Verification ID Only</label><br>
		        	<input class="small_input" type="text" name="google_site_verification_id" size="70" value="<?php echo get_option('google_site_verification_id'); ?>" placeholder=""></input>
				</td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Bing Webmaster', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="bing_site_verification_id">Verification ID Only</label><br>
		        	<input class="small_input" type="text" name="bing_site_verification_id" size="70" value="<?php echo get_option('bing_site_verification_id'); ?>" placeholder=""></input>
				</td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Microsoft Clarity', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="microsoft_clarity_verification_id">Verification ID Only</label><br>
		        	<input class="small_input" type="text" name="microsoft_clarity_verification_id" size="70" value="<?php echo get_option('microsoft_clarity_verification_id'); ?>" placeholder=""></input>
				</td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Webmonitor', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="webmonitor_content_id">Verification ID Only</label><br>
		        	<input class="small_input" type="text" name="webmonitor_content_id" size="70" value="<?php echo get_option('webmonitor_content_id'); ?>" placeholder=""></input>
				</td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Google Analytics', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="google_analytics_id">Verification ID Only</label><br>
		        	<input class="small_input" type="text" name="google_analytics_id" size="70" value="<?php echo get_option('google_analytics_id'); ?>" placeholder=""></input>
				</td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('AdUnblock', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="enable_adunblock_check">Enable</label><br>
					<select name="enable_adunblock_check" >
					  <option value="yes" <?php if( get_option('enable_adunblock_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('enable_adunblock_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				</td>
				<td class="inline_block">
					<label for="enable_adunblock_opacity">Opacity</label><br>
					<select name="enable_adunblock_opacity" >
					  <option value="100" <?php if( get_option('enable_adunblock_opacity')  == '100') echo 'selected';?> >100</option>
					  <option value="90" <?php if( get_option('enable_adunblock_opacity')  == '90') echo 'selected';?> >90</option>
					  <option value="80" <?php if( get_option('enable_adunblock_opacity')  == '80') echo 'selected';?> >80</option>
					  <option value="70" <?php if( get_option('enable_adunblock_opacity')  == '70') echo 'selected';?> >70</option>
					  <option value="60" <?php if( get_option('enable_adunblock_opacity')  == '60') echo 'selected';?> >60</option>
					  <option value="50" <?php if( get_option('enable_adunblock_opacity')  == '50') echo 'selected';?> >50</option>
					  <option value="40" <?php if( get_option('enable_adunblock_opacity')  == '40') echo 'selected';?> >40</option>
					  <option value="30" <?php if( get_option('enable_adunblock_opacity')  == '30') echo 'selected';?> >30</option>
					  <option value="20" <?php if( get_option('enable_adunblock_opacity')  == '20') echo 'selected';?> >20</option>
					  <option value="10" <?php if( get_option('enable_adunblock_opacity')  == '10') echo 'selected';?> >10</option>
					  <option value="0" <?php if( get_option('enable_adunblock_opacity')  == '0') echo 'selected';?> >0</option>
					</select> 
				</td>

				<td class="inline_block">
					<label for="enable_adunblock_url">Image Url (if any)</label><br>
		        	<input class="small_input" type="text" name="enable_adunblock_url" size="70" value="<?php echo get_option('enable_adunblock_url'); ?>" placeholder=""></input>
				</td>
	        </tr>

	        <tr valign="top">
		        <th scope="row"><?php _e('JQuery', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="jquery_cdn_check">Cloudflare CDN</label><br>
					<select name="jquery_cdn_check" >
					  <option value="yes" <?php if( get_option('jquery_cdn_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('jquery_cdn_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				</td>
				<td class="inline_block" >
					<label for="jquery_cdn_version">Version</label><br>
					<select name="jquery_cdn_version" >
					  <option value="1.12.4" <?php if( get_option('jquery_cdn_version')  == '1.12.4') echo 'selected';?> >1.12.4</option>
					  <option value="1.12.3" <?php if( get_option('jquery_cdn_version')  == '1.12.3') echo 'selected';?> >1.12.3</option>
					  <option value="1.12.2" <?php if( get_option('jquery_cdn_version')  == '1.12.2') echo 'selected';?> >1.12.2</option>
					  <option value="1.12.1" <?php if( get_option('jquery_cdn_version')  == '1.12.1') echo 'selected';?> >1.12.1</option>
					  <option value="1.12.0" <?php if( get_option('jquery_cdn_version')  == '1.12.0') echo 'selected';?> >1.12.0</option>
					  <option value="1.11.1" <?php if( get_option('jquery_cdn_version')  == '1.11.1') echo 'selected';?> >1.11.1</option>
					  <option value="1.11.0" <?php if( get_option('jquery_cdn_version')  == '1.11.0') echo 'selected';?> >1.11.0</option>
					</select> 
				</td>
				<td class="inline_block">
					<label for="jquery_cdn_location">Location</label><br>
					<select name="jquery_cdn_location" >
					  <option value="head" <?php if( get_option('jquery_cdn_location')  == 'head') echo 'selected';?> >Head</option>
					  <option value="foot" <?php if( get_option('jquery_cdn_location')  == 'foot') echo 'selected';?> >Foot</option>
					</select> 
				</td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Tag Cloud', 'imedia_basic'); ?></th>
				<td class="inline_block" >
					<label for="tag_cloud_count">Tags Count</label><br>
		        	<input class="small_input" type="text" name="tag_cloud_count" size="5" value="<?php echo get_option('tag_cloud_count'); ?>" placeholder="25"></input>
				</td>
				<td class="inline_block" >
					<label for="tag_cloud_largest">Largest Font</label><br>
		        	<input class="small_input" type="text" name="tag_cloud_largest"  size="5" value="<?php echo get_option('tag_cloud_largest'); ?>" placeholder="15"></input>
				</td>
				<td class="inline_block" >
					<label for="tag_cloud_smallest">Smallest Font</label><br>
		        	<input class="small_input" type="text" name="tag_cloud_smallest"  size="5" value="<?php echo get_option('tag_cloud_smallest'); ?>" placeholder="13"></input>
				</td>
	        </tr>

	        <tr valign="top">
		        <th scope="row"><?php _e('Disqus Ads', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="disqus_ads_status">Show</label><br>
					<select name="disqus_ads_status" >
					  <option value="no" <?php if( get_option('disqus_ads_show')  == 'enabled') echo 'selected';?> >No</option>
					  <option value="yes" <?php if( get_option('disqus_ads_show')  == 'disabled') echo 'selected';?> >Yes</option>
					</select> 
				</td>
				<td class="inline_block" >
					<label for="disqus_attribute_type">Attribute Type</label><br>
		        	<input class="small_input" type="text" name="disqus_attribute_type" size="5" value="<?php echo get_option('disqus_attribute_type'); ?>" placeholder="title"></input>
				</td>
				<td class="inline_block" >
					<label for="disqus_attribute_value">Attribute Value</label><br>
		        	<input class="input" type="text" name="disqus_attribute_value" size="30" value="<?php echo get_option('disqus_attribute_value'); ?>" placeholder="Disqus,Disqusads"></input>
				</td>
				<td class="inline_block" >
					<label for="disqus_frame_numbers">Frame Numbers</label><br>
		        	<input class="small_input" type="text" name="disqus_frame_numbers" size="5" value="<?php echo get_option('disqus_frame_numbers'); ?>" placeholder="1,3"></input>
				</td>
				<td class="inline_block" >
					<label for="disqus_check_interval">Check Interval (Sec)</label><br>
		        	<input class="small_input" type="text" name="disqus_check_interval"  size="5" value="<?php echo get_option('disqus_check_interval'); ?>" placeholder="15"></input>
				</td>
	        </tr>

	        <tr valign="top">
		        <th scope="row"><?php _e('Start of &lt;head&gt; tag', 'imedia_basic'); ?></th>
		        <td><textarea name="imedia_basic_head_start" placeholder="<!-- start of <head> tag -->"><?php echo get_option('imedia_basic_head_start'); ?></textarea></td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Bottom of &lt;head&gt; tag', 'imedia_basic'); ?></th>
		        <td><textarea name="imedia_basic_head_end" placeholder="<!-- bottom of <head> tag -->"><?php echo get_option('imedia_basic_head_end'); ?></textarea></td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('End of page (before footer scripts)', 'imedia_basic'); ?></th>
		        <td><textarea name="imedia_basic_footer_start" placeholder="<!-- before footer scripts -->"><?php echo get_option('imedia_basic_footer_start'); ?></textarea></td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('After Footer Scripts', 'imedia_basic'); ?></th>
		        <td><textarea name="imedia_basic_footer_end" placeholder="<!-- after footer scripts -->"><?php echo get_option('imedia_basic_footer_end'); ?></textarea></td>
	        </tr>

	        <tr valign="top">
		        <th scope="row"><?php _e('Post Emails', 'imedia_basic'); ?></th>
				<td class="inline_block">
					<label for="author_publish_email_check">Author Publish</label><br>
					<select name="author_publish_email_check" >
					  <option value="yes" <?php if( get_option('author_publish_email_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('author_publish_email_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				</td>
				<td class="inline_block">
					<label for="author_pending_email_check">Author Pending</label><br>
					<select name="author_pending_email_check" >
					  <option value="yes" <?php if( get_option('author_pending_email_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('author_pending_email_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				</td>
				<td class="inline_block">
					<label for="admin_publish_email_check">Admin Publish</label><br>
					<select name="admin_publish_email_check" >
					  <option value="yes" <?php if( get_option('admin_publish_email_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('admin_publish_email_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				</td>
				<td class="inline_block">
					<label for="admin_pending_email_check">Admin Pending</label><br>
					<select name="admin_pending_email_check" >
					  <option value="yes" <?php if( get_option('admin_pending_email_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('admin_pending_email_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Custom Login Page', 'imedia_basic'); ?></th>
				<td>
					<select name="custom_login_page_check" >
					  <option value="yes" <?php if( get_option('custom_login_page_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('custom_login_page_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Disable Feeds', 'imedia_basic'); ?></th>
				<td>
					<select name="disable_feeds_check" >
					  <option value="yes" <?php if( get_option('disable_feeds_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('disable_feeds_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Remove Head Links', 'imedia_basic'); ?></th>
				<td>
					<select name="remove_head_links_check" >
					  <option value="yes" <?php if( get_option('remove_head_links_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('remove_head_links_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Remove Head Emoji', 'imedia_basic'); ?></th>
				<td>
					<select name="remove_head_emoji_check" >
					  <option value="yes" <?php if( get_option('remove_head_emoji_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('remove_head_emoji_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Disable Heartbeat', 'imedia_basic'); ?></th>
				<td>
					<select name="disable_heartbeat_check" >
					  <option value="yes" <?php if( get_option('disable_heartbeat_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('disable_heartbeat_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Remove CF7 Scripts Styles', 'imedia_basic'); ?></th>
				<td>
					<select name="remove_cf7_scripts_styles_check" >
					  <option value="yes" <?php if( get_option('remove_cf7_scripts_styles_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('remove_cf7_scripts_styles_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Remove CF7 Refill', 'imedia_basic'); ?></th>
				<td>
					<select name="remove_cf7_refill_check" >
					  <option value="yes" <?php if( get_option('remove_cf7_refill_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('remove_cf7_refill_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Remove Extra Scripts Styles', 'imedia_basic'); ?></th>
				<td>
					<select name="remove_extra_scripts_styles_check" >
					  <option value="yes" <?php if( get_option('remove_extra_scripts_styles_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('remove_extra_scripts_styles_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Set Image Editor to GD', 'imedia_basic'); ?></th>
				<td>
					<select name="set_image_editor_gd_check" >
					  <option value="yes" <?php if( get_option('set_image_editor_gd_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('set_image_editor_gd_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Set Text Editor to Tinymce', 'imedia_basic'); ?></th>
				<td>
					<select name="set_text_editor_tinymce_check" >
					  <option value="yes" <?php if( get_option('set_text_editor_tinymce_check')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('set_text_editor_tinymce_check')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Hide reCAPTCHA v3 Badge', 'imedia_basic'); ?></th>
				<td>
					<select name="hide_recaptcha_badge" >
					  <option value="yes" <?php if( get_option('hide_recaptcha_badge')  == 'yes') echo 'selected';?> >Yes</option>
					  <option value="no" <?php if( get_option('hide_recaptcha_badge')  == 'no') echo 'selected';?> >No</option>
					</select> 
				<td>
	        </tr>
	        <tr valign="top">
		        <th scope="row"><?php _e('Recaptcha Threshold', 'imedia_basic'); ?></th>
				<td>
					<select name="recaptcha_threshold" >
					  <option value="0.0" <?php if( get_option('recaptcha_threshold')  == '0.0') echo 'selected';?> >0.0</option>
					  <option value="0.1" <?php if( get_option('recaptcha_threshold')  == '0.1') echo 'selected';?> >0.1</option>
					  <option value="0.2" <?php if( get_option('recaptcha_threshold')  == '0.2') echo 'selected';?> >0.2</option>
					  <option value="0.3" <?php if( get_option('recaptcha_threshold')  == 0.3 ) echo 'selected';?> >0.3</option>
					  <option value="0.4" <?php if( get_option('recaptcha_threshold')  == '0.4') echo 'selected';?> >0.4</option>
					  <option value="0.5" <?php if( get_option('recaptcha_threshold')  == '0.5') echo 'selected';?> >0.5</option>
					  <option value="0.6" <?php if( get_option('recaptcha_threshold')  == '0.6') echo 'selected';?> >0.6</option>
					  <option value="0.7" <?php if( get_option('recaptcha_threshold')  == '0.7') echo 'selected';?> >0.7</option>
					  <option value="0.8" <?php if( get_option('recaptcha_threshold')  == '0.8') echo 'selected';?> >0.8</option>
					  <option value="0.9" <?php if( get_option('recaptcha_threshold')  == '0.9') echo 'selected';?> >0.9</option>
					  <option value="1.0" <?php if( get_option('recaptcha_threshold')  == '1.0') echo 'selected';?> >1.0</option>
					</select> 
				<td>
	        </tr>

	    </table>
	    <?php submit_button(); ?>
	</form>
	<?php
}


//******************* Header & Footer Inject *******************//

function imedia_enable_adunblock() { 
	$aub_banner = plugin_dir_url( __FILE__ ).'aub_banner_1.png';
	if (get_option('enable_adunblock_url')) $aub_banner = get_option('enable_adunblock_url');

	$opacity_alpha = get_option('enable_adunblock_opacity');
	$opacity = $opacity_alpha / 100;
	echo '
	<style>
	#aub_cover {background-color: rgb(0, 0, 0); opacity: 0.9; z-index: 999999998; background: #a6a6a6 url("'.plugin_dir_url( __FILE__ ).'aub_bg_2x2.png") 50% 50% repeat;background-color: rgb(166, 166, 166);opacity: '.$opacity.';filter: Alpha(Opacity='.$opacity_alpha.'); position: fixed; top: 0; left: 0; width: 100%; height: 100%;}
	#aub {z-index: 999999999; position: fixed; top: 10%; left: 10%; width: 80%; height: 80%;max-width: 80%; max-height: 80%;}
	#aub #aub_box {max-width: 450px;  margin: 0 auto;display: block;z-index: 999999;border-radius:15px;border: 3px solid #ccc;padding:15px;background: #fff}
	#aub #aub_image {padding-top: 93.333%;background-image: url("'.$aub_banner.'");background-size: cover; background-position: center;}
	#aub #aub_button {margin: 0 auto;  padding-top: 10px; text-align: center;}
	#aub #aub_refresh {padding: 10px 45px;text-align: center;text-transform: uppercase;font-size:20px;font-weight: 700;dtransition: 0.5s;background-size: 200% auto;color: white;box-shadow: 0 0 20px #eee;border-radius:6px;display: inline-block;background-image: linear-gradient(to right, #e52d27 0%, #b31217  51%, #e52d27  100%);margin: 10px;}
	#aub #aub_refresh:hover {background-position: right center; color: #fff;text-decoration: none;}
	</style>    
	<script src="'.plugin_dir_url( __FILE__ ).'doubleserve.js" type="text/javascript"></script>
	<script type="text/javascript">
		(function($) {
			var aub = "No";
			if( $("#bFVMAHszuOSR").length == 0){
				var aub = "Yes";
				var aub_inner = \'<div id="aub_cover"></div><div id="aub"><div id="aub_box" style="display:block" id="adblockEnabled"><div id="aub_image"></div><div id="aub_button"><input type="button" id="aub_refresh" value="Refresh" onClick="window.location.reload()"></div></div></div>\';
				$("body").append(aub_inner);
			}
			
			if(typeof ga !=="undefined"){
			  ga("send","event","Blocking AUB",aub,{"nonInteraction":1});
			} else if(typeof _gaq !=="undefined"){
			  _gaq.push(["_trackEvent","Blocking AUB",aub,undefined,undefined,true]);
			}
			
		})(jQuery);
	</script>
	';
}

function imedia_basic_google_analytics() { 
	echo '<script async src="https://www.googletagmanager.com/gtag/js?id='.get_option('google_analytics_id').'"></script><script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag("js", new Date()); gtag("config", "'.get_option('google_analytics_id').'");</script>'."\n";
}

function imedia_basic_google_site_verification() { 
	echo '<meta name="google-site-verification" content="'.get_option('google_site_verification_id').'" />'."\n";
}

function imedia_basic_bing_site_verification() { 
	echo '<meta name="msvalidate.01" content="'.get_option('bing_site_verification_id').'" />'."\n";
}

function imedia_basic_microsoft_clarity_verification() { 
	echo '<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i+"?ref=bwt";y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window, document, "clarity", "script", "'.get_option('microsoft_clarity_verification_id').'");</script>'."\n";
}

function imedia_basic_webmonitor_content() { 
	echo '<meta name="webmonitor" content="'.get_option('webmonitor_content_id').'" />'."\n";
}

function imedia_basic_disqus_ads() { 
	$check_interval = get_option('disqus_check_interval')*1000;
	$attribute_type = get_option('disqus_attribute_type');

	$avs = explode(',', get_option('disqus_attribute_value'));
	foreach ($avs as $av) $attribute_value .= "(".$av.")|";
	$attribute_values = trim(rtrim(trim($attribute_value), '|'));

	$fns = explode(',', get_option('disqus_frame_numbers'));
	foreach ($fns as $fn) $frame_numbers .= "r == ".$fn." || ";
	$frame_numbers = trim(rtrim(trim($frame_numbers), '||'));

	$display = '
		<script type="text/javascript" async="async">
			$(document).ready(function() {
				(function($){
				    setInterval(() => {
				    	let c = 1; 
				        $.each($("iframe"), (arr,x) => {
				            let attribute = $(x).attr("'.$attribute_type.'");
				            if ( attribute && attribute.match(/'.$attribute_values.'/gi)) c = c + 1;
				        });
						if (c == 4){
					    	let r = 1; 
					        $.each($("iframe"), (arr,x) => {
					            let attribute = $(x).attr("'.$attribute_type.'");
					            if ( attribute && attribute.match(/'.$attribute_values.'/gi)) {
					                if( '.$frame_numbers.' ) $(x).remove();
					            	r = r + 1;
					            }
					        });
						}
				    }, '.$check_interval.');
				})(jQuery);
			});
		</script>
	'."\n";
	echo $display;
}


function imedia_basic_head_start_html() { echo get_option('imedia_basic_head_start'); }
function imedia_basic_head_end_html() { echo get_option('imedia_basic_head_end'); }
function imedia_basic_footer_start_html() { echo get_option('imedia_basic_footer_start'); }
function imedia_basic_footer_end_html() { echo get_option('imedia_basic_footer_end'); }

//******************* Custom Login *******************//
if( get_option('custom_login_page_check')  !== 'no'){
	if( !isset($_REQUEST['interim-login']) ){
		add_action( 'login_head', 'custom_login_header' );
		function custom_login_header() {
			?>
			<script type="text/javascript">
				wp_custom_login_remove_element('wp-admin-css');
				wp_custom_login_remove_element('colors-fresh-css');
				function wp_custom_login_remove_element(id) {
					var element = document.getElementById(id);
					if( typeof element !== 'undefined' && element != null && element.value == '' ) {
						element.parentNode.removeChild(element);
					}
				}
			</script>
			<style>
			body #login, #login{font-family:sans-serif;background:rgba(0,0,0,0.8) !important;color:#fff !important;font-size: 0.8em;font-weight:normal;line-height: 1.4em;padding:10px !important;border:1px solid #D3D3D3;border-radius:5px;width:326px !important;;max-width:326px !important;margin:0px auto;margin-top:30px;margin-bottom:30px;}
			#login a{color:#333;text-decoration: none;}
			#login h1,#login h1 a{font-size:1.2em;padding:0;margin:0;color:#fff;font-weight:normal;margin-bottom: 15px;text-align: center;}
			#login p{padding:0;margin:0;}
			#login label, #login .admin-email__details {font-size:0.9em;color:#fff;font-weight:normal;line-height: 2.5em;}
			#login a.button-large, #login input[type="text"]#user_login, #login input[type="password"]#user_pass, #login #login_error, #login .g-recaptcha{background-color:#F9F9F9 !important;color:#333 !important;border:1px solid #D3D3D3;border-radius:3px;width:100%;min-width:100%;max-width:100%;margin:0;}
			#login input[type="text"]#user_login, #login input[type="password"]#user_pass{font-weight:bold;padding:3px 8px;line-height: 1.4em;margin-bottom: 10px;font-size: 1.4em;}
			#login #login_error a, #login .admin-email__details a, #login p.message{font-size:1em;color:#FF3232 !important;font-weight:bold;text-align:center}
			#login input[type="submit"], #login input.button, #login .button-large, #login .admin-email__actions-primary a.button{background:#FF3232 !important;color:#fff !important;cursor:pointer;font-size:1.4em !important;font-weight:bold;width:100%;min-width:100%;max-width:100%;padding:8px;margin:10px 0 ;border:0;border-radius:3px;}
			#login input[type="submit"]:hover, #login input.button:hover, #login .button-large:hover, #login .admin-email__actions-primary a.button:hover {background:#CC2828 !important; color:#fff !important;}
			#login .wp-hide-pw{background:transparent;color:#FFF;padding:0;border:0;float: right;}
			#login .g-recaptcha iframe{margin:0;padding:0;border:0;}
			#login input#rememberme{clear:both;display:inline-block}
			#login .forgetmenot label,#login .admin-email__details a{display:inline-block}
			#login .admin-email__details strong{color:#1589C4;}
			#login p#nav a::before {content: "\2190\00A0";}
			#login p#nav a,#login p#nav a:hover,#login p#backtoblog a,#login p#backtoblog a:hover,.captcha-title,.captcha-title:hover, #login .admin-email__actions-secondary a {background-color:transparent!important;color:#fff !important;text-align:left;line-height:2em;font-size:1em;font-weight:bold;text-transform: none;}
			#login h1 a:hover,#login p#nav a:hover, #login p#backtoblog a:hover,#login .wp-hide-pw:hover,#login .admin-email__actions-secondary a:hover{color:#CC2828 !important;transition:.5s;}
			#login input#user_login:hover, #login input#user_pass:hover,#login input.button:hover{background:#F9F9F9}
			#login img.captcha_code_img,#login input#ux_txt_captcha_challenge_field{width:100%;border-radius:3px;}
			#login #login_error{padding:5px;line-height:1.4em;margin-bottom: 10px;}
			.g-recaptcha-outer{border: 0;}
			</style>

			<?php
			get_header();
		}
	
		add_action( 'login_footer', 'custom_login_footer' );
		function custom_login_footer() {
			get_footer();
		}
	
		function custom_is_login_page() {
			return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
		}
	
		// changing the logo link from wordpress.org to your site
		add_filter( 'login_headerurl', 'mb_login_url' );
		function mb_login_url() {
			return home_url();
		}
		
		// changing the alt text on the logo to show your site name
		add_filter( 'login_headertitle', 'mb_login_title' );
		function mb_login_title() { 
			return get_option('blogname'); 
		}
	}
}

//******************* Post Mails *******************//
function set_mail_html_content_type() {
	return 'text/html';
}

function basic_content_type() {
	return 'text/html';
}
function basic_sender_email( $original_email_address ) {
    return get_option('admin_email');
}
function basic_sender_name( $original_email_from ) {
    return get_bloginfo().' Admin';
}

function author_publish_notice( $ID, $post ) {
	if( $post->post_type != 'post' ) return;
	if( $_POST['original_post_status'] == 'publish' ) return;
	$to = get_the_author_meta( 'user_email', $post->post_author );
	$author_name = get_the_author_meta( 'display_name', $post->post_author );
	if ( empty( $author_name ) ) $author_name = get_the_author_meta( 'nickname', $post->post_author );
	$subject = 'Your Article is Online';
	$message = '
		<h2>Hi '.$author_name.',</h2> 
		<h3>Congratulations!</h3> 
		<h1>Article Published</h1> 
		<h3>Your article is now approved and published on <a href="'.get_site_url().'">'.get_bloginfo().'</a></h3> 
		<h3>Article : <a href="'.get_permalink( $ID ).'">'.$post->post_title .'</a></h3>
		<p>Please do hang around and answer any questions viewers may have !</p> 
		<p>Thanks</p>
		<h2>'.get_bloginfo().' Admin</h2>
		';
	add_filter( 'wp_mail_content_type', 'basic_content_type' );
	add_filter( 'wp_mail_from', 'basic_sender_email' );
	add_filter( 'wp_mail_from_name', 'basic_sender_name' );
	wp_mail( $to, $subject, $message ); 
	remove_filter( 'wp_mail_content_type', 'basic_content_type' );
	remove_filter( 'wp_mail_from', 'basic_sender_email' );
	remove_filter( 'wp_mail_from_name', 'basic_sender_name' );
}

function admin_publish_notice( $ID, $post ) {
	if( $post->post_type != 'post' ) return;
	if( $_POST['original_post_status'] == 'publish' ) return;
	$to = get_option('admin_email');
	$author_name = get_the_author_meta( 'display_name', $post->post_author );
	if ( empty( $author_name ) ) $author_name = get_the_author_meta( 'nickname', $post->post_author );
	$subject = 'Article is Published by '.$author_name;
	$message = '
		<h1>New Article Published</h1> 
		<h3>An article is published on <a href="'.get_site_url().'">'.get_bloginfo().'</a></h3> 
		<h3>Article : <a href="'.get_permalink( $ID ).'">'.$post->post_title.'</a></h3>
		<h3>Author : '.$author_name.'</h3></p>
		<p>Please check if any modifications are needed !</p> 
		<p>Thanks</p>
		<h2>'.get_bloginfo().' Admin</h2>
		';
	add_filter( 'wp_mail_content_type', 'basic_content_type' );
	add_filter( 'wp_mail_from', 'basic_sender_email' );
	add_filter( 'wp_mail_from_name', 'basic_sender_name' );
	wp_mail( $to, $subject, $message ); 
	remove_filter( 'wp_mail_content_type', 'basic_content_type' );
	remove_filter( 'wp_mail_from', 'basic_sender_email' );
	remove_filter( 'wp_mail_from_name', 'basic_sender_name' );
}

function author_pending_notice( $ID, $post ) {
	if( $post->post_type != 'post' ) return;
	$to = get_the_author_meta( 'user_email', $post->post_author );
	$author_name = get_the_author_meta( 'display_name', $post->post_author );
	if ( empty( $author_name ) ) $author_name = get_the_author_meta( 'nickname', $post->post_author );
	$subject = 'Your Article is Pending Review';
	$message = '
		<h2>Hi '.$author_name.',</h2>
		<h3>Great !</h3> 
		<h1>Article Submitted.</h1>
		<h3>Your article is now submitted and pending review on <a href="'.get_site_url().'">'.get_bloginfo().'</a></h3> 
		<h3>Article : <a href="'.get_permalink( $ID ).'">'.$post->post_title .'</a></h3>
		<p>An editor shall review and if approved it shall be publish .</p> 
		<p>Thanks</p>
		<h2>'.get_bloginfo().' Admin</h2>
		';
	add_filter( 'wp_mail_content_type', 'basic_content_type' );
	add_filter( 'wp_mail_from', 'basic_sender_email' );
	add_filter( 'wp_mail_from_name', 'basic_sender_name' );
	wp_mail( $to, $subject, $message ); 
	remove_filter( 'wp_mail_content_type', 'basic_content_type' );
	remove_filter( 'wp_mail_from', 'basic_sender_email' );
	remove_filter( 'wp_mail_from_name', 'basic_sender_name' );
}

function admin_pending_notice( $ID, $post ) {
	if( $post->post_type != 'post' ) return;
	$to = get_option('admin_email');
	$author_name = get_the_author_meta( 'display_name', $post->post_author );
	if ( empty( $author_name ) ) $author_name = get_the_author_meta( 'nickname', $post->post_author );
	$subject = 'Article is Submitted by '.$author_name;
	$message = '
		<h1>New Article Submitted.</h1>
		<h3>An article is Submitted on <a href="'.get_site_url().'">'.get_bloginfo().'</a></h3> 
		<h3>Article : <a href="'.get_permalink( $ID ).'">'.$post->post_title.'</a></h3>
		<h3>Author : '.$author_name.'</h3></p>
		<p>Please review and publish it if approved !</p> 
		<p>Thanks</p>
		<h2>'.get_bloginfo().' Admin</h2>
		';
	add_filter( 'wp_mail_content_type', 'basic_content_type' );
	add_filter( 'wp_mail_from', 'basic_sender_email' );
	add_filter( 'wp_mail_from_name', 'basic_sender_name' );
	wp_mail( $to, $subject, $message ); 
	remove_filter( 'wp_mail_content_type', 'basic_content_type' );
	remove_filter( 'wp_mail_from', 'basic_sender_email' );
	remove_filter( 'wp_mail_from_name', 'basic_sender_name' );
}

//******************* Disable Feeds *******************//
class Disable_Feeds {
	private static $instance = null;
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
		if( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_setup' ) );
		} else {
			add_action( 'wp_loaded', array( $this, 'remove_links' ) );
			add_action( 'template_redirect', array( $this, 'filter_feeds' ), 1 );
			add_filter( 'bbp_request', array( $this, 'filter_bbp_feeds' ), 9 );
		}		add_action( 'plugins_loaded', array( $this, 'register_text_domain' ) );
	}

	public function register_text_domain() {
		load_plugin_textdomain( 'disable-feeds', false, dirname( plugin_basename( __FILE__ ) ) .  '/languages' );
	}

	function admin_setup() {
		add_settings_field( 'disable_feeds_redirect', 'Disable Feeds Plugin', array( $this, 'settings_field' ), 'reading' );
		register_setting( 'reading', 'disable_feeds_redirect' );
		register_setting( 'reading', 'disable_feeds_allow_main' );
	}

	function settings_field() {
		$redirect = $this->redirect_status();
		echo '<p>' . __('The <em>Disable Feeds</em> plugin is active, By default, all feeds are disabled, and all requests for feeds are redirected to the corresponding HTML content. You can tweak this behaviour below.', 'disable-feeds') . '</p>';
		echo '<p><input type="radio" name="disable_feeds_redirect" value="on" id="disable_feeds_redirect_yes" class="radio" ' . checked( $redirect, 'on', false ) . '/><label for="disable_feeds_redirect_yes"> ' . __('Redirect feed requests to corresponding HTML content', 'disable-feeds') . '</label>';
		echo '<br /><input type="radio" name="disable_feeds_redirect" value="off" id="disable_feeds_redirect_no" class="radio" ' . checked( $redirect, 'off', false ) . '/><label for="disable_feeds_redirect_no"> ' . __('Issue a Page Not Found (404) error for feed requests', 'disable-feeds') . '</label></p>';
		echo '<p><input type="checkbox" name="disable_feeds_allow_main" value="on" id="disable_feeds_allow_main" ' . checked( $this->allow_main(), true, false ) . '/><label for="disable_feeds_allow_main"> ' . __('Do not disable the <strong>global post feed</strong> and <strong>global comment feed</strong>', 'disable-feeds') . '</label></p>';
	}

	function remove_links() {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

	function filter_feeds() {
		if( !is_feed() || is_404() ) return;
		if( $this->allow_main() && ! ( is_singular() || is_archive() || is_date() || is_author() || is_category() || is_tag() || is_tax() || is_search() ))return;
		$this->redirect_feed();
	}

	//BBPress feed detection sourced from bbp_request_feed_trap() in BBPress Core.
	function filter_bbp_feeds( $query_vars ) {
		// Looking at a feed
		if ( isset( $query_vars['feed'] ) ) {
			// Forum/Topic/Reply Feed
			if ( isset( $query_vars['post_type'] ) ) {
				// Matched post type
				$post_type = false;
				// Post types to check
				$post_types = array(
					bbp_get_forum_post_type(),
					bbp_get_topic_post_type(),
					bbp_get_reply_post_type()
				);
				// Cast query vars as array outside of foreach loop
				$qv_array = (array) $query_vars['post_type'];
				// Check if this query is for a bbPress post type
				foreach ( $post_types as $bbp_pt ) {
				    if ( in_array( $bbp_pt, $qv_array, true ) ) {
					    $post_type = $bbp_pt;
					    break;
				    }
				}
				// Looking at a bbPress post type
				if ( ! empty( $post_type ) ) {
					$this->redirect_feed();
				}
			}
		}
		// No feed so continue on
		return $query_vars;
	}

	private function redirect_feed() {
		global $wp_rewrite, $wp_query;
		if( $this->redirect_status() == 'on' ) {
			if( isset( $_GET['feed'] ) ) {
				wp_redirect( esc_url_raw( remove_query_arg( 'feed' ) ), 301 );
				exit;
			}
			if( get_query_var( 'feed' ) !== 'old' )	// WP redirects these anyway, and removing the query var will confuse it thoroughly
				set_query_var( 'feed', '' );

			redirect_canonical();	// Let WP figure out the appropriate redirect URL.
			// Still here? redirect_canonical failed to redirect, probably because of a filter. Try the hard way.
			$struct = ( !is_singular() && is_comment_feed() ) ? $wp_rewrite->get_comment_feed_permastruct() : $wp_rewrite->get_feed_permastruct();
			$struct = preg_quote( $struct, '#' );
			$struct = str_replace( '%feed%', '(\w+)?', $struct );
			$struct = preg_replace( '#/+#', '/', $struct );
			$requested_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$new_url = preg_replace( '#' . $struct . '/?$#', '', $requested_url );

			if( $new_url != $requested_url ) {
				wp_redirect( $new_url, 301 );
				exit;
			}
		} else {
			$wp_query->is_feed = false;
			$wp_query->set_404();
			status_header( 404 );
			// Override the xml+rss header set by WP in send_headers
			header( 'Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset') );
		}
	}

	private function redirect_status() {
		$r = get_option( 'disable_feeds_redirect', 'on' );
		// back compat
		if( is_bool( $r ) ) {
			$r = $r ? 'on' : 'off';
			update_option( 'disable_feeds_redirect', $r );
		}
		return $r;
	}

	private function allow_main() {
		return ( get_option( 'disable_feeds_allow_main', 'off' ) == 'on' );
	}
}

//******************* Remove Bloat *******************//
/* Deregister Hearbeat */
function my_deregister_heartbeat() {
	global $pagenow;
	if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow && 'site-health.php' != $pagenow )
		wp_deregister_script('heartbeat');
}

/* Disable Contact Form Script & Styles on other pages */
function deregister_cf7_javascript() {
    if ( !is_page( array('contact', 'contact-us', 'advertise', 'lead-form') )  && !is_page_template('contact.php') ) {
        wp_deregister_script( 'contact-form-7' );
    }
}

function deregister_cf7_styles() {
    if ( !is_page( array('contact', 'contact-us', 'advertise', 'lead-form') )  && !is_page_template('contact.php') ) {
        wp_deregister_style( 'contact-form-7' );
    }
}

/* Disable WP Page Navi Styles as already included */
function deregister_wppagenavi_styles() {
	wp_deregister_style( 'wp-pagenavi' );
}

/* Enable Image Editor to GD */
function ms_image_editor_default_to_gd( $editors ) {
	$gd_editor = 'WP_Image_Editor_GD';
	$editors = array_diff( $editors, array( $gd_editor ) );
	array_unshift( $editors, $gd_editor );
	return $editors;
}

/* Enques jQuery from CDN.  */
$jqv = get_option('jquery_cdn_version');
$jql = get_option('jquery_cdn_location');
function jquery_enqueue() { 
   if (is_admin()) return; 
   global $wp_scripts; 
   global $jqv; 
   global $jql; 
   if ($jql == 'foot') $jql = true;
   		else $jql = false;
   if (is_a($wp_scripts, 'WP_Scripts') && isset($wp_scripts->registered['jquery'])) {
		$cdnjquery = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/'.$jqv.'/jquery.min.js';
         if(200 === wp_remote_retrieve_response_code(wp_remote_head($cdnjquery))) {
	         wp_deregister_script('jquery');
	         wp_register_script('jquery', $cdnjquery , false , $jqv, $jql);
         }
   }
   wp_enqueue_script('jquery'); 
}


//******************* Cloudflare Flexible SSL *******************//
class iMedia_Cloudflare_Flexible_SSL {
	public function __construct() {}
	public function run() {
		if ( !$this->isSsl() && $this->isSslToNonSslProxy() ) {
			$_SERVER[ 'HTTPS' ] = 'on';
			add_action( 'shutdown', array( $this, 'maintainPluginLoadPosition' ) );
		}
	}

	private function isSsl() {
		return function_exists( 'is_ssl' ) && is_ssl();
	}

	private function isSslToNonSslProxy() {
		$bIsProxy = false;
		$aServerKeys = array( 'HTTP_CF_VISITOR', 'HTTP_X_FORWARDED_PROTO' );
		foreach ( $aServerKeys as $sKey ) {
			if ( isset( $_SERVER[ $sKey ] ) && ( strpos( $_SERVER[ $sKey ], 'https' ) !== false ) ) {
				$bIsProxy = true;
				break;
			}
		}

		return $bIsProxy;
	}

	public function maintainPluginLoadPosition() {
		$sBaseFile = plugin_basename( __FILE__ );
		$nLoadPosition = $this->getActivePluginLoadPosition( $sBaseFile );
		if ( $nLoadPosition > 1 ) {
			$this->setActivePluginLoadPosition( $sBaseFile, 0 );
		}
	}

	private function getActivePluginLoadPosition( $sPluginFile ) {
		$sOptionKey = is_multisite() ? 'active_sitewide_plugins' : 'active_plugins';
		$aActive = get_option( $sOptionKey );
		$nPosition = -1;
		if ( is_array( $aActive ) ) {
			$nPosition = array_search( $sPluginFile, $aActive );
			if ( $nPosition === false ) {
				$nPosition = -1;
			}
		}
		return $nPosition;
	}

	private function setActivePluginLoadPosition( $sPluginFile, $nDesiredPosition = 0 ) {
		$aActive = $this->setArrayValueToPosition( get_option( 'active_plugins' ), $sPluginFile, $nDesiredPosition );
		update_option( 'active_plugins', $aActive );
		if ( is_multisite() ) {
			$aActive = $this->setArrayValueToPosition( get_option( 'active_sitewide_plugins' ), $sPluginFile, $nDesiredPosition );
			update_option( 'active_sitewide_plugins', $aActive );
		}
	}

	private function setArrayValueToPosition( $aSubjectArray, $mValue, $nDesiredPosition ) {
		if ( $nDesiredPosition < 0 || !is_array( $aSubjectArray ) ) {
			return $aSubjectArray;
		}
		$nMaxPossiblePosition = count( $aSubjectArray ) - 1;
		if ( $nDesiredPosition > $nMaxPossiblePosition ) {
			$nDesiredPosition = $nMaxPossiblePosition;
		}
		$nPosition = array_search( $mValue, $aSubjectArray );
		if ( $nPosition !== false && $nPosition != $nDesiredPosition ) {
			// remove existing and reset index
			unset( $aSubjectArray[ $nPosition ] );
			$aSubjectArray = array_values( $aSubjectArray );
			// insert and update
			// http://stackoverflow.com/questions/3797239/insert-new-item-in-array-on-any-position-in-php
			array_splice( $aSubjectArray, $nDesiredPosition, 0, $mValue );
		}
		return $aSubjectArray;
	}
}

if( get_option('allow_cloudflare_flexible_check')  !== 'no') {
	$iMedia_Cf_Flexible_Ssl_Check = new iMedia_Cloudflare_Flexible_SSL();
	$iMedia_Cf_Flexible_Ssl_Check->run();
}


//******************* TinyMCE Schema.org markup *******************//
function tsm_get_extended_valid_elements() {
	$elements = array(
		'@' => array('id','class','style','title','itemscope','itemtype','itemprop','datetime','rel'),
		'article', 'div', 'p', 'dl', 'dt', 'dd', 'ul', 'li', 'span',
		'a' => array('href','name','target','rev','charset','lang','tabindex','accesskey','type','class','onfocus','onblur'),
                'img' => array('src','alt','width','height'
                ),
		'meta' => array('content'),
		'link' => array('href'),
		'time' => array('itemprop')
	);
	return apply_filters( 'tsm_extended_valid_elements', $elements );
}

function tsm_tinymce_init( $settings ) {
	if( !empty( $settings['extended_valid_elements'] ) ) {
		$settings['extended_valid_elements'] .= ',';
	}
	$result = $settings['extended_valid_elements'];
	$elements = tsm_get_extended_valid_elements();
	foreach ( $elements as $key => $element ) {
		if ( is_array( $element ) && !empty( $key ) ) {
			$name = $key;
			$attributes = $element;
		} else {
			$name = $element;
			$attributes = array();
		}

		if ( !empty( $result ) ) {
			$result .= ',';
		}

		$result .= $name;

		if ( !empty( $attributes ) ) {
			$result .= '[' . implode('|', $attributes) . ']';
		}
	}
	$settings['extended_valid_elements'] = $result;
	if ( !isset($settings['valid_children'] ) ) {
		$settings['valid_children'] = '';
	}
	$settings['valid_children'] .= '+body[meta],+div[meta],+body[link],+div[link]';
	return $settings;
}

function hide_recaptcha_badge_head() {
    ?><style type="text/css">.grecaptcha-badge {visibility: hidden;display: none;}</style><?php
}

// Disable use XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );
// Disable X-Pingback to header
add_filter( 'wp_headers', 'disable_x_pingback' );
function disable_x_pingback( $headers ) {
    unset( $headers['X-Pingback'] );
	return $headers;
}


