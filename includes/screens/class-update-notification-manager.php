<?php
/**
 * Update Notification Manager
 *
 * Provides controls to delete inactive themes, suppress update nags for
 * inactive themes/plugins, and snooze all update notifications until the
 * Updates screen is opened.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WP_Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Update_Notification_Manager {
	private const THEME_OPTION  = 'wpshadow_suppressed_theme_updates';
	private const PLUGIN_OPTION = 'wpshadow_suppressed_plugin_updates';
	private const SNOOZE_OPTION = 'wpshadow_updates_snoozed';

	/**
	 * Bootstrap hooks.
	 */
	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( __CLASS__, 'handle_requests' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_render_notices' ) );
		add_action( 'load-update-core.php', array( __CLASS__, 'clear_snooze_on_updates_page' ) );

		add_filter( 'site_transient_update_themes', array( __CLASS__, 'filter_theme_updates' ) );
		add_filter( 'site_transient_update_plugins', array( __CLASS__, 'filter_plugin_updates' ) );

		add_filter( 'theme_action_links', array( __CLASS__, 'add_theme_actions' ), 10, 3 );
		add_filter( 'plugin_action_links', array( __CLASS__, 'add_plugin_actions' ), 10, 4 );
	}

	/**
	 * Process management actions triggered from admin links.
	 */
	public static function handle_requests(): void {
		$action = isset( $_GET['wpshadow_action'] ) ? sanitize_key( wp_unslash( $_GET['wpshadow_action'] ) ) : '';
		if ( empty( $action ) || ! self::can_manage() ) {
			return;
		}

		$redirect = self::get_referer_or_default();

		switch ( $action ) {
			case 'delete_theme':
				self::handle_theme_delete( $redirect );
				break;
			case 'suppress_theme_update':
				self::handle_theme_suppression( true, $redirect );
				break;
			case 'unsuppress_theme_update':
				self::handle_theme_suppression( false, $redirect );
				break;
			case 'suppress_plugin_update':
				self::handle_plugin_suppression( true, $redirect );
				break;
			case 'unsuppress_plugin_update':
				self::handle_plugin_suppression( false, $redirect );
				break;
			case 'snooze_updates':
				self::snooze_updates();
				self::redirect_with_notice( 'updates_snoozed', $redirect );
				break;
			case 'unsnooze_updates':
				self::clear_snooze();
				self::redirect_with_notice( 'updates_unsnoozed', $redirect );
				break;
			default:
				break;
		}
	}

	/**
	 * Add action links to theme cards (delete + suppress/allow updates).
	 *
	 * @param array    $actions Existing actions.
	 * @param WP_Theme $theme   Theme instance.
	 * @param string   $context Screen context.
	 *
	 * @return array
	 */
	public static function add_theme_actions( $actions, $theme, $context ) {
		if ( ! self::can_manage() || ! $theme instanceof WP_Theme ) {
			return $actions;
		}

		$slug          = $theme->get_stylesheet();
		$active        = wp_get_theme();
		$active_slug   = $active->get_stylesheet();
		$active_parent = $active->get_template();
		$is_inactive   = ( $slug !== $active_slug && $slug !== $active_parent );
		$suppressed    = self::get_suppressed_themes();
		$base_url      = admin_url( 'themes.php' );

		if ( $is_inactive ) {
			$actions['wpshadow-delete'] = sprintf(
				'<a href="%s" class="wpshadow-delete-theme">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'wpshadow_action' => 'delete_theme',
								'theme'           => rawurlencode( $slug ),
							),
							$base_url
						),
						'wpshadow_delete_theme'
					)
				),
				esc_html__( 'Delete', 'wpshadow' )
			);

			if ( in_array( $slug, $suppressed, true ) ) {
				$actions['wpshadow-allow-updates'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'wpshadow_action' => 'unsuppress_theme_update',
									'theme'           => rawurlencode( $slug ),
								),
								$base_url
							),
							'wpshadow_unsuppress_theme_update'
						)
					),
					esc_html__( 'Allow Updates', 'wpshadow' )
				);
			} else {
				$actions['wpshadow-hide-updates'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'wpshadow_action' => 'suppress_theme_update',
									'theme'           => rawurlencode( $slug ),
								),
								$base_url
							),
							'wpshadow_suppress_theme_update'
						)
					),
					esc_html__( 'Snooze Updates', 'wpshadow' )
				);
			}
		}

		return $actions;
	}

	/**
	 * Add action links to plugin rows for inactive plugins (hide/allow updates).
	 *
	 * @param array  $actions Existing actions.
	 * @param string $plugin_file Plugin basename.
	 * @param array  $plugin_data Plugin data array.
	 * @param string $context Screen context.
	 *
	 * @return array
	 */
	public static function add_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
		if ( ! self::can_manage() ) {
			return $actions;
		}

		self::ensure_plugin_functions();
		if ( is_plugin_active( $plugin_file ) ) {
			return $actions;
		}

		$suppressed = self::get_suppressed_plugins();
		$base_url   = admin_url( 'plugins.php' );

		if ( in_array( $plugin_file, $suppressed, true ) ) {
			$actions['wpshadow-allow-updates'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'wpshadow_action' => 'unsuppress_plugin_update',
								'plugin'          => rawurlencode( $plugin_file ),
							),
							$base_url
						),
						'wpshadow_unsuppress_plugin_update'
					)
				),
				esc_html__( 'Allow Updates', 'wpshadow' )
			);
		} else {
			$actions['wpshadow-hide-updates'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'wpshadow_action' => 'suppress_plugin_update',
								'plugin'          => rawurlencode( $plugin_file ),
							),
							$base_url
						),
						'wpshadow_suppress_plugin_update'
					)
				),
				esc_html__( 'Snooze Updates', 'wpshadow' )
			);
		}

		return $actions;
	}

	/**
	 * Remove suppressed themes from the update response and honor snooze.
	 *
	 * @param object|array|null $transient Theme update transient.
	 * @return object|array|null
	 */
	public static function filter_theme_updates( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		if ( self::is_snoozed() ) {
			$transient->response = array();
			return $transient;
		}

		$suppressed = self::get_suppressed_themes();
		if ( empty( $suppressed ) || empty( $transient->response ) || ! is_array( $transient->response ) ) {
			return $transient;
		}

		foreach ( $suppressed as $slug ) {
			if ( isset( $transient->response[ $slug ] ) ) {
				unset( $transient->response[ $slug ] );
			}
		}

		return $transient;
	}

	/**
	 * Remove suppressed plugins from the update response and honor snooze.
	 *
	 * @param object|array|null $transient Plugin update transient.
	 * @return object|array|null
	 */
	public static function filter_plugin_updates( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		if ( self::is_snoozed() ) {
			$transient->response = array();
			return $transient;
		}

		$suppressed = self::get_suppressed_plugins();
		if ( empty( $suppressed ) || empty( $transient->response ) || ! is_array( $transient->response ) ) {
			return $transient;
		}

		foreach ( $suppressed as $plugin_file ) {
			if ( isset( $transient->response[ $plugin_file ] ) ) {
				unset( $transient->response[ $plugin_file ] );
			}
		}

		return $transient;
	}

	/**
	 * Show notices for actions and snooze status.
	 */
	public static function maybe_render_notices(): void {
		if ( ! self::can_manage() ) {
			return;
		}

		$notice  = isset( $_GET['wpshadow_notice'] ) ? sanitize_key( wp_unslash( $_GET['wpshadow_notice'] ) ) : '';
		$message = '';

		switch ( $notice ) {
			case 'theme_deleted':
				$message = __( 'Theme deleted successfully.', 'wpshadow' );
				break;
			case 'theme_delete_failed':
				$message = __( 'Could not delete the selected theme.', 'wpshadow' );
				break;
			case 'theme_suppressed':
				$message = __( 'Update notices hidden for this theme.', 'wpshadow' );
				break;
			case 'theme_unsuppressed':
				$message = __( 'Theme updates will show normally.', 'wpshadow' );
				break;
			case 'plugin_suppressed':
				$message = __( 'Update notices hidden for this plugin.', 'wpshadow' );
				break;
			case 'plugin_unsuppressed':
				$message = __( 'Plugin updates will show normally.', 'wpshadow' );
				break;
			case 'updates_snoozed':
				$message = __( 'Update notifications snoozed until you open the Updates screen.', 'wpshadow' );
				break;
			case 'updates_unsnoozed':
				$message = __( 'Update notifications restored.', 'wpshadow' );
				break;
			default:
				break;
		}

		if ( ! empty( $message ) ) {
			printf(
				'<div class="notice notice-success"><p>%s</p></div>',
				esc_html( $message )
			);
		}

		self::render_snooze_toggle();
	}

	/**
	 * Snooze status reminder and toggle on plugin/theme screens.
	 */
	private static function render_snooze_toggle(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( empty( $screen ) ) {
			return;
		}

		if ( ! in_array( $screen->base, array( 'themes', 'plugins' ), true ) ) {
			return;
		}

		$base_url = 'themes' === $screen->base ? admin_url( 'themes.php' ) : admin_url( 'plugins.php' );

		if ( self::is_snoozed() ) {
			$updates_url = admin_url( 'update-core.php' );
			printf(
				'<div class="notice notice-info"><p>%s <a class="button button-secondary" href="%s">%s</a> <a class="button" href="%s">%s</a></p></div>',
				esc_html__( 'Update notifications are snoozed. They will return when you open the Updates screen.', 'wpshadow' ),
				esc_url( $updates_url ),
				esc_html__( 'Open Updates', 'wpshadow' ),
				esc_url( wp_nonce_url( add_query_arg( array( 'wpshadow_action' => 'unsnooze_updates' ), $base_url ), 'wpshadow_unsnooze_updates' ) ),
				esc_html__( 'Unsnooze now', 'wpshadow' )
			);
			return;
		}

		// Only show snooze banner if there are actual updates available
		if ( ! self::has_available_updates() ) {
			return;
		}

		printf(
			'<div class="notice notice-info"><p>%s <a class="button" href="%s">%s</a></p></div>',
			esc_html__( 'Want quiet time? Snooze update notifications until you intentionally check Updates.', 'wpshadow' ),
			esc_url( wp_nonce_url( add_query_arg( array( 'wpshadow_action' => 'snooze_updates' ), $base_url ), 'wpshadow_snooze_updates' ) ),
			esc_html__( 'Snooze updates', 'wpshadow' )
		);
	}

	/**
	 * Delete a theme if safe.
	 *
	 * @param string $redirect Redirect target.
	 */
	private static function handle_theme_delete( string $redirect ): void {
		$slug  = isset( $_GET['theme'] ) ? sanitize_text_field( wp_unslash( $_GET['theme'] ) ) : '';
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( empty( $slug ) || ! wp_verify_nonce( $nonce, 'wpshadow_delete_theme' ) ) {
			return;
		}

		$theme         = wp_get_theme( $slug );
		$active        = wp_get_theme();
		$active_slug   = $active->get_stylesheet();
		$active_parent = $active->get_template();

		if ( ! $theme->exists() || $slug === $active_slug || $slug === $active_parent ) {
			self::redirect_with_notice( 'theme_delete_failed', $redirect );
		}

		require_once ABSPATH . 'wp-admin/includes/theme.php';
		$result = delete_theme( $slug );

		if ( is_wp_error( $result ) || false === $result ) {
			self::redirect_with_notice( 'theme_delete_failed', $redirect );
		}

		self::redirect_with_notice( 'theme_deleted', $redirect );
	}

	/**
	 * Toggle suppression for a theme.
	 *
	 * @param bool   $suppress Whether to suppress.
	 * @param string $redirect Redirect target.
	 */
	private static function handle_theme_suppression( bool $suppress, string $redirect ): void {
		$slug         = isset( $_GET['theme'] ) ? sanitize_text_field( wp_unslash( $_GET['theme'] ) ) : '';
		$nonce_action = $suppress ? 'wpshadow_suppress_theme_update' : 'wpshadow_unsuppress_theme_update';
		$nonce        = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( empty( $slug ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return;
		}

		$active        = wp_get_theme();
		$active_slug   = $active->get_stylesheet();
		$active_parent = $active->get_template();
		if ( $slug === $active_slug || $slug === $active_parent ) {
			self::redirect_with_notice( 'theme_delete_failed', $redirect );
		}

		$suppressed = self::get_suppressed_themes();
		if ( $suppress ) {
			$suppressed[] = $slug;
			$suppressed   = array_values( array_unique( $suppressed ) );
			update_site_option( self::THEME_OPTION, $suppressed );
			self::redirect_with_notice( 'theme_suppressed', $redirect );
		}

		$suppressed = array_values( array_diff( $suppressed, array( $slug ) ) );
		update_site_option( self::THEME_OPTION, $suppressed );
		self::redirect_with_notice( 'theme_unsuppressed', $redirect );
	}

	/**
	 * Toggle suppression for a plugin.
	 *
	 * @param bool   $suppress Whether to suppress.
	 * @param string $redirect Redirect target.
	 */
	private static function handle_plugin_suppression( bool $suppress, string $redirect ): void {
		self::ensure_plugin_functions();
		$plugin       = isset( $_GET['plugin'] ) ? sanitize_text_field( wp_unslash( $_GET['plugin'] ) ) : '';
		$nonce_action = $suppress ? 'wpshadow_suppress_plugin_update' : 'wpshadow_unsuppress_plugin_update';
		$nonce        = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( empty( $plugin ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return;
		}

		if ( is_plugin_active( $plugin ) ) {
			self::redirect_with_notice( 'plugin_unsuppressed', $redirect );
		}

		$suppressed = self::get_suppressed_plugins();
		if ( $suppress ) {
			$suppressed[] = $plugin;
			$suppressed   = array_values( array_unique( $suppressed ) );
			update_site_option( self::PLUGIN_OPTION, $suppressed );
			self::redirect_with_notice( 'plugin_suppressed', $redirect );
		}

		$suppressed = array_values( array_diff( $suppressed, array( $plugin ) ) );
		update_site_option( self::PLUGIN_OPTION, $suppressed );
		self::redirect_with_notice( 'plugin_unsuppressed', $redirect );
	}

	/**
	 * Suppress updates for provided theme slugs.
	 *
	 * @param array $slugs Theme slugs.
	 * @return int Number suppressed.
	 */
	public static function suppress_themes( array $slugs ): int {
		$existing = self::get_suppressed_themes();
		$merged   = array_values( array_unique( array_merge( $existing, $slugs ) ) );
		update_site_option( self::THEME_OPTION, $merged );
		return count( $merged ) - count( $existing );
	}

	/**
	 * Remove all theme suppressions.
	 */
	public static function clear_theme_suppression(): void {
		delete_site_option( self::THEME_OPTION );
	}

	/**
	 * Suppress updates for provided plugins.
	 *
	 * @param array $plugins Plugin basenames.
	 * @return int Number suppressed.
	 */
	public static function suppress_plugins( array $plugins ): int {
		$existing = self::get_suppressed_plugins();
		$merged   = array_values( array_unique( array_merge( $existing, $plugins ) ) );
		update_site_option( self::PLUGIN_OPTION, $merged );
		return count( $merged ) - count( $existing );
	}

	/**
	 * Remove all plugin suppressions.
	 */
	public static function clear_plugin_suppression(): void {
		delete_site_option( self::PLUGIN_OPTION );
	}

	/**
	 * Snooze all updates.
	 */
	public static function snooze_updates(): void {
		update_site_option( self::SNOOZE_OPTION, 1 );
	}

	/**
	 * Clear snooze flag.
	 */
	public static function clear_snooze(): void {
		delete_site_option( self::SNOOZE_OPTION );
	}

	/**
	 * Determine if snooze is active.
	 *
	 * @return bool
	 */
	public static function is_snoozed(): bool {
		return (bool) get_site_option( self::SNOOZE_OPTION, false );
	}

	/**
	 * Get inactive theme slugs.
	 *
	 * @return array
	 */
	public static function get_inactive_theme_slugs(): array {
		$themes        = wp_get_themes();
		$active        = wp_get_theme();
		$active_slug   = $active->get_stylesheet();
		$active_parent = $active->get_template();

		$inactive = array();
		foreach ( $themes as $slug => $theme ) {
			if ( $slug === $active_slug || $slug === $active_parent ) {
				continue;
			}
			$inactive[] = $slug;
		}

		return $inactive;
	}

	/**
	 * Get inactive plugins.
	 *
	 * @return array Basenames.
	 */
	public static function get_inactive_plugins(): array {
		self::ensure_plugin_functions();
		$all_plugins    = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );
		return array_values( array_diff( $all_plugins, $active_plugins ) );
	}

	/**
	 * Get suppressed theme slugs.
	 *
	 * @return array
	 */
	public static function get_suppressed_themes(): array {
		$suppressed = get_site_option( self::THEME_OPTION, array() );
		return is_array( $suppressed ) ? array_values( array_filter( $suppressed ) ) : array();
	}

	/**
	 * Get suppressed plugin basenames.
	 *
	 * @return array
	 */
	public static function get_suppressed_plugins(): array {
		$suppressed = get_site_option( self::PLUGIN_OPTION, array() );
		return is_array( $suppressed ) ? array_values( array_filter( $suppressed ) ) : array();
	}

	/**
	 * Clear snooze state on Updates screen load.
	 */
	public static function clear_snooze_on_updates_page(): void {
		if ( self::is_snoozed() ) {
			self::clear_snooze();
			add_action(
				'admin_notices',
				function () {
					printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html__( 'Update notifications restored after visiting the Updates screen.', 'wpshadow' ) );
				}
			);
		}
	}

	/**
	 * Check if there are any available updates.
	 *
	 * @return bool
	 */
	private static function has_available_updates(): bool {
		$theme_updates  = get_site_transient( 'update_themes' );
		$plugin_updates = get_site_transient( 'update_plugins' );
		$core_updates   = get_site_transient( 'update_core' );

		// Check for theme updates
		$has_theme_updates = is_object( $theme_updates ) && ! empty( $theme_updates->response ) && is_array( $theme_updates->response ) && count( $theme_updates->response ) > 0;

		// Check for plugin updates
		$has_plugin_updates = is_object( $plugin_updates ) && ! empty( $plugin_updates->response ) && is_array( $plugin_updates->response ) && count( $plugin_updates->response ) > 0;

		// Check for core updates
		$has_core_updates = is_object( $core_updates ) && ! empty( $core_updates->updates ) && is_array( $core_updates->updates ) && count( $core_updates->updates ) > 0;

		return $has_theme_updates || $has_plugin_updates || $has_core_updates;
	}

	/**
	 * Ensure plugin helper functions are loaded.
	 */
	private static function ensure_plugin_functions(): void {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	/**
	 * Determine if the current user can manage updates.
	 *
	 * @return bool
	 */
	private static function can_manage(): bool {
		if ( is_multisite() && is_network_admin() ) {
			return current_user_can( 'manage_network_options' );
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Redirect with notice code.
	 *
	 * @param string $notice Notice key.
	 * @param string $redirect Redirect URL.
	 */
	private static function redirect_with_notice( string $notice, string $redirect ): void {
		$url = remove_query_arg( array( 'wpshadow_action', '_wpnonce' ), $redirect );
		$url = add_query_arg( 'wpshadow_notice', rawurlencode( $notice ), $url );
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Preferred redirect target.
	 *
	 * @return string
	 */
	private static function get_referer_or_default(): string {
		$referer = wp_get_referer();
		if ( $referer ) {
			return $referer;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'plugins' === $screen->base ) {
			return admin_url( 'plugins.php' );
		}

		return admin_url( 'themes.php' );
	}
}
