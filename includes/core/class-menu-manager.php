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

		// Action Items (Kanban Board)
		add_submenu_page(
			'wpshadow',
			__( 'Action Items', 'wpshadow' ),
			__( 'Action Items', 'wpshadow' ),
			'read',
			'wpshadow-action-items',
			'wpshadow_render_action_items'
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

		// Workflows (Automation)
		add_submenu_page(
			'wpshadow',
			__( 'Workflows', 'wpshadow' ),
			__( 'Workflows', 'wpshadow' ),
			'read',
			'wpshadow-workflows',
			'wpshadow_render_workflow_builder'
		);

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

		// Scan Settings is now a tab on Settings page, not a separate menu
		// Legacy redirect handled in handle_legacy_redirects()

		// Tools (Utilities & Features)
		add_submenu_page(
			'wpshadow',
			__( 'Tools', 'wpshadow' ),
			__( 'Tools', 'wpshadow' ),
			'read',
			'wpshadow-tools',
			'wpshadow_render_tools'
		);

		// Help & Documentation
		add_submenu_page(
			'wpshadow',
			__( 'Help', 'wpshadow' ),
			__( 'Help', 'wpshadow' ),
			'read',
			'wpshadow-help',
			'wpshadow_render_help'
		);
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
		$redirects = array(
			'wpshadow-guardian-reports'       => 'wpshadow-reports',
			'wpshadow-guardian-notifications' => 'wpshadow-settings&tab=notifications',
			'wpshadow-scan-settings'          => 'wpshadow-settings&tab=scan-settings',
			'wpshadow-exit-followups'         => 'wpshadow', // Redirect exit-followups to dashboard
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
}
