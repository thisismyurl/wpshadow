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

use WPShadow\Admin\Post_Types_Page;
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
		add_action( 'admin_enqueue_scripts', array( 'WPShadow\Admin\Post_Types_Page', 'enqueue_assets' ) );
	}

	/**
	 * Register all WPShadow admin menus
	 *
	 * @return void
	 */
	public static function register_menus() {
		$core_pages_released = self::are_core_pages_released();

		// Top-level menu
		add_menu_page(
			'WPShadow',
			'WPShadow',
			'read',
			'wpshadow',
			'wpshadow_render_dashboard',
			'dashicons-shield-alt',
			999
		);

		// Dashboard submenu
		add_submenu_page(
			'wpshadow',
			__( 'Dashboard', 'wpshadow' ),
			__( 'Dashboard', 'wpshadow' ),
			'read',
			'wpshadow',
			'wpshadow_render_dashboard'
		);

		if ( $core_pages_released ) {
			// Findings (Kanban Board)
			add_submenu_page(
				'wpshadow',
				__( 'Findings', 'wpshadow' ),
				__( 'Findings', 'wpshadow' ),
				'read',
				'wpshadow-findings',
				'wpshadow_render_findings'
			);

			// Guardian (Diagnostics & Treatments System)
			add_submenu_page(
				'wpshadow',
				__( 'Guardian', 'wpshadow' ),
				__( 'Guardian', 'wpshadow' ),
				'read',
				'wpshadow-guardian',
				'wpshadow_render_guardian'
			);

			// Automations (Workflow Automation)
			add_submenu_page(
				'wpshadow',
				__( 'Automations', 'wpshadow' ),
				__( 'Automations', 'wpshadow' ),
				'read',
				'wpshadow-automations',
				'wpshadow_render_workflow_builder'
			);
		}

		// Reports (Analytics & Insights)
		add_submenu_page(
			'wpshadow',
			__( 'Reports', 'wpshadow' ),
			__( 'Reports', 'wpshadow' ),
			'manage_options',
			'wpshadow-reports',
			'wpshadow_render_reports'
		);

		// Settings (including Notifications & Scan Settings as tabs)
		add_submenu_page(
			'wpshadow',
			__( 'Settings', 'wpshadow' ),
			__( 'Settings', 'wpshadow' ),
			'manage_options',
			'wpshadow-settings',
			'wpshadow_render_settings'
		);

		Post_Types_Page::subscribe();

		// Scan Settings is now a tab on Settings page, not a separate menu
		// Legacy redirect handled in handle_legacy_redirects()

		// Utilities (Advanced Features & Tools)
		add_submenu_page(
			'wpshadow',
			__( 'Utilities', 'wpshadow' ),
			__( 'Utilities', 'wpshadow' ),
			'read',
			'wpshadow-utilities',
			'wpshadow_render_utilities'
		);

		// Academy (moved above Help priority)
		$academy_available = true;
		if ( class_exists( '\\WPShadow\\Academy\\Academy_Release_Gate' ) ) {
			$academy_available = \WPShadow\Academy\Academy_Release_Gate::is_available();
		}

		if ( $academy_available ) {
			add_submenu_page(
				'wpshadow',
				__( 'WPShadow Academy', 'wpshadow' ),
				__( 'Academy', 'wpshadow' ),
				'manage_options',
				'wpshadow-academy',
				array( 'WPShadow\\Academy\\Academy_UI', 'render_academy_page' )
			);
		}

		// Achievements (with Leaderboard & Rewards as submenus)
		if ( class_exists( '\WPShadow\Gamification\Gamification_Release_Gate' ) && \WPShadow\Gamification\Gamification_Release_Gate::is_released() ) {
			add_submenu_page(
				'wpshadow',
				__( 'Achievements', 'wpshadow' ),
				__( 'Achievements', 'wpshadow' ),
				'read',
				'wpshadow-achievements',
				array( 'WPShadow\Gamification\Gamification_UI', 'render_achievements_page' )
			);
		}

		// Help & Documentation
		add_submenu_page(
			'wpshadow',
			__( 'Help', 'wpshadow' ),
			__( 'Help', 'wpshadow' ),
			'read',
			'wpshadow-help',
			'wpshadow_render_help'
		);

		// Note: Vault submenu removed - Vault is a pro feature handled by wpshadow-pro-vault plugin
		// Vault Light functionality remains available without menu item
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

		$page      = Form_Param_Helper::get( 'page', 'text', '' );
		$core_pages_released = self::are_core_pages_released();

		if ( in_array( $page, array( 'wpshadow-findings', 'wpshadow-guardian', 'wpshadow-automations' ), true ) && ! $core_pages_released ) {
			if ( current_user_can( 'read' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
				exit;
			}
		}

		if ( 'wpshadow-academy' === $page && class_exists( '\\WPShadow\\Academy\\Academy_Release_Gate' ) && ! \WPShadow\Academy\Academy_Release_Gate::is_available() ) {
			if ( current_user_can( 'manage_options' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
				exit;
			}
		}

		if ( in_array( $page, array( 'wpshadow-achievements', 'wpshadow-leaderboard', 'wpshadow-rewards' ), true ) && class_exists( '\WPShadow\Gamification\Gamification_Release_Gate' ) && ! \WPShadow\Gamification\Gamification_Release_Gate::is_released() ) {
			if ( current_user_can( 'read' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
				exit;
			}
		}

		$redirects = array(
			'wpshadow-guardian-reports'       => 'wpshadow-reports',
			'wpshadow-guardian-notifications' => 'wpshadow-settings&tab=notifications',
			'wpshadow-scan-settings'          => 'wpshadow-settings&tab=scan-settings',
			'wpshadow-privacy'                => 'wpshadow-settings&tab=privacy-dashboard',
			'wpshadow-tools'                  => 'wpshadow-utilities',
		);

		if ( isset( $redirects[ $page ] ) ) {
			$capability = ( 'wpshadow-guardian-reports' === $page || 'wpshadow-guardian-notifications' === $page ) ? 'manage_options' : 'read';

			if ( current_user_can( $capability ) ) {
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
	 * Check whether Findings, Guardian, and Automations pages are released.
	 *
		 * @since 1.6093.1200
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
}
