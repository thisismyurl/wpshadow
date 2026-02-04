<?php
/**
 * Loyalty Program Diagnostic
 *
 * Checks whether a loyalty or rewards program is available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerRetention
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loyalty Program Diagnostic Class
 *
 * Verifies that a loyalty or rewards program exists.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Loyalty_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'loyalty-program';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Loyalty or Rewards Program';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a loyalty or rewards program exists';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$loyalty_plugins = array(
			'woocommerce-points-and-rewards/woocommerce-points-and-rewards.php' => 'WooCommerce Points and Rewards',
			'sumo-reward-points/sumo-reward-points.php' => 'SUMO Reward Points',
			'gratisfaction/gratisfaction.php' => 'Gratisfaction',
			'mycred/mycred.php' => 'myCred',
		);

		$active_loyalty = array();
		foreach ( $loyalty_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_loyalty[] = $plugin_name;
			}
		}

		$stats['loyalty_tools'] = ! empty( $active_loyalty ) ? implode( ', ', $active_loyalty ) : 'none';

		$loyalty_pages = self::find_pages_by_keywords( array( 'loyalty', 'rewards', 'points', 'member perks' ) );
		$stats['loyalty_pages'] = ! empty( $loyalty_pages ) ? implode( ', ', $loyalty_pages ) : 'none';

		if ( empty( $active_loyalty ) && empty( $loyalty_pages ) ) {
			$issues[] = __( 'No loyalty or rewards program detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A loyalty program gives customers a reason to return and buy again. Even a small points system can improve retention.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/loyalty-program',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since  1.6035.1400
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
