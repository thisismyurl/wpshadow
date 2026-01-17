<?php
/**
 * Asset Version Removal - Remove CSS Versions
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Asset_Version_Removal_Remove_CSS extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'asset-version-removal-remove-css',
				'name'               => __( 'Remove CSS Versions', 'plugin-wpshadow' ),
				'description'        => __( 'Strip version query strings from enqueued styles to improve caching.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'asset-version-removal',
				'category'           => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'asset-version-removal' ) ) {
			return;
		}

		$option_name = 'wpshadow_asset-version-removal_remove_css_versions';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'style_loader_src', array( $this, 'remove_version' ), 10 );
	}

	public function remove_version( $src ) {
		if ( ! is_string( $src ) || strpos( $src, 'ver=' ) === false ) {
			return $src;
		}

		// Honor preserve-plugin-versions toggle for plugin assets.
		if ( $this->should_preserve_plugin_version( $src ) ) {
			return $src;
		}

		return remove_query_arg( 'ver', $src );
	}

	private function should_preserve_plugin_version( string $src ): bool {
		$preserve = get_option( 'wpshadow_asset-version-removal_preserve_plugin_versions', false );
		if ( ! $preserve ) {
			return false;
		}

		// Treat URLs containing /plugins/ as plugin assets.
		return strpos( $src, '/plugins/' ) !== false;
	}
}
