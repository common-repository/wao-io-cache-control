<?php
/**
 * @package	wao.io Cache Control
 * @author	Avenga Germany GmbH
 * @version 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: wao.io Cache Control
 * Text Domain: wao-io-cache-control
 * Domain Path: /languages
 * Plugin URI: https://wao.io/en/features/loadtime-optimization/caching
 * Description: Automatically clear wao.io optimizer cache from WordPress
 * Short Description: Automatically clear wao.io optimizer cache from WordPress
 * Author: Avenga Germany GmbH
 * Version: 1.0.0
 * Author URI: https://avenga.com
 * Requires at least: 4.8
 * Tested up to: 5.5
 * Requires PHP: 5.6
 */

if ( is_admin() ) {
	define( 'wao_io_cc__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'wao_io_cc__CACHECONTROL_PATH', 'cache-control.php' );
	define( 'wao_io_cc__SETTINGS_PATH', 'settings.php' );
	define( 'wao_io_cc__PLUGIN_VERSION', '1.0.0');

	function wao_io_cc_settings_init() {
		add_settings_section(
			'wao_io_api_settings_section', // section ID
			'wao.io Cache Control', // display title (default englisch)
			'wao_io_before_settings_callback', // callback function to be called when opening section
			'general' // settings page ID (where to render: default general settings page)
		);
		add_settings_field(
			'wao_io_apikey', // option field ID
			'API Key', // display title (default englisch)
			'wao_io_render_input_callback', // generic input field renderer
			'general', // settings page ID (where to render: default general settings page)
			'wao_io_api_settings_section', // section inside settings page
			array( // arguments passed to generic input field renderer
				'wao_io_apikey' // option field ID
			)
		);
		add_settings_field(
			'wao_io_siteid', // option field ID
			'Site ID', // display title (default englisch)
			'wao_io_render_input_callback', // generic input field renderer
			'general', // settings page ID (where to render: default general settings page)
			'wao_io_api_settings_section', // section inside settings page
			array( // arguments passed to generic input field renderer
				'wao_io_siteid' // option field ID
			)
		);
		register_setting(
			'general', // settings page ID (where to render: default general settings page)
			'wao_io_apikey', // id/Name der Option
			'esc_attr' // validation callback (built-in esc_attr)
		);
		register_setting(
			'general', // settings page ID (where to render: default general settings page)
			'wao_io_siteid', // id/Name der Option
			'esc_attr' // validation callback (built-in esc_attr)
		);
	}
	add_action('admin_init', 'wao_io_cc_settings_init');

	// generic input field renderer as a callback for all settings options
	function wao_io_render_input_callback($args) {
		$option = get_option($args[0]);
		echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" class="regular-text code" />';
	}

	function wao_io_before_settings_callback() { // above settings section:
		?>
			<p id="wao-io-settings">
				<?php _e( 'If you do not have an API key yet, please contact', 'wao-io-cache-control' ); ?>
				<a href="https://wao.io/#wordpress-plugin-cachecontrol">
					wao.io
				</a>
				<?php _e( 'support!', 'wao-io-cache-control' ); ?>
			</p>
		<?php
	}

	// API Functions

	// Plugins whose main purpose is contact with a third party API have implicit consent to contact the API.
	// https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines No. 7:
	// "By installing, activating, registering, and configuring plugins that utilize those services, consent is granted for those systems."

	function wao_io_api_ping($modeflag) {
		$url = 'https://api.wao.io/v1/notify';
		$data = wao_io_api_post($url, $modeflag);
		set_transient( 'wao_io_cc_message', $data, 5*MINUTE_IN_SECONDS );
	}


	function wao_io_api_clear_cache($modeflag) {
		$url = 'https://api.wao.io/v1/sites/' . get_option('wao_io_siteid') . '/caches?action=invalidate';
		$data = wao_io_api_post($url, $modeflag);
		set_transient( 'wao_io_cc_message', $data, 5*MINUTE_IN_SECONDS );
	}

	function wao_io_api_post($url, $modeflag) {
		$apikey = get_option('wao_io_apikey');
		$siteid = get_option('wao_io_siteid');

		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'user-agent' => 'wao.io Cache Control (WordPress-Plugin) ' . wao_io_cc__PLUGIN_VERSION,
			'blocking' => true,
			'headers' => array(
				'accept' => 'application/json',
				'Authorization' => "Bearer $apikey",
				'Initiator' => $modeflag
			),
			'cookies' => array(),
			'body' => array()
			)
		);

		$title = esc_html__( 'Could not invalidate your cache at wao.io.', 'wao-io-cache-control' );
		$notice_class = 'error'; // Default to error, unless we are sure of success
		$status = '';
		$site_id = '';
		$content = '';

		if ( is_wp_error( $response ) ) {
			$content = sanitize_text_field($response->get_error_message());
		} else {

			if (isset($response['response']['code'])) {
				if ($response['response']['code'] < 400) {
					$title = esc_html__( 'Successfully invalidated your cache at wao.io.', 'wao-io-cache-control' );
					$notice_class = 'success';
				}

				if ($response['body']) {
					$api_response = json_decode($response['body']);
					if ($api_response) {
						/* if (isset($api_response->status)) {
							$status = sanitize_text_field( $api_response->status );
						} */
						if (isset($api_response->siteId)) {
							$site_id = sanitize_text_field( $api_response->siteId );
						}
					} else {
						$content = sanitize_text_field( $response['body'] );
					}
				}

			}
		}

		$data = array( 'title'=>$title,'content'=>$content, 'status'=>$status, 'site_id'=>$site_id, 'notice_class'=>$notice_class );
		/* Prepare data to store in transient message object, so we can refresh to styled page and show it in notifications
		 * set maximum expiry to 5 minutes (should expire or deleted before anyway,
		 * see https://codex.wordpress.org/Transients_API)
		 */
		return $data;
	}

	// Plugin Setup

	function wao_io_cc_menu_setup() {
		$wao_io_cc_page_title = 'wao.io Cache-Control'; // page / tab title
		$wao_io_cc_menu_title = 'wao.io Cache Control'; // title in menu
		// Capability level must not be empty;
		// everyone who may publish posts can trigger an implicit cache invalidation
		// so they are alos allowed to use the button:
		$wao_io_cc_capability = 'publish_posts'; //
		$wao_io_cc_slug = 'wao_io-cache-control'; // slug like in url ?page=wao.io
		$wao_io_cc_opening_callback = 'wao_io_cc_controlpage';
		$wao_io_cc_settings_callback = 'wao_io_cc_settings';
		$wao_io_cc_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI0LjAuMiwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkViZW5lXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCA3OS42IDQ2LjMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDc5LjYgNDYuMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMEE1QjY7fQoJLnN0MXtmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMERBQzY7fQoJLnN0MntmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMEMzQzM7fQoJLnN0M3tmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiM0REVERDg7fQoJLnN0NHtmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMDk2QUY7fQoJLnN0NXtmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMENGQzU7fQoJLnN0NntmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMEI0QkM7fQoJLnN0N3tmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtmaWxsOiMwMEU2Qzg7fQo8L3N0eWxlPgo8dGl0bGU+d2FvLmlvIGxvZ28gaW4gY29sb3I8L3RpdGxlPgo8Zz4KCTxnPgoJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xOS45LDQ2LjNWMjMuMkwwLDM0LjhMMTkuOSw0Ni4zeiIvPgoJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik01OS43LDQ2LjNWMjMuMkwzOS44LDM0LjhMNTkuNyw0Ni4zeiIvPgoJCTxwYXRoIGNsYXNzPSJzdDIiIGQ9Ik0zOS44LDM0LjhWMTEuN0wxOS45LDIzLjJMMzkuOCwzNC44eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDMiIGQ9Ik03OS41LDM0LjhWMTEuN0w1OS43LDIzLjJMNzkuNSwzNC44eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDQiIGQ9Ik0wLDExLjd2MjMuMWwxOS45LTExLjVMMCwxMS43eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDUiIGQ9Ik0zOS44LDExLjd2MjMuMWwxOS45LTExLjVMMzkuOCwxMS43eiIvPgoJCTxwYXRoIGNsYXNzPSJzdDYiIGQ9Ik0xOS45LDIzLjJ2MjMuMWwxOS45LTExLjVMMTkuOSwyMy4yeiIvPgoJCTxwYXRoIGNsYXNzPSJzdDciIGQ9Ik01OS43LDIzLjJ2MjMuMWwxOS45LTExLjVMNTkuNywyMy4yeiIvPgoJCTxwYXRoIGNsYXNzPSJzdDQiIGQ9Ik01OS43LDB2NC42bDQtMi4zTDU5LjcsMHoiLz4KCQk8cGF0aCBjbGFzcz0ic3QyIiBkPSJNNTkuNyw3LjR2OC4zbDcuMS00LjFMNTkuNyw3LjR6Ii8+CgkJPHBhdGggY2xhc3M9InN0NSIgZD0iTTY4LjgsNS4zdjEyLjVsMTAuOC02LjNMNjguOCw1LjN6Ii8+Cgk8L2c+CjwvZz4KPC9zdmc+Cg==';

		add_menu_page(
			$wao_io_cc_page_title,
			$wao_io_cc_menu_title,
			$wao_io_cc_capability,
			'wao_io-cache-control',
			$wao_io_cc_opening_callback,
			$wao_io_cc_icon
		);
	}

	function wao_io_cc_controlpage() {
		require_once(wao_io_cc__PLUGIN_DIR . wao_io_cc__CACHECONTROL_PATH);
	}

	function wao_io_api_clear_cache_manual() {
		wao_io_api_clear_cache('button');
		wp_redirect(admin_url('admin.php?page=wao_io-cache-control'));
		die();
	}

	add_action('admin_menu', 'wao_io_cc_menu_setup');
	add_action('admin_post_clear_cache', 'wao_io_api_clear_cache_manual');

	register_activation_hook(__FILE__, 'wao_io_cc_activation'  );
	function wao_io_cc_activation(){
		add_action( 'admin_notices', 'wao_io_activation_notice' );
	}
	function wao_io_activation_notice() {
		?>
		<div class="updated notice is-dismissible">
			<p><?php _e( 'Activated wao.io Cache Control plugin.', 'wao-io-cache-control' ); ?></p>
		</div>
		<?php
		wao_io_api_ping('plugin/activate');
	}

	// Handle WordPress content changes

	// post_updated returns no status, but also triggered when saving as draft?
	// publish_post also triggered when updating a published post
	add_action('publish_post', 'wao_io_cc_contentchange_publish_post', 10, 3);
	add_action('publish_page', 'wao_io_cc_contentchange_publish_page', 10, 3);
	add_action('after_switch_theme', 'wao_io_cc_contentchange_switch_theme', 10, 3);
	add_action('customize_save_after', 'wao_io_cc_contentchange_customize_theme', 10, 3);
	add_action('update_theme_complete_actions', 'wao_io_cc_contentchange_update_theme', 10, 3);
	add_action('upgrader_process_complete', 'wao_io_cc_contentchange_upgrader_complete', 10, 3);

	function wao_io_cc_contentchange_publish_post() {
		wao_io_api_clear_cache('auto/publish_post');
	}

	function wao_io_cc_contentchange_publish_page() {
		wao_io_api_clear_cache('auto/publish_page');
	}

	function wao_io_cc_contentchange_switch_theme() {
		wao_io_api_clear_cache('auto/switch_theme');
	}

	function wao_io_cc_contentchange_customize_theme() {
		wao_io_api_clear_cache('auto/customize_theme');
	}

	function wao_io_cc_contentchange_update_theme() {
		wao_io_api_clear_cache('auto/update_theme');
	}

	// TODO: Does using upgrader_complete obsolete update_theme ?
	function wao_io_cc_contentchange_upgrader_complete($upgrader_object, $options) {
		if ($options['action'] == 'update'){
			wao_io_api_clear_cache('auto/upgrader_complete/theme');
		 }
	}

	function wao_io_cc_load_plugin_textdomain() {
		load_plugin_textdomain( 'wao-io-cache-control', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'wao_io_cc_load_plugin_textdomain' );

}
?>
