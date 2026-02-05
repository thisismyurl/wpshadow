<?php
/**
 * Customer Survey Implementation Diagnostic
 *
 * Checks if customer satisfaction surveys are implemented on the website.
 *
 * @package WPShadow\Diagnostics
 * @since   1.6032.0145
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Customer Survey Implementation
 *
 * Detects whether the site has survey tools for collecting customer feedback.
 */
class Diagnostic_Survey_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'customer-survey-implementation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Survey Implementation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies customer satisfaction surveys are implemented';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues   = array();
		$stats    = array();
		$plugins  = array(
			'woocommerce-product-reviews-pro/woocommerce-product-reviews-pro.php' => 'WooCommerce Product Reviews Pro',
			'trustpilot-reviews/trustpilot-reviews.php'                           => 'Trustpilot Reviews',
			'surveywp/surveywp.php'                                              => 'SurveyWP',
			'quiz-master-next/quiz_master_next.php'                              => 'Quiz Master Next',
			'wp-surveys/wp-surveys.php'                                          => 'WP Surveys',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_survey_tools'] = count( $active );
		$stats['survey_plugins_found'] = $active;

		// Check for survey pages or posts
		$survey_pages = self::find_pages_by_keywords( array( 'survey', 'feedback form', 'customer feedback' ) );
		$stats['survey_pages_found'] = count( $survey_pages );

		if ( empty( $active ) && empty( $survey_pages ) ) {
			$issues[] = __( 'No customer survey tools or survey pages detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Customer surveys help collect valuable feedback to improve your products and services. Consider implementing a survey tool to gather customer satisfaction data.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/customer-surveys',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages by keywords
	 *
	 * @param array $keywords Keywords to search for
	 * @return array List of matching pages
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
