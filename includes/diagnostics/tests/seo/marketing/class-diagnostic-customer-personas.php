<?php
/**
 * Customer Persona Research Diagnostic
 *
 * Checks whether customer personas are documented and researched.
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
 * Customer Persona Research Diagnostic Class
 *
 * Verifies that customer personas are documented and used in strategy.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Customer_Personas extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'customer-persona-research';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Persona Development or Research';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer personas are documented and researched';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-research';

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

		// Check for persona documentation pages (45 points).
		$persona_pages = self::find_pages_by_keywords(
			array(
				'persona',
				'customer profile',
				'target audience',
				'who we serve',
				'ideal customer',
			)
		);

		if ( count( $persona_pages ) > 0 ) {
			$earned_points          += 45;
			$stats['persona_pages'] = implode( ', ', $persona_pages );
		} else {
			$issues[] = __( 'No customer persona or target audience documentation detected', 'wpshadow' );
		}

		// Check for CRM/marketing tools (35 points).
		$crm_plugins = array(
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
			'jetpack/jetpack.php'                              => 'Jetpack CRM',
			'zero-bs-crm/zero-bs-crm.php'                      => 'Zero BS CRM',
			'fluentcrm/fluentcrm.php'                          => 'FluentCRM',
		);

		$active_crm = array();
		foreach ( $crm_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_crm[]   = $plugin_name;
				$earned_points += 18;
			}
		}

		if ( count( $active_crm ) > 0 ) {
			$stats['crm_tools'] = implode( ', ', $active_crm );
		} else {
			$warnings[] = __( 'No CRM or customer segmentation tools detected', 'wpshadow' );
		}

		// Check for analytics tools (20 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 7;
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['analytics_tools'] = implode( ', ', $active_analytics );
		} else {
			$warnings[] = __( 'No analytics tools detected for persona insights', 'wpshadow' );
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
					__( 'Your customer persona research scored %s. When you try to speak to everyone, you connect with no one. Clear personas help you write messages that feel personal and relevant, which can improve conversions significantly.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/customer-personas',
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
