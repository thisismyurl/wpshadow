<?php
/**
 * Win-Back Campaign Diagnostic
 *
 * Checks whether lapsed customer win-back automations exist.
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
 * Win-Back Campaign Diagnostic Class
 *
 * Verifies that win-back or reactivation tools exist.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Win_Back_Campaign extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'win-back-campaign';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Win-Back Campaign for Lapsed Customers';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if win-back campaigns for lapsed customers exist';

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

		$winback_plugins = array(
			'automatewoo/automatewoo.php' => 'AutomateWoo',
			'woocommerce-follow-up-emails/woocommerce-follow-up-emails.php' => 'Follow-Up Emails',
			'klaviyo/klaviyo.php' => 'Klaviyo',
			'fluentcrm/fluentcrm.php' => 'FluentCRM',
		);

		$active_winback = array();
		foreach ( $winback_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_winback[] = $plugin_name;
			}
		}

		$stats['winback_tools'] = ! empty( $active_winback ) ? implode( ', ', $active_winback ) : 'none';

		$winback_pages = self::find_pages_by_keywords( array( 'we miss you', 'come back', 'reactivation', 'win back' ) );
		$stats['winback_pages'] = ! empty( $winback_pages ) ? implode( ', ', $winback_pages ) : 'none';

		if ( empty( $active_winback ) && empty( $winback_pages ) ) {
			$issues[] = __( 'No win-back campaign detected for lapsed customers', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Win-back campaigns help past customers return. A friendly reminder or comeback offer can revive relationships at a lower cost than new acquisition.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/win-back-campaign',
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
