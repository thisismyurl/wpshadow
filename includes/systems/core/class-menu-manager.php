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

		// Post Types (Custom Post Type Management)
		add_submenu_page(
			'wpshadow',
			__( 'Post Types', 'wpshadow' ),
			__( 'Post Types', 'wpshadow' ),
			'manage_options',
			'wpshadow-post-types',
			array( 'WPShadow\Admin\Post_Types_Page', 'render_page' )
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
		add_submenu_page(
			'wpshadow',
			__( 'WPShadow Academy', 'wpshadow' ),
			__( 'Academy', 'wpshadow' ),
			'manage_options',
			'wpshadow-academy',
			array( 'WPShadow\Academy\Academy_UI', 'render_academy_page' )
		);

		// Achievements (with Leaderboard & Rewards as submenus)
		add_submenu_page(
			'wpshadow',
			__( 'Achievements', 'wpshadow' ),
			__( 'Achievements', 'wpshadow' ),
			'read',
			'wpshadow-achievements',
			array( 'WPShadow\Gamification\Gamification_UI', 'render_achievements_page' )
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
		$redirects = array(
			'wpshadow-guardian-reports'       => 'wpshadow-reports',
			'wpshadow-guardian-notifications' => 'wpshadow-settings&tab=notifications',
			'wpshadow-scan-settings'          => 'wpshadow-settings&tab=scan-settings',
			'wpshadow-privacy'                => 'wpshadow-settings&tab=privacy-dashboard',
			'wpshadow-tools'                  => 'wpshadow-utilities',
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
