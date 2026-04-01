<?php
/**
 * Feedback Form Detection
 *
 * Checks if the site has a feedback collection mechanism.
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
 * Feedback Form Detection Diagnostic
 *
 * Verifies that a feedback form is present for collecting user feedback.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Feedback_Form_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feedback-form-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feedback Form Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if your site has a feedback form for collecting user input';

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

		// Check for feedback form plugins
		$feedback_plugins = array(
			'wp-contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                => 'WPForms',
			'formidable/formidable.php'               => 'Formidable Forms',
			'ninja-forms/ninja-forms.php'             => 'Ninja Forms',
			'WP-Optimize-Pro/wp-optimize.php'         => 'WP Feedback',
			'fluent-forms/fluent-forms.php'           => 'Fluent Forms',
		);

		$active_plugins = array();
		foreach ( $feedback_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugins[] = $name;
			}
		}

		$stats['feedback_plugins_found'] = count( $active_plugins );
		$stats['active_plugins']          = $active_plugins;

		// Check for feedback form pages
		$feedback_keywords = array( 'feedback', 'contact', 'contact-form', 'get-in-touch', 'inquiry' );
		$feedback_pages    = self::find_pages_by_keywords( $feedback_keywords );

		$stats['feedback_pages_found'] = count( $feedback_pages );
		$stats['feedback_pages']        = $feedback_pages;

		if ( empty( $active_plugins ) && empty( $feedback_pages ) ) {
			$issues[] = __( 'No feedback form detected on your site', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Collecting feedback helps improve your site and shows users you care about their input', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/feedback-form-detection?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
