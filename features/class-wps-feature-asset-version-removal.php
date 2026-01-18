<?php
/**
 * Feature: Asset Version String Removal
 *
 * Remove version query strings from CSS/JS URLs for improved caching
 * and minor security hardening (obscures WordPress/plugin versions).
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Asset_Version_Removal
 *
 * Remove version query parameters from CSS/JS asset URLs.
 */
final class WPSHADOW_Feature_Asset_Version_Removal extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'asset-version-removal',
				'name'               => __( 'Remove Asset Version Strings', 'wpshadow' ),
				'description'        => __( 'Make your site faster for returning visitors! It stores files in their browser for quick loading but automatically updates them when you make changes to keep everything current.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'sub_features'       => array(
					'remove_css_versions' => array(
						'name'            => __( 'Remove CSS Versions', 'wpshadow' ),
						'description'     => __( 'Strip version query strings from enqueued styles to improve caching.', 'wpshadow' ),
						'default_enabled' => true,
						'version'         => '1.0.0',
					),
					'remove_js_versions' => array(
						'name'            => __( 'Remove JavaScript Versions', 'wpshadow' ),
						'description'     => __( 'Strip version query strings from enqueued scripts to improve caching.', 'wpshadow' ),
						'default_enabled' => true,
						'version'         => '1.0.0',
					),
					'preserve_plugin_versions' => array(
						'name'            => __( 'Preserve Plugin Versions', 'wpshadow' ),
						'description'     => __( 'Keep version query strings on plugin assets to avoid cache confusion for third-party files.', 'wpshadow' ),
						'default_enabled' => false,
						'version'         => '1.0.0',
					),
					'css_ignore_rules' => array(
						'name'            => __( 'CSS Ignore Rules', 'wpshadow' ),
						'description'     => __( 'Specify CSS files to exclude from version removal using regex patterns.', 'wpshadow' ),
						'default_enabled' => true,
						'version'         => '1.0.0',
						'has_settings'    => true,
					),
					'js_ignore_rules' => array(
						'name'            => __( 'JavaScript Ignore Rules', 'wpshadow' ),
						'description'     => __( 'Specify JavaScript files to exclude from version removal using regex patterns.', 'wpshadow' ),
						'default_enabled' => true,
						'version'         => '1.0.0',
						'has_settings'    => true,
					),
					'plugin_ignore_list' => array(
						'name'            => __( 'Plugin Ignore List', 'wpshadow' ),
						'description'     => __( 'Select specific plugins to exclude from version string removal.', 'wpshadow' ),
						'default_enabled' => true,
						'version'         => '1.0.0',
						'has_settings'    => true,
					),
				),
			)
		);

		// Initialize default sub-feature options
		WPSHADOW_Asset_Version_Helpers::init_sub_feature_defaults(
			array(
				'remove_css_versions'      => true,
				'remove_js_versions'       => true,
				'preserve_plugin_versions' => false,
				'css_ignore_rules'         => true,
				'js_ignore_rules'          => true,
				'plugin_ignore_list'       => true,
			),
			'asset-version-removal'
		);

		// Initialize storage options for ignore rules and plugin list
		if ( ! get_option( 'wpshadow_asset-version-removal_css_ignore_patterns' ) ) {
			update_option( 'wpshadow_asset-version-removal_css_ignore_patterns', array() );
		}
		if ( ! get_option( 'wpshadow_asset-version-removal_js_ignore_patterns' ) ) {
			update_option( 'wpshadow_asset-version-removal_js_ignore_patterns', array() );
		}
		if ( ! get_option( 'wpshadow_asset-version-removal_ignored_plugins' ) ) {
			update_option( 'wpshadow_asset-version-removal_ignored_plugins', array() );
		}

		$this->log_activity( 'feature_initialized', 'Asset Version Removal feature initialized', 'info' );
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * Registers Site Health tests and hooks for all sub-features.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		// Register CSS version removal
		if ( WPSHADOW_Asset_Version_Helpers::is_sub_feature_enabled( 'asset-version-removal', 'remove_css_versions' ) ) {
			add_filter( 'style_loader_src', array( $this, 'remove_css_version' ), 10 );
		}

		// Register JavaScript version removal
		if ( WPSHADOW_Asset_Version_Helpers::is_sub_feature_enabled( 'asset-version-removal', 'remove_js_versions' ) ) {
			add_filter( 'script_loader_src', array( $this, 'remove_js_version' ), 10 );
		}

		// Register AJAX handlers for ignore rules
		add_action( 'wp_ajax_wpshadow_save_css_ignore_rules', array( $this, 'ajax_save_css_ignore_rules' ) );
		add_action( 'wp_ajax_wpshadow_save_js_ignore_rules', array( $this, 'ajax_save_js_ignore_rules' ) );
		add_action( 'wp_ajax_wpshadow_save_plugin_ignore_list', array( $this, 'ajax_save_plugin_ignore_list' ) );

		// Preserve plugin versions is handled via option check in the helpers
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['asset_version_removal'] = array(
			'label' => __( 'Asset Version Removal', 'wpshadow' ),
			'test'  => array( $this, 'test_asset_version_removal' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for asset version removal.
	 *
	 * @return array Test result.
	 */
	public function test_asset_version_removal(): array {
		$remove_css = WPSHADOW_Asset_Version_Helpers::is_sub_feature_enabled( 'asset-version-removal', 'remove_css_versions' );
		$remove_js  = WPSHADOW_Asset_Version_Helpers::is_sub_feature_enabled( 'asset-version-removal', 'remove_js_versions' );

		$status = ( $remove_css && $remove_js ) ? 'good' : 'recommended';
		$label  = ( $remove_css && $remove_js ) ?
			__( 'Asset version strings are being removed', 'wpshadow' ) :
			__( 'Asset version removal could be improved', 'wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Removing version strings from CSS and JavaScript URLs improves browser caching and page load times.', 'wpshadow' )
			),
			'actions'     => '',
			'test'        => 'asset_version_removal',
		);
	}

	/**
	 * Remove version parameter from CSS URL.
	 *
	 * @param string|mixed $src CSS file URL.
	 * @return string|mixed Modified URL.
	 */
	public function remove_css_version( $src ) {
		return WPSHADOW_Asset_Version_Helpers::remove_version( $src );
	}

	/**
	 * Remove version parameter from JavaScript URL.
	 *
	 * @param string|mixed $src JavaScript file URL.
	 * @return string|mixed Modified URL.
	 */
	public function remove_js_version( $src ) {
		return WPSHADOW_Asset_Version_Helpers::remove_version( $src );
	}

	/**
	 * Save CSS ignore rules via AJAX.
	 *
	 * @return void
	 */
	public function ajax_save_css_ignore_rules(): void {
		check_ajax_referer( 'wpshadow_save_css_ignore_rules' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$patterns = isset( $_POST['patterns'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['patterns'] ) ) : array();
		$patterns = array_filter( $patterns );

		update_option( 'wpshadow_asset-version-removal_css_ignore_patterns', $patterns );
		wp_send_json_success( array( 'message' => __( 'CSS ignore rules saved successfully.', 'wpshadow' ) ) );
	}

	/**
	 * Save JavaScript ignore rules via AJAX.
	 *
	 * @return void
	 */
	public function ajax_save_js_ignore_rules(): void {
		check_ajax_referer( 'wpshadow_save_js_ignore_rules' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$patterns = isset( $_POST['patterns'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['patterns'] ) ) : array();
		$patterns = array_filter( $patterns );

		update_option( 'wpshadow_asset-version-removal_js_ignore_patterns', $patterns );
		wp_send_json_success( array( 'message' => __( 'JavaScript ignore rules saved successfully.', 'wpshadow' ) ) );
	}

	/**
	 * Save plugin ignore list via AJAX.
	 *
	 * @return void
	 */
	public function ajax_save_plugin_ignore_list(): void {
		check_ajax_referer( 'wpshadow_save_plugin_ignore_list' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$plugins = isset( $_POST['plugins'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['plugins'] ) ) : array();
		$plugins = array_filter( $plugins );

		update_option( 'wpshadow_asset-version-removal_ignored_plugins', $plugins );
		wp_send_json_success( array( 'message' => __( 'Plugin ignore list saved successfully.', 'wpshadow' ) ) );
	}
}
