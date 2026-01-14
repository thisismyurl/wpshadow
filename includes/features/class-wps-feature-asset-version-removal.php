<?php
/**
 * Feature: Asset Version String Removal
 *
 * Remove version query strings from CSS/JS URLs for improved caching
 * and minor security hardening (obscures WordPress/plugin versions).
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPS_Feature_Asset_Version_Removal
 *
 * Remove version query parameters from CSS/JS asset URLs.
 */
final class WPS_Feature_Asset_Version_Removal extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'asset-version-removal',
				'name'                => __( 'Remove Asset Version Strings', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Help visitors load files faster by letting their browser remember them longer', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => true,
				'version'             => '1.0.0',
			'widget_group'        => 'cleanup',
			'widget_label'        => __( 'Code Cleanup', 'plugin-wp-support-thisismyurl' ),
			'widget_description'  => __( 'Remove unnecessary code and optimize markup', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'style_loader_src', array( $this, 'remove_version_strings' ), 10 );
		add_filter( 'script_loader_src', array( $this, 'remove_version_strings' ), 10 );
	}

	/**
	 * Remove version query parameter from asset URLs.
	 *
	 * @param string $src The source URL.
	 * @return string Modified URL without version parameter.
	 */
	public function remove_version_strings( string $src ): string {
		// Check if 'ver=' exists in URL.
		if ( strpos( $src, 'ver=' ) === false ) {
			return $src;
		}

		// Remove the version query parameter.
		return remove_query_arg( 'ver', $src );
	}
}

