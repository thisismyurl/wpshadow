<?php
/**
 * Member Satisfaction Surveys Diagnostic
 *
 * Tests whether the site conducts quarterly member satisfaction surveys and acts on feedback.
 *
 * @since   1.26034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Satisfaction Surveys Diagnostic Class
 *
 * Regular satisfaction surveys help identify issues, gather feedback, and improve
 * member experience, reducing churn and increasing retention.
 *
 * @since 1.26034.0230
 */
class Diagnostic_Member_Satisfaction_Surveys extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'member-satisfaction-surveys';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Satisfaction Surveys';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site conducts quarterly member satisfaction surveys and acts on feedback';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for membership sites.
		if ( ! self::is_membership_site() ) {
			return null;
		}

		$issues = array();
		$survey_score = 0;
		$max_score = 6;

		// Check for survey plugins.
		$survey_plugins = array(
			'wp-polls/wp-polls.php' => 'WP-Polls',
			'survey-maker/survey-maker.php' => 'Survey Maker',
			'formidable/formidable.php' => 'Formidable Forms',
			'crowdsignal-forms/crowdsignal-forms.php' => 'Crowdsignal',
			'quiz-and-survey-master/quiz_master_next.php' => 'Quiz and Survey Master',
		);

		$has_survey_plugin = false;
		foreach ( $survey_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_survey_plugin = true;
				$survey_score++;
				break;
			}
		}

		if ( ! $has_survey_plugin ) {
			$issues[] = __( 'No survey plugin detected', 'wpshadow' );
		}

		// Check for recent survey posts/pages.
		$recent_surveys = self::check_recent_surveys();
		if ( $recent_surveys ) {
			$survey_score++;
		} else {
			$issues[] = __( 'No recent satisfaction surveys found in past 90 days', 'wpshadow' );
		}

		// Check for feedback collection system.
		$feedback_system = self::check_feedback_system();
		if ( $feedback_system ) {
			$survey_score++;
		} else {
			$issues[] = __( 'No systematic feedback collection mechanism', 'wpshadow' );
		}

		// Check for Net Promoter Score (NPS) implementation.
		$nps_tracking = self::check_nps_tracking();
		if ( $nps_tracking ) {
			$survey_score++;
		} else {
			$issues[] = __( 'No Net Promoter Score (NPS) tracking', 'wpshadow' );
		}

		// Check for survey automation.
		$automated_surveys = self::check_automated_surveys();
		if ( $automated_surveys ) {
			$survey_score++;
		} else {
			$issues[] = __( 'Surveys not automated or scheduled regularly', 'wpshadow' );
		}

		// Check for feedback-driven improvements.
		$feedback_action = self::check_feedback_action();
		if ( $feedback_action ) {
			$survey_score++;
		} else {
			$issues[] = __( 'No evidence of acting on survey feedback', 'wpshadow' );
		}

		// Determine severity based on survey implementation.
		$survey_percentage = ( $survey_score / $max_score ) * 100;

		if ( $survey_percentage < 30 ) {
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( $survey_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 30;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Survey implementation percentage */
				__( 'Member satisfaction tracking at %d%%. ', 'wpshadow' ),
				(int) $survey_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Regular surveys can reduce churn by 15-25%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/member-satisfaction-surveys',
			);
		}

		return null;
	}

	/**
	 * Check if this is a membership site.
	 *
	 * @since  1.26034.0230
	 * @return bool True if membership features detected, false otherwise.
	 */
	private static function is_membership_site() {
		// Check for membership plugins.
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
			'woocommerce-memberships/woocommerce-memberships.php',
			's2member/s2member.php',
		);

		foreach ( $membership_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for recent surveys.
	 *
	 * @since  1.26034.0230
	 * @return bool True if recent surveys exist, false otherwise.
	 */
	private static function check_recent_surveys() {
		$keywords = array( 'survey', 'satisfaction', 'feedback form', 'member survey' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
					'date_query'     => array(
						array(
							'after' => '90 days ago',
						),
					),
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_recent_surveys', false );
	}

	/**
	 * Check for feedback collection system.
	 *
	 * @since  1.26034.0230
	 * @return bool True if feedback system exists, false otherwise.
	 */
	private static function check_feedback_system() {
		// Check for form plugins that could collect feedback.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'ninja-forms/ninja-forms.php',
			'gravityforms/gravityforms.php',
		);

		foreach ( $form_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_feedback_system', false );
	}

	/**
	 * Check for NPS tracking.
	 *
	 * @since  1.26034.0230
	 * @return bool True if NPS tracking exists, false otherwise.
	 */
	private static function check_nps_tracking() {
		// Check for NPS-related content or plugins.
		$nps_keywords = array( 'net promoter score', 'nps', 'recommend to a friend' );

		foreach ( $nps_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_nps_tracking', false );
	}

	/**
	 * Check for automated surveys.
	 *
	 * @since  1.26034.0230
	 * @return bool True if automated surveys exist, false otherwise.
	 */
	private static function check_automated_surveys() {
		// Check for email marketing with automation.
		$automation_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
		);

		foreach ( $automation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_automated_surveys', false );
	}

	/**
	 * Check for evidence of acting on feedback.
	 *
	 * @since  1.26034.0230
	 * @return bool True if feedback action evidence exists, false otherwise.
	 */
	private static function check_feedback_action() {
		// Check for posts about improvements based on feedback.
		$action_keywords = array( 'based on your feedback', 'you asked for', 'member requested' );

		foreach ( $action_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_acts_on_feedback', false );
	}
}
