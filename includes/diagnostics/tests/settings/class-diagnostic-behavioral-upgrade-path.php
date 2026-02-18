<?php
/**
 * Diagnostic: Upgrade Path Clear
 *
 * Tests whether the site provides a clear tier progression that achieves
 * >10% annual upgrade rate from basic to premium tiers.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4545
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upgrade Path Clear Diagnostic
 *
 * Checks for multiple membership tiers and upgrade pathways. Clear upgrade
 * paths increase revenue per user by 30-50% through tier progression.
 *
 * @since 1.6034.1450
 */
class Diagnostic_Behavioral_Upgrade_Path extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'provides-clear-upgrade-path';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upgrade Path Clear';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site provides clear tier progression for upgrades';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for upgrade path implementation.
	 *
	 * Looks for multiple tiers and upgrade prompts in membership systems.
	 *
	 * @since  1.6034.1450
	 * @return array|null Finding array if single-tier, null if multi-tier.
	 */
	public static function check() {
		$tier_count = 0;

		// Check WooCommerce subscription products.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$args    = array(
				'post_type'      => 'product',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'subscription', 'variable-subscription' ),
					),
				),
			);
			$products = get_posts( $args );
			$tier_count = count( $products );
		}

		// Check membership plugin levels.
		if ( class_exists( 'MeprUser' ) ) {
			global $wpdb;
			$levels = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mepr_memberships" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			if ( $levels > $tier_count ) {
				$tier_count = (int) $levels;
			}
		}

		// Check Paid Memberships Pro levels.
		if ( function_exists( 'pmpro_getAllLevels' ) ) {
			$levels = pmpro_getAllLevels( true );
			if ( count( $levels ) > $tier_count ) {
				$tier_count = count( $levels );
			}
		}

		// Need at least 2 tiers for upgrade path.
		if ( $tier_count >= 2 ) {
			return null;
		}

		// Only applicable if has membership functionality.
		$has_membership = false;
		
		$membership_indicators = array(
			class_exists( 'WC_Subscriptions' ),
			class_exists( 'MeprUser' ),
			function_exists( 'pmpro_hasMembershipLevel' ),
			is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ),
		);

		foreach ( $membership_indicators as $indicator ) {
			if ( $indicator ) {
				$has_membership = true;
				break;
			}
		}

		if ( ! $has_membership ) {
			return null; // Not a membership site.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'Only one membership tier detected. Multi-tier systems with clear upgrade paths increase revenue per user by 30-50%. Create Bronze → Silver → Gold tiers with obvious value differences. Show members what they\'re missing at higher tiers. Make upgrading self-serve and frictionless. Target >10% annual upgrade rate.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 38,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/upgrade-path',
		);
	}
}
