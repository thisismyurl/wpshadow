<?php
/**
 * Pricing A/B Testing Diagnostic
 *
 * Checks whether pricing pages have A/B testing configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\PricingOptimization
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pricing A/B Testing Diagnostic Class
 *
 * Verifies that A/B testing tools are present for pricing pages.
 *
 * @since 0.6093.1200
 */
class Diagnostic_AB_Testing_Pricing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'ab-testing-pricing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No A/B Testing on Pricing Pages';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pricing pages use A/B testing tools';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'pricing-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$ab_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php' => 'Nelio A/B Testing',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php' => 'Site Kit',
			'vwo/vwo.php' => 'VWO',
		);

		$active_ab = array();
		foreach ( $ab_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ab[] = $plugin_name;
			}
		}

		$stats['ab_tools'] = ! empty( $active_ab ) ? implode( ', ', $active_ab ) : 'none';

		if ( empty( $active_ab ) ) {
			$issues[] = __( 'No A/B testing tools detected for pricing experiments', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Testing pricing layouts helps you learn what works best for customers. Small improvements can make a big difference over time.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ab-testing-pricing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
