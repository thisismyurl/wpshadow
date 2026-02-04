<?php
/**
 * Repeat Purchase Incentives Diagnostic
 *
 * Checks whether repeat purchase incentives are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\RetentionOptimization
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repeat Purchase Incentives Diagnostic Class
 *
 * Verifies that repeat purchase incentives or reorder options exist.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Repeat_Purchase_Incentives extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'repeat-purchase-incentives';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Repeat Purchase Incentive Program';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if repeat purchase incentives or reorder tools exist';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'retention-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$repeat_plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php' => 'WooCommerce Subscriptions',
			'woocommerce-memberships/woocommerce-memberships.php' => 'WooCommerce Memberships',
			'woocommerce-follow-up-emails/woocommerce-follow-up-emails.php' => 'Follow-Up Emails',
			'automatewoo/automatewoo.php' => 'AutomateWoo',
		);

		$active_repeat = array();
		foreach ( $repeat_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_repeat[] = $plugin_name;
			}
		}

		$stats['repeat_tools'] = ! empty( $active_repeat ) ? implode( ', ', $active_repeat ) : 'none';

		$repeat_pages = self::find_pages_by_keywords( array( 'reorder', 'subscribe', 'repeat', 'save on repeat' ) );
		$stats['repeat_pages'] = ! empty( $repeat_pages ) ? implode( ', ', $repeat_pages ) : 'none';

		if ( empty( $active_repeat ) && empty( $repeat_pages ) ) {
			$issues[] = __( 'No repeat purchase incentives detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Repeat purchase incentives remind customers to come back and make reordering easy. Even a small follow-up offer can increase retention.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/repeat-purchase-incentives',
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
