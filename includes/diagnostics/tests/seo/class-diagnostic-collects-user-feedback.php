<?php
/**
 * Diagnostic: Collects User Feedback
 *
 * Tests if site actively collects and responds to user feedback.
 * User feedback helps improve content, products, and user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collects User Feedback Diagnostic Class
 *
 * Checks if site has mechanisms to collect user feedback through
 * surveys, polls, feedback forms, or rating systems.
 *
 * Detection methods:
 * - Survey/poll plugins
 * - Feedback form presence
 * - Rating/review systems
 * - NPS survey tools
 *
 * @since 1.7034.1430
 */
class Diagnostic_Collects_User_Feedback extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'collects-user-feedback';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Collects User Feedback';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site actively collects and responds to user feedback';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Survey/poll plugin installed
	 * - 1 point: Feedback form on contact page
	 * - 1 point: Rating system on posts/products
	 * - 1 point: NPS or customer satisfaction tool
	 * - 1 point: Recent feedback collected (past 30 days)
	 *
	 * @since  1.7034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for survey/poll plugins.
		$survey_plugins = array(
			'crowdsignal-forms/crowdsignal-forms.php'    => 'Crowdsignal',
			'formidable/formidable.php'                  => 'Formidable Forms',
			'wp-polls/wp-polls.php'                      => 'WP-Polls',
			'yop-poll/yop-poll.php'                      => 'YOP Poll',
			'totalpoll/totalpoll.php'                    => 'TotalPoll',
		);

		foreach ( $survey_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['survey_tool'] = $name;
				break;
			}
		}

		// Check for feedback/contact form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'       => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                   => 'WPForms',
			'ninja-forms/ninja-forms.php'                => 'Ninja Forms',
			'forminator/forminator.php'                  => 'Forminator',
			'gravityforms/gravityforms.php'              => 'Gravity Forms',
		);

		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['form_plugin'] = $name;
				break;
			}
		}

		// Check for rating/review systems.
		$rating_enabled = false;
		
		// Check if WooCommerce reviews are enabled.
		if ( class_exists( 'WooCommerce' ) ) {
			$reviews_enabled = get_option( 'woocommerce_enable_reviews', 'yes' );
			if ( 'yes' === $reviews_enabled ) {
				$rating_enabled = true;
			}
		}

		// Check for rating plugins.
		$rating_plugins = array(
			'yet-another-stars-rating/yet-another-stars-rating.php' => 'YASR',
			'kk-star-ratings/index.php'                             => 'KK Star Ratings',
			'wp-postratings/wp-postratings.php'                     => 'WP-PostRatings',
		);

		foreach ( $rating_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$rating_enabled = true;
				$details['rating_plugin'] = $name;
				break;
			}
		}

		if ( $rating_enabled ) {
			$score++;
		}

		// Check for NPS/customer satisfaction tools.
		$nps_plugins = array(
			'hotjar/hotjar.php'                          => 'Hotjar',
			'qualaroo/qualaroo.php'                      => 'Qualaroo',
			'usabilla/usabilla.php'                      => 'Usabilla',
		);

		foreach ( $nps_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['nps_tool'] = $name;
				break;
			}
		}

		// Check for recent comments (proxy for feedback).
		$recent_comments = get_comments(
			array(
				'number'      => 10,
				'status'      => 'approve',
				'date_query'  => array(
					array(
						'after' => '30 days ago',
					),
				),
			)
		);

		if ( count( $recent_comments ) >= 5 ) {
			$score++;
			$details['recent_feedback'] = sprintf(
				/* translators: %d: number of comments */
				__( '%d comments in past 30 days', 'wpshadow' ),
				count( $recent_comments )
			);
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'medium' : 'low';
		$threat_level = (int) ( 55 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'User feedback collection score: %d%%. Actively gathering feedback helps improve your content and user experience.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/user-feedback',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since  1.7034.1430
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'User feedback is your direct line to understanding what\'s working and what\'s not. Comments, surveys, ratings, and feedback forms reveal pain points you might never discover on your own. This feedback helps you prioritize improvements, create better content, and build trust by showing users you\'re listening.',
			'wpshadow'
		);
	}
}
