<?php
/**
 * Retargeting Campaigns Diagnostic
 *
 * Checks whether retargeting or remarketing campaigns are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retargeting Campaigns Diagnostic Class
 *
 * Verifies retargeting pixels and remarketing tools.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Retargeting_Campaigns extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'retargeting-campaigns';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Retargeting or Remarketing Campaigns';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether retargeting pixels and audiences are configured';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'paid-acquisition';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for retargeting pixel plugins (60 points).
		$retargeting_plugins = array(
			'facebook-for-woocommerce/facebook-for-woocommerce.php' => 'Facebook for WooCommerce',
			'pixel-caffeine/pixel-caffeine.php'                  => 'Pixel Caffeine',
			'official-facebook-pixel/facebook-pixel.php'         => 'Official Facebook Pixel',
			'google-tag-manager-for-wordpress/google-tag-manager-for-wordpress.php' => 'GTM4WP',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
		);

		$active_pixels = array();
		foreach ( $retargeting_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_pixels[] = $plugin_name;
				$earned_points  += 20;
			}
		}

		if ( count( $active_pixels ) > 0 ) {
			$stats['pixel_tools'] = implode( ', ', $active_pixels );
		} else {
			$issues[] = __( 'No retargeting pixel or tag manager detected', 'wpshadow' );
		}

		// Check for e-commerce or product catalogs (25 points).
		$commerce_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
		);

		$active_commerce = array();
		foreach ( $commerce_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_commerce[] = $plugin_name;
				$earned_points   += 12;
			}
		}

		if ( count( $active_commerce ) > 0 ) {
			$stats['commerce_tools'] = implode( ', ', $active_commerce );
		} else {
			$warnings[] = __( 'No e-commerce platform detected for dynamic retargeting', 'wpshadow' );
		}

		// Check for audience segmentation tools (15 points).
		$segmentation_plugins = array(
			'fluentcrm/fluentcrm.php' => 'FluentCRM',
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
		);

		$active_segments = array();
		foreach ( $segmentation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_segments[] = $plugin_name;
				$earned_points   += 7;
			}
		}

		if ( count( $active_segments ) > 0 ) {
			$stats['segmentation_tools'] = implode( ', ', $active_segments );
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
					__( 'Your retargeting setup scored %s. Most visitors do not convert on the first visit. Retargeting brings them back when they are ready. Without it, you leave a large portion of potential sales behind.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/retargeting-campaigns',
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
