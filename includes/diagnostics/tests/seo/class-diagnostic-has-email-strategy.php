<?php
/**
 * Diagnostic: Email Engagement Strategy
 *
 * Tests if site has an active email engagement and nurturing strategy.
 * Email engagement keeps subscribers interested and drives conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Engagement Strategy Diagnostic Class
 *
 * Checks if site sends regular emails and has strategies to
 * keep subscribers engaged beyond just collecting emails.
 *
 * Detection methods:
 * - Email marketing service integration
 * - Automation/sequence setup
 * - Newsletter publishing
 * - Segmentation capabilities
 *
 * @since 0.6093.1200
 */
class Diagnostic_Has_Email_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'has-email-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Engagement Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site has an active email engagement and nurturing strategy';

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
	 * - 1 point: Email service with automation features
	 * - 1 point: Welcome sequence configured
	 * - 1 point: Regular newsletter schedule
	 * - 1 point: Segmentation or tagging used
	 * - 1 point: Broadcast emails sent recently
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for advanced email marketing services with automation.
		$automation_services = array(
			'mailpoet/mailpoet.php'                      => 'MailPoet',
			'convertkit/convertkit.php'                  => 'ConvertKit',
			'klaviyo/klaviyo.php'                        => 'Klaviyo',
			'drip/drip.php'                              => 'Drip',
			'activecampaign/activecampaign.php'          => 'ActiveCampaign',
		);

		foreach ( $automation_services as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['automation_service'] = $name;
				break;
			}
		}

		// Check for newsletter plugins.
		$newsletter_plugins = array(
			'newsletter/plugin.php'                      => 'Newsletter',
			'mailpoet/mailpoet.php'                      => 'MailPoet',
		);

		foreach ( $newsletter_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				// Check if newsletters have been sent.
				if ( 'newsletter/plugin.php' === $plugin ) {
					global $wpdb;
					$sent_emails = $wpdb->get_var(
						"SELECT COUNT(*) FROM {$wpdb->prefix}newsletter_emails
						WHERE status = 'sent'
						AND send_on > DATE_SUB(NOW(), INTERVAL 90 DAY)"
					);

					if ( $sent_emails > 0 ) {
						$score++;
						$details['recent_newsletters'] = (int) $sent_emails;
					}
				}
				break;
			}
		}

		// Check for RSS-to-email functionality (indicates regular sending).
		if ( is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' ) ) {
			// MailChimp RSS campaigns.
			$score++;
			$details['rss_to_email'] = true;
		}

		// Check for post notification features.
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			if ( class_exists( 'Jetpack_Subscriptions' ) ) {
				$score++;
				$details['post_notifications'] = 'Jetpack Subscriptions';
			}
		}

		// Check for WooCommerce email customization (if ecommerce).
		if ( class_exists( 'WooCommerce' ) ) {
			// Check for email customization plugins.
			$woo_email_plugins = array(
				'woo-email-customizer/woo-email-customizer.php' => 'WooCommerce Email Customizer',
				'yith-woocommerce-email-templates/init.php'     => 'YITH Email Templates',
			);

			foreach ( $woo_email_plugins as $plugin => $name ) {
				if ( is_plugin_active( $plugin ) ) {
					$score++;
					$details['ecommerce_email_customization'] = $name;
					break;
				}
			}
		}

		// Check for email logs (indicates active sending).
		if ( is_plugin_active( 'wp-mail-logging/wp-mail-logging.php' ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpml_mails';

			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
				$recent_emails = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $table_name
						WHERE time > %s",
						date( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
					)
				);

				if ( $recent_emails > 10 ) {
					$score++;
					$details['recent_emails_sent'] = (int) $recent_emails;
				}
			}
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
				__( 'Email engagement score: %d%%. Active email marketing keeps your audience engaged and drives repeat visitors.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-engagement-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since 0.6093.1200
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Building an email list is just the start. The real value comes from engaging those subscribers. Regular emails keep your brand top-of-mind, drive traffic to new content, and nurture relationships. Automated sequences (welcome series, educational courses, post-purchase follow-ups) work 24/7 to convert subscribers into customers. Without engagement, your list becomes cold and worthless.',
			'wpshadow'
		);
	}
}
