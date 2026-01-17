<?php
/**
 * jQuery Cleanup - Remove jQuery Migrate on Frontend
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_jQuery_Cleanup_Remove_Migrate_Frontend extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'jquery-cleanup-remove-migrate-frontend',
				'name'               => __( 'Remove jQuery Migrate on Frontend', 'plugin-wpshadow' ),
				'description'        => __( 'Remove the legacy jQuery Migrate dependency on the frontend.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'jquery-cleanup',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'jquery-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_jquery-cleanup_remove_migrate_frontend';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'wp_default_scripts', array( $this, 'remove_jquery_migrate' ) );
	}

	public function remove_jquery_migrate( \WP_Scripts $scripts ): void {
		$keep_admin = get_option( 'wpshadow_jquery-cleanup_keep_admin', true );
		if ( is_admin() && $keep_admin ) {
			return;
		}

		if ( ! isset( $scripts->registered['jquery'] ) ) {
			return;
		}

		$script = $scripts->registered['jquery'];
		if ( empty( $script->deps ) ) {
			return;
		}

		$original_deps = (array) $script->deps;
		$script->deps  = array_diff( $original_deps, array( 'jquery-migrate' ) );

		if ( $original_deps !== $script->deps && get_option( 'wpshadow_jquery-cleanup_log_removals', false ) ) {
			$this->log_activity( 'jquery_migrate_removed', 'Removed jQuery Migrate dependency from frontend', 'info' );
		}
	}
}
