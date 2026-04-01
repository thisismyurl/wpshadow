<?php
/**
 * Customer Journey Mapping Diagnostic
 *
 * Checks whether a documented customer journey map exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Journey Mapping Diagnostic Class
 *
 * Verifies that journey documentation and touchpoint planning is present.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Customer_Journey_Mapping extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'customer-journey-mapping';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Journey Mapping';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a customer journey map is documented';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-research';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for journey documentation pages (50 points).
		$journey_pages = self::find_pages_by_keywords(
			array(
				'customer journey',
				'journey map',
				'funnel',
				'buyer journey',
			)
		);

		if ( count( $journey_pages ) > 0 ) {
			$earned_points         += 50;
			$stats['journey_pages'] = implode( ', ', $journey_pages );
		} else {
			$issues[] = __( 'No customer journey or funnel documentation detected', 'wpshadow' );
		}

		// Check for analytics tools (30 points).
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
			$warnings[] = __( 'No analytics tools detected for journey tracking', 'wpshadow' );
		}

		// Check for conversion tracking tools (20 points).
		$conversion_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
		);

		$active_conversions = array();
		foreach ( $conversion_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_conversions[] = $plugin_name;
				$earned_points       += 7;
			}
		}

		if ( count( $active_conversions ) > 0 ) {
			$stats['conversion_tools'] = implode( ', ', $active_conversions );
		} else {
			$warnings[] = __( 'No conversion tracking tools detected', 'wpshadow' );
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
					__( 'Your customer journey mapping scored %s. Without a clear map of the steps customers take, it is hard to spot where they drop off. Mapping the journey helps you remove friction and improve conversions at every stage.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/customer-journey-mapping?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 0.6093.1200
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
