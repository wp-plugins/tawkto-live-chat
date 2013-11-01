<?php
/*
Plugin Name: Tawk.to Live Chat
Plugin URI: https://tawk.to
Description: Embeds Tawk.to live chat widget to every page
Version: 0.1.0
Author: Tawkto
*/

if(!class_exists('TawkTo_Settings')){

	class TawkTo_Settings{

		public function __construct(){
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
		}
		public function admin_init(){
			add_settings_section(
				'tawkto-section',
				'Embed Code',
				array(&$this, 'settings_section_text'),
				'tawkto_plugin'
			);

			add_settings_field(
				'tawkto-embed-code',
				'Embed code',
				array(&$this, 'settings_field_input_text'),
				'tawkto_plugin',
				'tawkto-section',
				array(
					'field' => 'embed_code'
				)
			);

			register_setting('tawkto-group', 'tawkto-embed-code');
		}

		public function settings_section_text(){
			echo 'Please paste the embed code from within the tawk.to dashboard into the text area below.<br />' .
				'No account ? <a href="https://tawk.to/?utm_source=wpdirectory&utm_medium=link&utm_campaign=signup" target="_blank">Get one for free here</a>';
		}

		public function settings_field_input_text(){
			$value = get_option('tawkto-embed-code');
			echo sprintf('<textarea name="tawkto-embed-code" id="tawkto-embed-code" rows="10" cols="50">%s</textarea>', $value);
		}

		public function add_menu(){
			add_options_page(
				'Tawk.to Settings',
				'Tawk.to',
				'manage_options',
				'tawkto_plugin',
				array(&$this, 'create_plugin_settings_page')
			);
		}

		public function create_plugin_settings_page(){
			if(!current_user_can('manage_options'))	{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
		}
	}
}

if(!class_exists('TawkTo')){
	class TawkTo{
		public function __construct(){
			$tawkto_settings = new TawkTo_Settings();
		}

		public static function activate(){
			add_option("tawkto-embed-code", '', '', 'yes');
		}

		public static function deactivate(){
			delete_option('tawkto-embed-code');
		}

		public function print_embed_code(){
			$embed_code = get_option('tawkto-embed-code');

			if(!empty($embed_code)){
				echo $embed_code;
			}
		}
	}
}

if(class_exists('TawkTo')){
	register_activation_hook(__FILE__, array('TawkTo', 'activate'));
	register_deactivation_hook(__FILE__, array('TawkTo', 'deactivate'));

	$tawkto = new TawkTo();

	if(isset($tawkto)){
		function plugin_settings_link($links){
			$settings_link = '<a href="options-general.php?page=tawkto_plugin">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
	}

	add_action('wp_footer',  array($tawkto, 'print_embed_code'));
}