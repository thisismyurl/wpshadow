<?php
/**
 * User Testing Program Diagnostic
 *
 * Tests for regular user testing and research programs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Testing Program Diagnostic Class
 *
 * Evaluates whether the site conducts regular user testing and research.
 * Checks for testing tools, session recording, feedback mechanisms, and research infrastructure.
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Testing_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conducts_user_testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Testing Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for regular user testing and research';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats           = array();
		$issues          = array();
		$warnings        = array();
		$score           = 0;
		$total_points    = 0;
		$earned_points   = 0;

		// Check for user testing/heatmap tools.
		$testing_tools = array(
			'hotjar-async'         => 'Hotjar',
			'crazyegg'             => 'CrazyEgg',
			'mouseflow'            => 'Mouseflow',
			'fullstory'            => 'FullStory',
			'smartlook'            => 'Smartlook',
			'lucky-orange'         => 'Lucky Orange',
			'inspectlet'           => 'Inspectlet',
			'clicktale'            => 'ClickTale',
			'usertesting'          => 'UserTesting',
			'userzoom'             => 'UserZoom',
		);

		$active_testing_tools = array();
		foreach ( $testing_tools as $handle => $name ) {
			$total_points += 15;
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_testing_tools[] = $name;
				$earned_points         += 15;
			}
		}

		$stats['testing_tools'] = array(
			'found' => count( $active_testing_tools ),
			'list'  => $active_testing_tools,
		);

		if ( empty( $active_testing_tools ) ) {
			$issues[] = __( 'No user testing or session recording tools detected', 'wpshadow' );
		}

		// Check for feedback/survey plugins.
		$feedback_plugins = array(
			'qualaroo/qualaroo.php'               => 'Qualaroo',
			'usabilla/usabilla.php'               => 'Usabilla',
			'survey-maker/survey-maker.php'       => 'Survey Maker',
			'wp-feedback/wp-feedback.php'         => 'WP Feedback',
			'usersnap/usersnap.php'               => 'Usersnap',
			'feedback-fish/feedback-fish.php'     => 'Feedback Fish',
		);

		$active_feedback_tools = array();
		foreach ( $feedback_plugins as $plugin => $name ) {
			$total_points += 10;
			if ( is_plugin_active( $plugin ) ) {
				$active_feedback_tools[] = $name;
				$earned_points          += 10;
			}
		}

		$stats['feedback_tools'] = array(
			'found' => count( $active_feedback_tools ),
			'list'  => $active_feedback_tools,
		);

		if ( empty( $active_feedback_tools ) ) {
			$warnings[] = __( 'No user feedback or survey tools detected', 'wpshadow' );
		}

		// Check for usability testing infrastructure.
		$total_points += 15;
		$testing_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				's'              => 'usability test',
			)
		);

		if ( ! empty( $testing_pages ) ) {
			$earned_points += 15;
			$stats['testing_pages'] = count( $testing_pages );
		} else {
			$stats['testing_pages'] = 0;
			$warnings[] = __( 'No dedicated usability testing pages found', 'wpshadow' );
		}

		// Check for user research documentation.
		$total_points += 10;
		$research_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'any',
				's'              => 'user research',
			)
		);

		if ( ! empty( $research_posts ) ) {
			$earned_points += 10;
			$stats['research_docs'] = count( $research_posts );
		} else {
			$stats['research_docs'] = 0;
		}

		// Check for A/B testing tools (indicates active testing culture).
		$ab_testing_scripts = array(
			'google-optimize' => 'Google Optimize',
			'optimizely'      => 'Optimizely',
			'vwo'             => 'VWO',
		);

		$active_ab_tools = array();
		foreach ( $ab_testing_scripts as $handle => $name ) {
			$total_points += 10;
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_ab_tools[] = $name;
				$earned_points    += 10;
			}
		}

		$stats['ab_testing_tools'] = array(
			'found' => count( $active_ab_tools ),
			'list'  => $active_ab_tools,
		);

		// Check for analytics with user flow tracking.
		$total_points += 10;
		if ( wp_script_is( 'google-analytics', 'enqueued' ) ||
			 wp_script_is( 'gtag', 'enqueued' ) ||
			 is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ) {
			$earned_points += 10;
			$stats['analytics_enabled'] = true;
		} else {
			$stats['analytics_enabled'] = false;
			$warnings[] = __( 'No analytics platform detected for user flow tracking', 'wpshadow' );
		}

		// Check for accessibility testing tools.
		$total_points += 10;
		$accessibility_plugins = array(
			'wp-accessibility/wp-accessibility.php' => 'WP Accessibility',
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
		);

		$active_a11y_tools = array();
		foreach ( $accessibility_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_a11y_tools[] = $name;
			}
		}

		if ( ! empty( $active_a11y_tools ) ) {
			$earned_points += 10;
		}

		$stats['accessibility_tools'] = array(
			'found' => count( $active_a11y_tools ),
			'list'  => $active_a11y_tools,
		);

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 40;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 55;
		} elseif ( $score > 70 ) {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if testing infrastructure is insufficient.
		if ( $score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: testing score percentage */
					__( 'User testing infrastructure score: %d%%. Regular user testing and research helps optimize conversion rates.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-testing-program',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
