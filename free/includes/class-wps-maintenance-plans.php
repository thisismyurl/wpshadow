<?php
/**
 * Maintenance Plans Manager
 *
 * Manages tiered maintenance subscription plans (Free, Pro, Enterprise).
 * MVP implementation - payment integration deferred.
 *
 * @package WPShadow
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPSHADOW_Maintenance_Plans
 *
 * Manages maintenance plan tiers and feature access control.
 */
class WPSHADOW_Maintenance_Plans {

	/**
	 * Plan tier constants
	 */
	const TIER_FREE       = 'free';
	const TIER_PRO        = 'pro';
	const TIER_ENTERPRISE = 'enterprise';

	/**
	 * Plan tier definitions
	 *
	 * @var array
	 */
	private static array $plan_tiers = array(
		self::TIER_FREE       => array(
			'name'        => 'Free',
			'price'       => 0,
			'price_year'  => 0,
			'icon'        => '🆓',
			'description' => 'Essential maintenance tools for WordPress sites',
			'features'    => array(
				'weekly_health_reports',
				'performance_monitoring',
				'auto_fixes_common',
				'error_log_viewer',
				'support_request_form',
			),
		),
		self::TIER_PRO        => array(
			'name'        => 'Pro',
			'price'       => 99,
			'price_year'  => 990,
			'icon'        => '💰',
			'description' => 'Advanced features with priority support',
			'features'    => array(
				'weekly_health_reports',
				'performance_monitoring',
				'auto_fixes_common',
				'error_log_viewer',
				'support_request_form',
				'priority_support_4h',
				'monthly_optimization_audit',
				'automated_db_cleanup_weekly',
				'quick_fixes_6_month',
				'site_comparison_benchmark',
				'email_alerts_critical',
			),
		),
		self::TIER_ENTERPRISE => array(
			'name'        => 'Enterprise',
			'price'       => 299,
			'price_year'  => 2990,
			'icon'        => '💎',
			'description' => 'Complete solution with 24/7 support',
			'features'    => array(
				'weekly_health_reports',
				'performance_monitoring',
				'auto_fixes_common',
				'error_log_viewer',
				'support_request_form',
				'priority_support_4h',
				'monthly_optimization_audit',
				'automated_db_cleanup_weekly',
				'quick_fixes_6_month',
				'site_comparison_benchmark',
				'email_alerts_critical',
				'direct_phone_email_support',
				'support_24_7',
				'weekly_optimization_review',
				'unlimited_quick_fixes',
				'custom_recommendation_engine',
				'integration_consulting',
				'emergency_sos_2h',
				'quarterly_strategy_consultation',
			),
		),
	);

	/**
	 * Feature definitions with human-readable names
	 *
	 * @var array
	 */
	private static array $feature_labels = array(
		'weekly_health_reports'           => 'Weekly health reports',
		'performance_monitoring'          => 'Performance monitoring',
		'auto_fixes_common'               => 'Auto-fixes for common issues',
		'error_log_viewer'                => 'Error log viewer',
		'support_request_form'            => 'Support request form',
		'priority_support_4h'             => 'Priority support (4-hour response)',
		'monthly_optimization_audit'      => 'Monthly optimization audit',
		'automated_db_cleanup_weekly'     => 'Automated database cleanup (weekly)',
		'quick_fixes_6_month'             => 'Quick fixes (6 per month)',
		'site_comparison_benchmark'       => 'Site comparison benchmarking',
		'email_alerts_critical'           => 'Email alerts for critical issues',
		'direct_phone_email_support'      => 'Direct phone/email support',
		'support_24_7'                    => '24/7 support available',
		'weekly_optimization_review'      => 'Weekly optimization review',
		'unlimited_quick_fixes'           => 'Unlimited quick fixes',
		'custom_recommendation_engine'    => 'Custom recommendation engine',
		'integration_consulting'          => 'Integration consulting',
		'emergency_sos_2h'                => 'Emergency SOS (2-hour response)',
		'quarterly_strategy_consultation' => 'Quarterly strategy consultation',
	);

	/**
	 * Get current site's maintenance plan tier
	 *
	 * @return string Plan tier (free, pro, or enterprise)
	 */
	public static function get_current_tier(): string {
		return get_option( 'wpshadow_maintenance_plan_tier', self::TIER_FREE );
	}

	/**
	 * Set current site's maintenance plan tier
	 *
	 * @param string $tier Plan tier to set.
	 * @return bool Success status
	 */
	public static function set_current_tier( string $tier ): bool {
		if ( ! in_array( $tier, array( self::TIER_FREE, self::TIER_PRO, self::TIER_ENTERPRISE ), true ) ) {
			return false;
		}

		return update_option( 'wpshadow_maintenance_plan_tier', $tier );
	}

	/**
	 * Get all plan tier definitions
	 *
	 * @return array Plan tier definitions
	 */
	public static function get_all_tiers(): array {
		return self::$plan_tiers;
	}

	/**
	 * Get specific plan tier definition
	 *
	 * @param string $tier Plan tier to retrieve.
	 * @return array|null Plan tier definition or null if not found
	 */
	public static function get_tier_definition( string $tier ): ?array {
		return self::$plan_tiers[ $tier ] ?? null;
	}

	/**
	 * Check if current plan has access to a feature
	 *
	 * @param string $feature Feature identifier.
	 * @return bool Whether current plan has access
	 */
	public static function has_feature( string $feature ): bool {
		$current_tier = self::get_current_tier();
		$tier_def     = self::get_tier_definition( $current_tier );

		if ( ! $tier_def ) {
			return false;
		}

		return in_array( $feature, $tier_def['features'], true );
	}

	/**
	 * Get human-readable feature label
	 *
	 * @param string $feature Feature identifier.
	 * @return string Feature label
	 */
	public static function get_feature_label( string $feature ): string {
		return self::$feature_labels[ $feature ] ?? ucwords( str_replace( '_', ' ', $feature ) );
	}

	/**
	 * Get all feature labels
	 *
	 * @return array Feature labels
	 */
	public static function get_feature_labels(): array {
		return self::$feature_labels;
	}

	/**
	 * Get plan enrollment information
	 *
	 * @return array Enrollment data
	 */
	public static function get_enrollment_info(): array {
		return array(
			'tier'         => self::get_current_tier(),
			'enrolled_at'  => get_option( 'wpshadow_maintenance_plan_enrolled_at', '' ),
			'status'       => get_option( 'wpshadow_maintenance_plan_status', 'active' ),
			'billing_type' => get_option( 'wpshadow_maintenance_plan_billing_type', 'monthly' ),
		);
	}

	/**
	 * Update plan enrollment information
	 *
	 * @param string $tier         Plan tier.
	 * @param string $billing_type Billing type (monthly or yearly).
	 * @return bool Success status
	 */
	public static function enroll_plan( string $tier, string $billing_type = 'monthly' ): bool {
		if ( ! in_array( $tier, array( self::TIER_FREE, self::TIER_PRO, self::TIER_ENTERPRISE ), true ) ) {
			return false;
		}

		if ( ! in_array( $billing_type, array( 'monthly', 'yearly' ), true ) ) {
			return false;
		}

		update_option( 'wpshadow_maintenance_plan_tier', $tier );
		update_option( 'wpshadow_maintenance_plan_billing_type', $billing_type );
		update_option( 'wpshadow_maintenance_plan_status', 'active' );

		// Set enrolled_at if not already set.
		if ( ! get_option( 'wpshadow_maintenance_plan_enrolled_at' ) ) {
			update_option( 'wpshadow_maintenance_plan_enrolled_at', current_time( 'mysql' ) );
		}

		return true;
	}

	/**
	 * Get plan comparison table data
	 *
	 * @return array Comparison table data
	 */
	public static function get_comparison_table(): array {
		$all_features = array();

		// Collect all unique features across all tiers.
		foreach ( self::$plan_tiers as $tier_data ) {
			$all_features = array_merge( $all_features, $tier_data['features'] );
		}
		$all_features = array_unique( $all_features );

		$comparison = array();
		foreach ( $all_features as $feature ) {
			$comparison[ $feature ] = array(
				'label'      => self::get_feature_label( $feature ),
				'free'       => in_array( $feature, self::$plan_tiers[ self::TIER_FREE ]['features'], true ),
				'pro'        => in_array( $feature, self::$plan_tiers[ self::TIER_PRO ]['features'], true ),
				'enterprise' => in_array( $feature, self::$plan_tiers[ self::TIER_ENTERPRISE ]['features'], true ),
			);
		}

		return $comparison;
	}
}
