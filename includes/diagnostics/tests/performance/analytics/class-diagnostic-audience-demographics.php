<?php
/**
 * Audience Demographics Diagnostic
 *
 * Tests if audience demographics are analyzed and understood through
 * analytics platforms and demographic tracking tools.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audience Demographics Diagnostic Class
 *
 * Verifies demographic tracking and audience analysis tools are configured
 * to enable targeted content and marketing strategies.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Audience_Demographics extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'knows_audience_demographics';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Audience Demographics Known';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies audience demographics are tracked and analyzed';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

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

		// Check for Google Analytics with demographics enabled (35 points).
		$ga_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
		);

		$active_ga = array();
		foreach ( $ga_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ga[]    = $plugin_name;
				$earned_points += 12; // Up to 35 points.
			}
		}

		if ( count( $active_ga ) > 0 ) {
			$stats['analytics_plugins'] = implode( ', ', $active_ga );
		} else {
			$issues[] = 'No analytics plugins detected for demographic tracking';
		}

		// Check for user tracking/profiling plugins (25 points).
		$profile_plugins = array(
			'user-registration/user-registration.php'           => 'User Registration',
			'profile-builder/index.php'                         => 'Profile Builder',
			'ultimate-member/ultimate-member.php'               => 'Ultimate Member',
			'memberpress/memberpress.php'                       => 'MemberPress',
		);

		$active_profile = array();
		foreach ( $profile_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_profile[] = $plugin_name;
				$earned_points   += 8; // Up to 25 points.
			}
		}

		if ( count( $active_profile ) > 0 ) {
			$stats['user_profile_plugins'] = implode( ', ', $active_profile );
		} else {
			$warnings[] = 'No user profiling plugins detected';
		}

		// Check for survey/feedback plugins (20 points).
		$survey_plugins = array(
			'wpforms-lite/wpforms.php'                          => 'WPForms',
			'contact-form-7/wp-contact-form-7.php'              => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'                       => 'Ninja Forms',
			'formidable/formidable.php'                         => 'Formidable Forms',
		);

		$active_survey = array();
		foreach ( $survey_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_survey[] = $plugin_name;
				$earned_points  += 7; // Up to 20 points.
			}
		}

		if ( count( $active_survey ) > 0 ) {
			$stats['survey_plugins'] = implode( ', ', $active_survey );
		} else {
			$warnings[] = 'No survey/feedback plugins detected';
		}

		// Check for email marketing with demographics (15 points).
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php'            => 'MailChimp',
			'newsletter/plugin.php'                             => 'Newsletter',
			'mailoptin/mailoptin.php'                           => 'MailOptin',
			'email-subscribers/email-subscribers.php'           => 'Email Subscribers',
		);

		$active_email = array();
		foreach ( $email_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_email[] = $plugin_name;
				$earned_points += 5; // Up to 15 points.
			}
		}

		if ( count( $active_email ) > 0 ) {
			$stats['email_marketing_plugins'] = implode( ', ', $active_email );
		}

		// Check for social media insights (5 points).
		$social_plugins = array(
			'facebook-for-wordpress/facebook-for-wordpress.php' => 'Facebook for WordPress',
			'instagram-feed/instagram-feed.php'                 => 'Instagram Feed',
		);

		$active_social = array();
		foreach ( $social_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_social[] = $plugin_name;
				$earned_points  += 3; // Up to 5 points.
			}
		}

		if ( count( $active_social ) > 0 ) {
			$stats['social_insights_plugins'] = implode( ', ', $active_social );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 50%.
		if ( $score < 50 ) {
			$severity     = $score < 30 ? 'medium' : 'low';
			$threat_level = $score < 30 ? 40 : 30;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your audience demographic tracking scored %s. Understanding who your visitors are (age, gender, location, interests) helps you create targeted content and marketing campaigns. Without demographic data, you\'re guessing who you\'re talking to.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/audience-demographics',
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
