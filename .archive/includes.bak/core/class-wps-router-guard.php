<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Router_Guard {

	public static function execute(): void {
		if ( ! is_admin() ) {
			return;
		}

		$raw_module = isset( $_GET['module'] ) ? sanitize_key( wp_unslash( $_GET['module'] ) ) : '';
		if ( ! empty( $raw_module ) ) {
			$module_slug = str_contains( $raw_module, '-wpshadow' ) ? $raw_module : $raw_module . '-wpshadow';
			if ( ! WPSHADOW_Module_Registry::is_enabled( $module_slug ) ) {

				if ( function_exists( __NAMESPACE__ . '\wpshadow_add_admin_notice' ) ) {
					wpshadow_add_admin_notice( sprintf( __( '%s is disabled. Redirected to Support dashboard.', 'wpshadow' ), ucfirst( $raw_module ) ), 'warning' );
				}
				$parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wpshadow' ) : admin_url( 'admin.php?page=wpshadow' );
				wp_safe_redirect( $parent_url );
				exit;
			}
		}

		$context = WPSHADOW_Tab_Navigation::get_current_context();
		$hub     = $context['hub'];
		$spoke   = $context['spoke'];

		if ( ! empty( $hub ) ) {
			$hub_slug = $hub . '-wpshadow';
			if ( ! WPSHADOW_Module_Registry::is_enabled( $hub_slug ) ) {
				if ( function_exists( __NAMESPACE__ . '\wpshadow_add_admin_notice' ) ) {
					wpshadow_add_admin_notice( sprintf( __( '%s hub is disabled. Redirected to Support dashboard.', 'wpshadow' ), ucfirst( $hub ) ), 'warning' );
				}
				$parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wpshadow' ) : admin_url( 'admin.php?page=wpshadow' );
				wp_safe_redirect( $parent_url );
				exit;
			}

			if ( ! empty( $spoke ) ) {
				$spoke_slug = $spoke . '-wpshadow';
				if ( ! WPSHADOW_Module_Registry::is_enabled( $spoke_slug ) ) {
					if ( function_exists( __NAMESPACE__ . '\wpshadow_add_admin_notice' ) ) {
						wpshadow_add_admin_notice( sprintf( __( '%1$s format is disabled. Redirected to %2$s hub.', 'wpshadow' ), strtoupper( $spoke ), ucfirst( $hub ) ), 'warning' );
					}
					$parent_url = is_network_admin() ? network_admin_url( 'admin.php?page=wpshadow&module=' . $hub ) : admin_url( 'admin.php?page=wpshadow&module=' . $hub );
					wp_safe_redirect( $parent_url );
					exit;
				}
			}
		}
	}
}
