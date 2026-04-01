<?php
/**
 * Customer Feedback Diagnostic
 *
 * Checks whether a customer feedback or survey system exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerFeedback
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Feedback Diagnostic Class
 *
 * Verifies that surveys or feedback forms are available.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Customer_Feedback_System extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'customer-feedback-system';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Feedback or Survey System';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feedback surveys or forms are available';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$survey_plugins = array(
			'wpforms-lite/wpforms.php' => 'WPForms',
			'gravityforms/gravityforms.php' => 'Gravity Forms',
			'fluentform/fluentform.php' => 'Fluent Forms',
			'formidable/formidable.php' => 'Formidable Forms',
			'userfeedback-lite/userfeedback.php' => 'UserFeedback',
		);

		$active_surveys = array();
		foreach ( $survey_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_surveys[] = $plugin_name;
			}
		}

		$stats['survey_tools'] = ! empty( $active_surveys ) ? implode( ', ', $active_surveys ) : 'none';

		$survey_pages = self::find_pages_by_keywords( array( 'feedback', 'survey', 'nps', 'review' ) );
		$stats['survey_pages'] = ! empty( $survey_pages ) ? implode( ', ', $survey_pages ) : 'none';

		if ( empty( $active_surveys ) && empty( $survey_pages ) ) {
			$issues[] = __( 'No customer feedback or survey system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Feedback helps you improve what matters most to customers. Simple surveys can reveal what is working and what needs attention.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/customer-feedback-system?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
