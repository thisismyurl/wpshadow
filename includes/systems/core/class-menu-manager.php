<?php
/**
 * WPShadow Menu Manager
 *
 * Centralized registration of WordPress admin menus for WPShadow.
 * Extracted from wpshadow.php as part of Phase 4.5 refactoring.
 *
 * Philosophy: Commandment #7 (Ridiculously Good - obvious structure)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;
/**
 * Manages WPShadow admin menu registration and setup
 */
class Menu_Manager {


	/**
	 * Initialize menus (call on admin_menu hook)
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_legacy_redirects' ) );
	}

	/**
	 * Register all WPShadow admin menus
	 *
	 * @return void
	 */
	public static function register_menus() {
		$admin_capability    = self::get_admin_capability();
		$analyst_capability  = self::get_analyst_capability();
		$core_pages_released = self::are_core_pages_released();

		// Top-level menu.
		add_menu_page(
			'WPShadow',
			'WPShadow',
			$admin_capability,
			'wpshadow',
			'wpshadow_render_dashboard',
			'dashicons-shield-alt',
			999
		);

		// Dashboard submenu.
		add_submenu_page(
			'wpshadow',
			__( 'Dashboard', 'wpshadow' ),
			__( 'Dashboard', 'wpshadow' ),
			$admin_capability,
			'wpshadow',
			'wpshadow_render_dashboard'
		);

		// Hidden diagnostic detail page used by dashboard and report links.
		add_submenu_page(
			'wpshadow',
			__( 'Diagnostic Detail', 'wpshadow' ),
			__( 'Diagnostic Detail', 'wpshadow' ),
			$analyst_capability,
			'wpshadow-diagnostic',
			'wpshadow_render_diagnostic_detail_page'
		);
		remove_submenu_page( 'wpshadow', 'wpshadow-diagnostic' );

		if ( $core_pages_released ) {
			// Findings.
			add_submenu_page(
				'wpshadow',
				__( 'Findings', 'wpshadow' ),
				__( 'Findings', 'wpshadow' ),
				$analyst_capability,
				'wpshadow-findings',
				'wpshadow_render_findings'
			);

			// Automations page removed.
		}

		// Scan Settings is now a tab on Settings page, not a separate menu.
		// Legacy redirect handled in handle_legacy_redirects().

		
		// Note: Vault submenu removed - Vault is a pro feature handled by wpshadow-pro-vault plugin.
		// Vault Light functionality remains available without menu item.
	}

	/**
	 * Handle legacy URL redirects for bookmarks/external links
	 *
	 * @return void
	 */
	public static function handle_legacy_redirects() {
		if ( ! Form_Param_Helper::has_get( 'page' ) ) {
			return;
		}

		$page                = Form_Param_Helper::get( 'page', 'text', '' );
		$core_pages_released = self::are_core_pages_released();

		if ( in_array( $page, array( 'wpshadow-findings' ), true ) && ! $core_pages_released ) {
			if ( current_user_can( self::get_analyst_capability() ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
				exit;
			}
		}

		if ( 'wpshadow-academy' === $page && class_exists( '\\WPShadow\\Academy\\Academy_Release_Gate' ) && ! \WPShadow\Academy\Academy_Release_Gate::is_available() ) {
			if ( current_user_can( self::get_admin_capability() ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
				exit;
			}
		}

		$redirects = array(
			'wpshadow-scan-settings'          => 'wpshadow',
			'wpshadow-privacy'                => 'wpshadow',
			'wpshadow-tools'                  => 'wpshadow',
			'wpshadow-defensive'              => 'wpshadow',
			'wpshadow-kpi'                    => 'wpshadow',
			'wpshadow-learning'               => 'wpshadow',
			'wpshadow-cultural'               => 'wpshadow',
		);

		if ( isset( $redirects[ $page ] ) ) {
			if ( current_user_can( self::get_analyst_capability() ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=' . $redirects[ $page ] ) );
				exit;
			}
		}
	}

	/**
	 * Register WPShadow settings link on plugins page
	 *
	 * @param array $links Plugin action links.
	 * @return array Modified action links.
	 */
	public static function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow' ) ) . '">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Check whether Findings page is released.
	 *
	 * @since 0.6093.1200
	 * @return bool True when pages should be visible.
	 */
	private static function are_core_pages_released() {
		$release_datetime = (string) apply_filters( 'wpshadow_core_pages_release_datetime', '2026-04-30 23:59:59' );

		try {
			$timezone = wp_timezone();
			$release  = new \DateTimeImmutable( $release_datetime, $timezone );
			$now      = new \DateTimeImmutable( 'now', $timezone );

			return $now >= $release;
		} catch ( \Exception $exception ) {
			return false;
		}
	}

	/**
	 * Get capability required for admin-level WPShadow pages.
	 *
	 * @return string Capability name.
	 */
	private static function get_admin_capability() {
		if ( is_multisite() && is_network_admin() ) {
			return 'manage_network_options';
		}

		return 'manage_options';
	}

	/**
	 * Get capability required for analyst/read-only WPShadow pages.
	 *
	 * @return string Capability name.
	 */
	private static function get_analyst_capability() {
		return self::get_admin_capability();
	}
}
