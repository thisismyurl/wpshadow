<?php
/**
 * Asset Version Removal - Preserve Plugin Versions
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Asset_Version_Removal_Preserve_Plugin_Versions extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'asset-version-removal-preserve-plugin-versions',
				'name'               => __( 'Preserve Plugin Versions', 'plugin-wpshadow' ),
				'description'        => __( 'Keep version query strings on plugin assets to avoid cache confusion for third-party files.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
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

		$option_name = 'wpshadow_asset-version-removal_preserve_plugin_versions';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}
	}
}
