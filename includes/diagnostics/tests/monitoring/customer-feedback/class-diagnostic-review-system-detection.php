<?php
/**
 * Review System Detection
 *
 * Checks if the site has a review/rating system.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review System Detection Diagnostic
 */
class Diagnostic_Review_System_Detection extends Diagnostic_Base {

	protected static $slug = 'review-system-detection';
	protected static $title = 'Review System Detection';
	protected static $description = 'Checks if your site has a review or rating system';
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.0200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$review_plugins = array(
			'yotpo-social-reviews-ugc/yotpo-social-reviews-ugc.php' => 'Yotpo Reviews',
			'woo-advanced-reviews/woo-advanced-reviews.php'         => 'WooCommerce Advanced Reviews',
			'wp-product-review/wp-product-review.php'               => 'WP Product Review',
			'leaderboard/leaderboard.php'                           => 'Leaderboard',
		);

		$active_plugins = array();
		foreach ( $review_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[] = $name;
			}
		}

		$stats['review_plugins_found'] = count( $active_plugins );

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No review system found on your site', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Customer reviews build trust and provide social proof to potential buyers', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/review-system-detection',
				'context'       => array( 'stats' => $stats, 'issues' => $issues ),
			);
		}

		return null;
	}
}
