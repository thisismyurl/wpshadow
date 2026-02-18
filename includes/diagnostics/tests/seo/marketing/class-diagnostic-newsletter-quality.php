<?php
/**
 * Newsletter Quality Diagnostic
 *
 * Tests newsletter frequency, design quality, and engagement tracking
 * to ensure effective email marketing practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.1645
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter Quality Diagnostic Class
 *
 * Verifies newsletter management tools and tracking systems are in place
 * for effective email marketing campaigns.
 *
 * @since 1.6035.1645
 */
class Diagnostic_Newsletter_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'maintains_newsletter_quality';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Newsletter Quality';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies newsletter frequency, design, and engagement tracking';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1645
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for email marketing platforms (45 points).
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php'            => 'Mailchimp for WP',
			'newsletter/newsletter.php'                        => 'Newsletter',
			'mailpoet/mailpoet.php'                            => 'MailPoet',
			'email-subscribers/email-subscribers.php'          => 'Email Subscribers',
			'sendinblue-mailin/sendinblue.php'                 => 'Brevo (Sendinblue)',
		);

		$active_email = array();
		foreach ( $email_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_email[]    = $plugin_name;
				$earned_points    += 23; // Up to 45 points.
			}
		}

		if ( count( $active_email ) > 0 ) {
			$stats['email_marketing_plugins'] = implode( ', ', $active_email );
		} else {
			$issues[] = 'No email marketing platform detected';
		}

		// Check for email analytics/tracking (25 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'email-log/email-log.php'                            => 'Email Log',
			'wp-mail-logging/wp-mail-logging.php'                => 'WP Mail Logging',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 13; // Up to 25 points.
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['email_analytics_plugins'] = implode( ', ', $active_analytics );
		} else {
			$warnings[] = 'No email analytics tracking detected';
		}

		// Check for email design/template tools (20 points).
		$design_plugins = array(
			'elementor/elementor.php'                          => 'Elementor',
			'beaver-builder-lite-version/fl-builder.php'       => 'Beaver Builder',
			'divi-builder/divi-builder.php'                    => 'Divi Builder',
		);

		$active_design = array();
		foreach ( $design_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_design[] = $plugin_name;
				$earned_points  += 10; // Up to 20 points.
			}
		}

		if ( count( $active_design ) > 0 ) {
			$stats['design_plugins'] = implode( ', ', $active_design );
		} else {
			$warnings[] = 'No visual design tools detected';
		}

		// Check for SMTP configuration (10 points).
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'                    => 'WP Mail SMTP',
			'easy-wp-smtp/easy-wp-smtp.php'                    => 'Easy WP SMTP',
			'post-smtp/postman-smtp.php'                       => 'Post SMTP',
		);

		$active_smtp = array();
		foreach ( $smtp_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_smtp[]  = $plugin_name;
				$earned_points += 10;
			}
		}

		if ( count( $active_smtp ) > 0 ) {
			$stats['smtp_plugins'] = implode( ', ', $active_smtp );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 40%.
		if ( $score < 40 ) {
			$severity     = $score < 20 ? 'medium' : 'low';
			$threat_level = $score < 20 ? 35 : 25;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your newsletter system scored %s. Email marketing remains one of the highest-ROI channels, but only with consistent quality. Professional tools help you design beautiful emails, maintain sending schedules, and track what resonates with subscribers.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/newsletter-quality',
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
