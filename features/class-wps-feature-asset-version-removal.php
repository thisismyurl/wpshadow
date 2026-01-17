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
				'name'               => __( 'Remove Asset Version Strings', 'plugin-wpshadow' ),
				'description'        => __( 'Help browsers cache your files better so repeat visitors load your site faster so browsers cache them longer between visits. Keeps automatic cache busting when files change by respecting WordPress versioning and file modification checks. Improves repeat visitor speed, lowers bandwidth, and reduces requests to your server while keeping assets up to date because changed files still prompt browsers to fetch fresh copies.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'sub_features'       => array(
					'remove_css_versions'      => __( 'Remove CSS Versions', 'plugin-wpshadow' ),
					'remove_js_versions'       => __( 'Remove JavaScript Versions', 'plugin-wpshadow' ),
					'preserve_plugin_versions' => __( 'Preserve Plugin Versions', 'plugin-wpshadow' ),
				),
			)
		);

		$this->set_default_sub_features();
		$this->log_activity( 'feature_initialized', 'Asset Version Removal feature initialized', 'info' );
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * Only attaches Site Health tests; child features perform the removals.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['asset_version_removal'] = array(
			'label' => __( 'Asset Version Removal', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_asset_version_removal' ),
		);

		return $tests;
	}

	/**
	 * Seed default sub-feature options if missing.
	 *
	 * @return void
	 */
	private function set_default_sub_features(): void {
		$defaults = array(
			'remove_css_versions'      => true,
			'remove_js_versions'       => true,
			'preserve_plugin_versions' => false,
		);

		foreach ( $defaults as $key => $default_value ) {
			$option_name   = 'wpshadow_asset-version-removal_' . $key;
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}

	/**
	 * Site Health test for asset version removal.
	 *
	 * @return array Test result.
	 */
	public function test_asset_version_removal(): array {
		$remove_css = get_option( 'wpshadow_asset-version-removal_remove_css_versions', true );
		$remove_js  = get_option( 'wpshadow_asset-version-removal_remove_js_versions', true );

		$status = ( $remove_css && $remove_js ) ? 'good' : 'recommended';
		$label  = ( $remove_css && $remove_js ) ?
			__( 'Asset version strings are being removed', 'plugin-wpshadow' ) :
			__( 'Asset version removal could be improved', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Removing version strings from CSS and JavaScript URLs improves browser caching and page load times.', 'plugin-wpshadow' )
			),
			'actions'     => '',
			'test'        => 'asset_version_removal',
		);
	}
}
