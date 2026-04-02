<?php
/**
 * Voice of Customer Program Diagnostic
 *
 * Checks whether a structured VOC program is in place.
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
 * Voice of Customer Program Diagnostic Class
 *
 * Verifies that customer interviews, surveys, and feedback loops exist.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Voice_Of_Customer_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'voice-of-customer-program';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Voice of Customer (VOC) Program';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether structured VOC processes are documented';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-research';

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

		// Check for feedback pages (40 points).
		$feedback_pages = self::find_pages_by_keywords(
			array(
				'feedback',
				'customer survey',
				'voice of customer',
				'customer feedback',
				'reviews',
			)
		);

		if ( count( $feedback_pages ) > 0 ) {
			$earned_points           += 40;
			$stats['feedback_pages'] = implode( ', ', $feedback_pages );
		} else {
			$issues[] = __( 'No feedback or survey pages detected', 'wpshadow' );
		}

		// Check for survey tools (35 points).
		$survey_plugins = array(
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'formidable/formidable.php'            => 'Formidable Forms',
		);

		$active_surveys = array();
		foreach ( $survey_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_surveys[] = $plugin_name;
				$earned_points  += 12;
			}
		}

		if ( count( $active_surveys ) > 0 ) {
			$stats['survey_tools'] = implode( ', ', $active_surveys );
		} else {
			$warnings[] = __( 'No survey tools detected for VOC collection', 'wpshadow' );
		}

		// Check for support channels (25 points).
		$support_plugins = array(
			'awesome-support/awesome-support.php' => 'Awesome Support',
			'fluent-support/fluent-support.php'   => 'Fluent Support',
			'wp-support-plus-responsive-ticket-system/wp-support-plus.php' => 'Support Plus',
		);

		$active_support = array();
		foreach ( $support_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_support[] = $plugin_name;
				$earned_points  += 8;
			}
		}

		if ( count( $active_support ) > 0 ) {
			$stats['support_tools'] = implode( ', ', $active_support );
		} else {
			$warnings[] = __( 'No support ticketing tools detected for customer insights', 'wpshadow' );
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
					__( 'Your voice of customer program scored %s. Without listening to customers, decisions become guesswork. Regular surveys, reviews, and support insights help you improve products and messaging based on real needs.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/voice-of-customer',
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
	 * @since 1.6093.1200
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
