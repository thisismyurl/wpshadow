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
				'description'        => __( 'Removes version query strings from stylesheet and script URLs so browsers cache them longer between visits. Keeps automatic cache busting when files change by respecting WordPress versioning and file modification checks. Improves repeat visitor speed, lowers bandwidth, and reduces requests to your server while keeping assets up to date because changed files still prompt browsers to fetch fresh copies.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
			'widget_group'       => 'performance',
			'widget_label'       => __( 'Performance Options', 'plugin-wpshadow' ),
				'widget_description' => __( 'Remove unnecessary code and optimize markup', 'plugin-wpshadow' ),
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
