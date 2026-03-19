<?php
/**
 * Paid Advertising Strategy Diagnostic
 *
 * Checks whether paid advertising and conversion tracking are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Advertising Strategy Diagnostic Class
 *
 * Verifies paid ad tracking and ROI measurement capabilities.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Paid_Advertising_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'paid-advertising-strategy';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Paid Advertising Strategy or Tracking';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether paid ads and conversion tracking are configured';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'paid-acquisition';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for ad tracking tools (45 points).
		$tracking_plugins = array(
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'google-tag-manager-for-wordpress/google-tag-manager-for-wordpress.php' => 'GTM4WP',
			'facebook-for-woocommerce/facebook-for-woocommerce.php' => 'Facebook for WooCommerce',
			'pixel-caffeine/pixel-caffeine.php'                  => 'Pixel Caffeine',
			'official-facebook-pixel/facebook-pixel.php'         => 'Official Facebook Pixel',
		);

		$active_tracking = array();
		foreach ( $tracking_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_tracking[] = $plugin_name;
				$earned_points    += 18;
			}
		}

		if ( count( $active_tracking ) > 0 ) {
			$stats['tracking_tools'] = implode( ', ', $active_tracking );
		} else {
			$issues[] = __( 'No ad tracking or tag manager tools detected', 'wpshadow' );
		}

		// Check for analytics platforms (30 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 10;
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['analytics_tools'] = implode( ', ', $active_analytics );
		} else {
			$warnings[] = __( 'No analytics platforms detected for paid ROI tracking', 'wpshadow' );
		}

		// Check for A/B testing tools (25 points).
		$testing_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php' => 'Nelio A/B Testing',
			'google-site-kit/google-site-kit.php'  => 'Google Site Kit (Optimize)',
			'optinmonster/optin-monster-wp-api.php' => 'OptinMonster',
		);

		$active_testing = array();
		foreach ( $testing_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_testing[] = $plugin_name;
				$earned_points  += 8;
			}
		}

		if ( count( $active_testing ) > 0 ) {
			$stats['testing_tools'] = implode( ', ', $active_testing );
		} else {
			$warnings[] = __( 'No A/B testing tools detected for ad optimization', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your paid advertising setup scored %s. Paid growth can scale fast, but only when tracking is in place. Without conversion tracking, ad spend becomes guesswork and can waste budget quickly.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/paid-advertising-strategy',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
