<?php
/**
 * Conversion Tracking Implementation Diagnostic
 *
 * Checks if conversion tracking is properly configured.
 *
 * @package WPShadow\Diagnostics
 * @since   1.6032.0146
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Conversion Tracking Implementation
 *
 * Detects whether conversion tracking is set up for business metrics.
 */
class Diagnostic_Conversion_Tracking_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conversion-tracking-implementation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Tracking Implementation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies conversion tracking is configured';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'google-analytics-for-wordpress/google-analytics-for-wordpress.php' => 'MonsterInsights',
			'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php' => 'WC Google Analytics',
			'facebook-for-woocommerce/facebook-for-woocommerce.php'             => 'Facebook Pixel',
			'google-site-kit/google-site-kit.php'                              => 'Google Site Kit',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_tracking_tools'] = count( $active );
		$stats['tracking_plugins']      = $active;

		// Check for WooCommerce conversion tracking
		$woo_active = is_plugin_active( 'woocommerce/woocommerce.php' );
		$stats['woocommerce_active'] = $woo_active;

		if ( $woo_active && empty( $active ) ) {
			$issues[] = __( 'WooCommerce active but no conversion tracking detected', 'wpshadow' );
		} elseif ( empty( $active ) ) {
			$issues[] = __( 'No conversion tracking system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Conversion tracking measures the actions that matter to your business: purchases, sign-ups, downloads, etc. Without tracking conversions, you cannot measure ROI from your marketing efforts or optimize your sales funnel effectively.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/conversion-tracking',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
