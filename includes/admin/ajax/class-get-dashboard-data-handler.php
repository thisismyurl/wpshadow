<?php
/**
 * Get Dashboard Data AJAX Handler
 *
 * Returns live dashboard state, diagnostic applicability, and recommendation actions.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Error_Handler;
use WPShadow\Core\Options_Manager;

/**
 * AJAX Handler: Get updated dashboard data
 *
 * Action: wp_ajax_wpshadow_get_dashboard_data
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 *
 * Returns: Updated gauges and findings data for real-time refresh
 *
 * @package WPShadow
 */
class Get_Dashboard_Data_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_dashboard_data', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_wpshadow_get_diagnostic_applicability', array( __CLASS__, 'handle_diagnostic_applicability' ) );
		add_action( 'wp_ajax_wpshadow_apply_diagnostic_recommendations', array( __CLASS__, 'handle_apply_diagnostic_recommendations' ) );
		add_action( 'wp_ajax_wpshadow_reset_diagnostic_relevance_prompt', array( __CLASS__, 'handle_reset_diagnostic_relevance_prompt' ) );
	}

	/**
	 * Handle dashboard data request
	 */
	public static function handle(): void {
		try {
			// Verify security.
			self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

			if ( ! function_exists( 'wpshadow_get_gauge_test_counts' ) ) {
				$gauge_module_path = WPSHADOW_PATH . 'includes/ui/dashboard/gauges-module.php';
				if ( file_exists( $gauge_module_path ) ) {
					require_once $gauge_module_path;
				}
			}

			$category_meta = \wpshadow_get_category_metadata();
			$last_scan     = (int) get_option( 'wpshadow_last_quick_checks', 0 );
			$never_run     = empty( $last_scan );

			$findings = function_exists( 'wpshadow_get_cached_findings' )
				? \wpshadow_get_cached_findings()
				: array();
			if ( empty( $findings ) ) {
				$findings = \wpshadow_get_site_findings();
			}

			$findings = self::filter_dashboard_findings_for_enabled_diagnostics( $findings );

			$dismissed = Options_Manager::get_array( 'wpshadow_dismissed_findings', array() );
			$findings  = array_filter(
				$findings,
				function ( $f ) use ( $dismissed ) {
					return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
				}
			);

			$gauge_data = array(
				'findings_count' => count( $findings ),
				'last_scan'      => $last_scan,
				'never_run'      => $never_run,
				'test_counts'    => function_exists( 'wpshadow_get_gauge_test_counts' )
					? \wpshadow_get_gauge_test_counts( $category_meta, $never_run )
					: array(),
			);

			self::send_success( $gauge_data );
		} catch ( \Throwable $e ) {
			Error_Handler::log_error( 'Dashboard data retrieval failed', $e );
			self::send_error( array( 'message' => __( 'Failed to retrieve dashboard data', 'wpshadow' ) ) );
		}
	}

	/**
	 * Remove findings produced by diagnostics that are currently disabled.
	 *
	 * Dashboard summaries and gauges should reflect only enabled diagnostics,
	 * while the Diagnostic Status table still lists disabled diagnostics separately.
	 *
	 * @since  0.6091
	 * @param  array $findings Raw findings array.
	 * @return array Filtered findings array.
	 */
	private static function filter_dashboard_findings_for_enabled_diagnostics( array $findings ): array {
		if ( empty( $findings ) ) {
			return array();
		}

		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled = is_array( $disabled ) ? array_values( array_unique( array_map( 'strval', $disabled ) ) ) : array();

		if ( empty( $disabled ) ) {
			return array_values( $findings );
		}

		$disabled_lookup = array();
		foreach ( $disabled as $class_name ) {
			$normalized = ltrim( (string) $class_name, '\\' );
			if ( '' === $normalized ) {
				continue;
			}

			$qualified = 0 === strpos( $normalized, 'WPShadow\\Diagnostics\\' )
				? $normalized
				: 'WPShadow\\Diagnostics\\' . $normalized;

			$disabled_lookup[ $qualified ] = true;
			$disabled_lookup[ str_replace( 'WPShadow\\Diagnostics\\', '', $qualified ) ] = true;
		}

		$disabled_finding_ids = array();
		$states               = function_exists( 'wpshadow_get_diagnostic_test_states' ) ? wpshadow_get_diagnostic_test_states() : array();
		if ( is_array( $states ) ) {
			foreach ( $states as $class_name => $state ) {
				if ( ! is_string( $class_name ) || ! is_array( $state ) ) {
					continue;
				}

				$normalized = ltrim( $class_name, '\\' );
				$qualified  = 0 === strpos( $normalized, 'WPShadow\\Diagnostics\\' )
					? $normalized
					: 'WPShadow\\Diagnostics\\' . $normalized;

				if ( ! isset( $disabled_lookup[ $qualified ] ) && ! isset( $disabled_lookup[ str_replace( 'WPShadow\\Diagnostics\\', '', $qualified ) ] ) ) {
					continue;
				}

				$finding_id = isset( $state['finding_id'] ) ? sanitize_key( (string) $state['finding_id'] ) : '';
				if ( '' !== $finding_id ) {
					$disabled_finding_ids[ $finding_id ] = true;
				}
			}
		}

		$map = class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' )
			? \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map()
			: array();

		if ( is_array( $map ) ) {
			foreach ( $map as $class_name => $diagnostic_data ) {
				$normalized = ltrim( (string) $class_name, '\\' );
				if ( '' === $normalized ) {
					continue;
				}

				$qualified = 0 === strpos( $normalized, 'WPShadow\\Diagnostics\\' )
					? $normalized
					: 'WPShadow\\Diagnostics\\' . $normalized;

				if ( ! isset( $disabled_lookup[ $qualified ] ) && ! isset( $disabled_lookup[ str_replace( 'WPShadow\\Diagnostics\\', '', $qualified ) ] ) ) {
					continue;
				}

				$file = isset( $diagnostic_data['file'] ) ? (string) $diagnostic_data['file'] : '';
				if ( ! class_exists( $qualified ) && '' !== $file && file_exists( $file ) ) {
					require_once $file;
				}

				if ( class_exists( $qualified ) && method_exists( $qualified, 'get_slug' ) ) {
					$slug = sanitize_key( (string) call_user_func( array( $qualified, 'get_slug' ) ) );
					if ( '' !== $slug ) {
						$disabled_finding_ids[ $slug ] = true;
					}
				}
			}
		}

		if ( empty( $disabled_finding_ids ) ) {
			return array_values( $findings );
		}

		$filtered = array_filter(
			$findings,
			static function ( $finding ) use ( $disabled_finding_ids ) {
				if ( ! is_array( $finding ) ) {
					return false;
				}

				$finding_id = isset( $finding['id'] ) ? sanitize_key( (string) $finding['id'] ) : '';
				if ( '' === $finding_id ) {
					return true;
				}

				return ! isset( $disabled_finding_ids[ $finding_id ] );
			}
		);

		return array_values( $filtered );
	}

	/**
	 * Return diagnostics that may be irrelevant for this site.
	 *
	 * @since 0.6095
	 * @return void Sends JSON response and dies.
	 */
	public static function handle_diagnostic_applicability(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

		if ( self::is_diagnostic_relevance_prompt_seen() ) {
			self::send_success(
				array(
					'groups'      => array(),
					'hash'        => '',
					'prompt_seen' => true,
				)
			);
		}

		$groups    = self::get_diagnostic_recommendation_groups();
		$hash_seed = wp_json_encode( $groups );
		self::mark_diagnostic_relevance_prompt_seen();

		self::send_success(
			array(
				'groups'      => $groups,
				'hash'        => md5( (string) $hash_seed ),
				'prompt_seen' => false,
			)
		);
	}

	/**
	 * Reset one-time relevance prompt and return fresh grouped recommendations.
	 *
	 * @since 0.6095
	 * @return void Sends JSON response and dies.
	 */
	public static function handle_reset_diagnostic_relevance_prompt(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			delete_user_meta( $user_id, 'wpshadow_diag_relevance_prompt_seen' );
		}

		$groups    = self::get_diagnostic_recommendation_groups();
		$hash_seed = wp_json_encode( $groups );

		self::send_success(
			array(
				'groups'      => $groups,
				'hash'        => md5( (string) $hash_seed ),
				'prompt_seen' => false,
			)
		);
	}

	/**
	 * Check if the diagnostic relevance prompt was already shown to this user.
	 *
	 * @since 0.6095
	 * @return bool True when the prompt has already been shown.
	 */
	private static function is_diagnostic_relevance_prompt_seen(): bool {
		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return true;
		}

		return ! empty( get_user_meta( $user_id, 'wpshadow_diag_relevance_prompt_seen', true ) );
	}

	/**
	 * Mark the diagnostic relevance prompt as shown for the current user.
	 *
	 * @since 0.6095
	 * @return void
	 */
	private static function mark_diagnostic_relevance_prompt_seen(): void {
		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return;
		}

		update_user_meta( $user_id, 'wpshadow_diag_relevance_prompt_seen', 1 );
	}

	/**
	 * Apply selected recommendations by disabling diagnostics.
	 *
	 * @since 0.6095
	 * @return void Sends JSON response and dies.
	 */
	public static function handle_apply_diagnostic_recommendations(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

		$apply_mode = self::get_post_param( 'apply_mode', 'key', 'disable' );
		if ( 'enable' !== $apply_mode && 'disable' !== $apply_mode ) {
			$apply_mode = 'disable';
		}

		$class_names = self::get_post_array_param( 'class_names', 'text', array() );
		$group_ids   = self::get_post_array_param( 'group_ids', 'key', array() );

		if ( ! empty( $group_ids ) ) {
			$groups    = self::get_diagnostic_recommendation_groups();
			$group_map = array();
			foreach ( $groups as $group ) {
				$group_id = isset( $group['id'] ) ? sanitize_key( (string) $group['id'] ) : '';
				if ( '' === $group_id ) {
					continue;
				}

				$group_map[ $group_id ] = isset( $group['class_names'] ) && is_array( $group['class_names'] )
					? $group['class_names']
					: array();
			}

			foreach ( $group_ids as $group_id ) {
				$normalized_group = sanitize_key( (string) $group_id );
				if ( isset( $group_map[ $normalized_group ] ) ) {
					$class_names = array_merge( $class_names, $group_map[ $normalized_group ] );
				}
			}
		}

		$map = class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' )
			? \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map()
			: array();

		$allowed_classes = array();
		foreach ( $map as $class_name => $diagnostic_data ) {
			$qualified         = 0 === strpos( (string) $class_name, 'WPShadow\\Diagnostics\\' )
				? (string) $class_name
				: 'WPShadow\\Diagnostics\\' . (string) $class_name;
			$allowed_classes[] = $qualified;
		}

		$selected = array();
		foreach ( $class_names as $class_name ) {
			$normalized = sanitize_text_field( (string) $class_name );
			if ( in_array( $normalized, $allowed_classes, true ) ) {
				$selected[] = $normalized;
			}
		}

		$selected = array_values( array_unique( $selected ) );
		if ( empty( $selected ) ) {
			self::send_success(
				array(
					'applied_count' => 0,
					'apply_mode'    => $apply_mode,
				)
			);
		}

		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();

		if ( 'disable' === $apply_mode ) {
			foreach ( $selected as $class_name ) {
				if ( ! in_array( $class_name, $disabled, true ) ) {
					$disabled[] = $class_name;
				}
			}
		} else {
			$disabled = array_values( array_diff( $disabled, $selected ) );
		}

		update_option( 'wpshadow_disabled_diagnostic_classes', array_values( array_unique( $disabled ) ) );

		self::send_success(
			array(
				'applied_count' => count( $selected ),
				'apply_mode'    => $apply_mode,
			)
		);
	}

	/**
	 * Build grouped recommendations for diagnostics likely irrelevant to this site.
	 *
	 * @since 0.6095
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_diagnostic_recommendation_groups(): array {
		$map = class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' )
			? \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map()
			: array();

		if ( empty( $map ) || ! is_array( $map ) ) {
			return array();
		}

		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();

		$signals      = self::get_site_feature_signals();
		$group_config = self::get_recommendation_group_catalog( $signals );
		$group_hits   = array();

		foreach ( $group_config as $group_id => $config ) {
			$group_hits[ $group_id ] = array(
				'id'             => $group_id,
				'label'          => (string) ( $config['label'] ?? $group_id ),
				'reason'         => (string) ( $config['reason'] ?? '' ),
				'enabled_count'  => 0,
				'disabled_count' => 0,
				'class_names'    => array(),
			);
		}

		foreach ( $map as $class_name => $diagnostic_data ) {
			$qualified = 0 === strpos( (string) $class_name, 'WPShadow\\Diagnostics\\' )
				? (string) $class_name
				: 'WPShadow\\Diagnostics\\' . (string) $class_name;

			$file = isset( $diagnostic_data['file'] ) ? (string) $diagnostic_data['file'] : '';
			if ( ! class_exists( $qualified ) && '' !== $file && file_exists( $file ) ) {
				require_once $file;
			}

			$title       = class_exists( $qualified ) && method_exists( $qualified, 'get_title' ) ? (string) $qualified::get_title() : '';
			$description = class_exists( $qualified ) && method_exists( $qualified, 'get_description' ) ? (string) $qualified::get_description() : '';
			$slug        = class_exists( $qualified ) && method_exists( $qualified, 'get_slug' ) ? (string) $qualified::get_slug() : '';
			$family      = class_exists( $qualified ) && method_exists( $qualified, 'get_family' ) ? (string) $qualified::get_family() : '';

			$text = strtolower( implode( ' ', array( $qualified, $title, $description, $slug, $family ) ) );

			foreach ( $group_config as $group_id => $config ) {
				$keywords = isset( $config['keywords'] ) && is_array( $config['keywords'] ) ? $config['keywords'] : array();
				if ( self::text_matches_keywords( $text, $keywords ) ) {
					$group_hits[ $group_id ]['class_names'][] = $qualified;
					if ( in_array( $qualified, $disabled, true ) ) {
						++$group_hits[ $group_id ]['disabled_count'];
					} else {
						++$group_hits[ $group_id ]['enabled_count'];
					}
					break;
				}
			}
		}

		$groups = array();
		foreach ( $group_hits as $group_id => $group ) {
			$class_names = array_values( array_unique( array_map( 'strval', $group['class_names'] ) ) );
			if ( empty( $class_names ) ) {
				continue;
			}

			sort( $class_names );
			$groups[] = array(
				'id'             => $group_id,
				'label'          => (string) $group['label'],
				'reason'         => (string) $group['reason'],
				'count'          => count( $class_names ),
				'enabled_count'  => (int) ( $group['enabled_count'] ?? 0 ),
				'disabled_count' => (int) ( $group['disabled_count'] ?? 0 ),
				'class_names'    => $class_names,
			);
		}

		usort(
			$groups,
			static function ( array $a, array $b ): int {
				return (int) ( $b['count'] ?? 0 ) <=> (int) ( $a['count'] ?? 0 );
			}
		);

		return array_slice( $groups, 0, 12 );
	}

	/**
	 * Get site-level feature signals used for recommendation matching.
	 *
	 * @since 0.6095
	 * @return array<string, bool>
	 */
	private static function get_site_feature_signals(): array {
		$active_plugins = get_option( 'active_plugins', array() );
		$active_plugins = is_array( $active_plugins ) ? $active_plugins : array();

		$is_plugin_active = static function ( string $plugin_file ) use ( $active_plugins ): bool {
			return in_array( $plugin_file, $active_plugins, true );
		};

		return array(
			'woocommerce'  => $is_plugin_active( 'woocommerce/woocommerce.php' ),
			'ecommerce'    => $is_plugin_active( 'woocommerce/woocommerce.php' ),
			'lms'          => $is_plugin_active( 'sfwd-lms/sfwd_lms.php' )
				|| $is_plugin_active( 'lifterlms/lifterlms.php' )
				|| $is_plugin_active( 'sensei-lms/sensei-lms.php' )
				|| $is_plugin_active( 'tutor/tutor.php' )
				|| $is_plugin_active( 'learnpress/learnpress.php' ),
			'membership'   => $is_plugin_active( 'memberpress/memberpress.php' )
				|| $is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' )
				|| $is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' )
				|| $is_plugin_active( 's2member/s2member.php' ),
			'booking'      => $is_plugin_active( 'woocommerce-bookings/woocommerce-bookings.php' )
				|| $is_plugin_active( 'bookly-responsive-appointment-booking-tool/main.php' )
				|| $is_plugin_active( 'ameliabooking/ameliabooking.php' )
				|| $is_plugin_active( 'wp-simple-booking-calendar/wp-simple-booking-calendar.php' ),
			'multilingual' => $is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' )
				|| $is_plugin_active( 'polylang/polylang.php' )
				|| $is_plugin_active( 'translatepress-multilingual/index.php' ),
		);
	}

	/**
	 * Build recommendation group catalog for currently-missing site features.
	 *
	 * @since 0.6095
	 * @param  array<string, bool> $signals Site feature signals.
	 * @return array<string, array<string, mixed>>
	 */
	private static function get_recommendation_group_catalog( array $signals ): array {
		$groups = array();

		if ( empty( $signals['woocommerce'] ) ) {
			$groups['woocommerce'] = array(
				'label'    => __( 'WooCommerce Diagnostics', 'wpshadow' ),
				'reason'   => __( 'WooCommerce does not appear to be active, so these checks may not add value for this site.', 'wpshadow' ),
				'keywords' => array( 'woocommerce', 'woo commerce', 'wc ' ),
			);
		}

		if ( empty( $signals['ecommerce'] ) ) {
			$groups['ecommerce'] = array(
				'label'    => __( 'eCommerce Diagnostics', 'wpshadow' ),
				'reason'   => __( 'No clear online-store signals were found, so these eCommerce checks may not be useful right now.', 'wpshadow' ),
				'keywords' => array( 'ecommerce', 'e-commerce', 'online store', 'shopping cart', 'checkout', 'product catalog', 'product page', 'cart' ),
			);
		}

		if ( empty( $signals['lms'] ) ) {
			$groups['lms'] = array(
				'label'    => __( 'LMS Diagnostics', 'wpshadow' ),
				'reason'   => __( 'No learning platform was detected, so these course-related checks may not apply to this site.', 'wpshadow' ),
				'keywords' => array( 'lms', 'learning management', 'course', 'lesson', 'student enrollment', 'learnpress', 'learndash', 'lifterlms', 'sensei', 'tutor lms' ),
			);
		}

		if ( empty( $signals['membership'] ) ) {
			$groups['membership'] = array(
				'label'    => __( 'Membership Diagnostics', 'wpshadow' ),
				'reason'   => __( 'No membership system was detected, so these member-access checks may not be useful here.', 'wpshadow' ),
				'keywords' => array( 'membership', 'memberpress', 'paid memberships pro', 'restrict content', 'subscription access', 'member login' ),
			);
		}

		if ( empty( $signals['booking'] ) ) {
			$groups['booking'] = array(
				'label'    => __( 'Booking Diagnostics', 'wpshadow' ),
				'reason'   => __( 'No booking system was detected, so appointment or booking checks may not be relevant for this site.', 'wpshadow' ),
				'keywords' => array( 'booking', 'appointment', 'reservation', 'bookly', 'amelia', 'schedule slot' ),
			);
		}

		if ( empty( $signals['multilingual'] ) ) {
			$groups['multilingual'] = array(
				'label'    => __( 'Multilingual Diagnostics', 'wpshadow' ),
				'reason'   => __( 'No multilingual plugin was detected, so translation-related checks may not be needed.', 'wpshadow' ),
				'keywords' => array( 'multilingual', 'translation', 'locale switcher', 'polylang', 'wpml', 'translatepress', 'language switcher' ),
			);
		}

		return $groups;
	}

	/**
	 * Check if text contains any keyword.
	 *
	 * @since 0.6095
	 * @param  string             $haystack Searchable text.
	 * @param  array<int, string> $keywords Keywords.
	 * @return bool
	 */
	private static function text_matches_keywords( string $haystack, array $keywords ): bool {
		foreach ( $keywords as $keyword ) {
			if ( '' !== $keyword && false !== strpos( $haystack, strtolower( $keyword ) ) ) {
				return true;
			}
		}

		return false;
	}
}
