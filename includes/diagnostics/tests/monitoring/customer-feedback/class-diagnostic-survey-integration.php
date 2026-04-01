<?php
/**
 * Survey Integration
 *
 * Checks if the site has survey tools integrated.
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
 * Survey Integration Diagnostic
 *
 * Verifies that survey tools are integrated for gathering structured feedback.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Survey_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'survey-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Survey Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if your site has survey tools integrated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		// Check for survey plugins
		$survey_plugins = array(
			'quillforms/quillforms.php'           => 'Quill Forms',
			'typeform/typeform.php'               => 'Typeform',
			'survey-funnel/survey-funnel.php'     => 'Survey Funnel',
			'wp-survey-and-poll/sp-poll.php'      => 'WP Survey and Poll',
			'advanced-poll-rating/ap-rating.php'  => 'Advanced Poll Rating',
			'yop-poll/yop-poll.php'               => 'YOP Poll',
		);

		$active_plugins = array();
		foreach ( $survey_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[] = $name;
			}
		}

		$stats['survey_plugins_found'] = count( $active_plugins );
		$stats['active_plugins']        = $active_plugins;

		// Check for survey pages
		$survey_keywords = array( 'survey', 'poll', 'questionnaire', 'feedback-survey', 'quiz' );
		$survey_pages    = self::find_pages_by_keywords( $survey_keywords );

		$stats['survey_pages_found'] = count( $survey_pages );
		$stats['survey_pages']        = $survey_pages;

		if ( empty( $active_plugins ) && empty( $survey_pages ) ) {
			$issues[] = __( 'No survey tools found on your site', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Surveys help you gather detailed feedback from your audience in a structured way', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/survey-integration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
	 * @since 0.6093.1200
	 * @param  array $keywords Array of keywords to search for.
	 * @return array Array of matching pages.
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

		return array_values( array_unique( $matches ) );
	}
}
