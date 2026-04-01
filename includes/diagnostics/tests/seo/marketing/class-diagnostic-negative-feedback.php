<?php
/**
 * Negative Feedback Response Diagnostic
 *
 * Tests if site addresses negative feedback/reviews promptly with
 * monitoring tools and response systems in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Negative Feedback Response Diagnostic Class
 *
 * Verifies review monitoring and response management systems are in place
 * to handle negative feedback promptly.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Negative_Feedback extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'addresses_negative_feedback';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Negative Feedback Response';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies negative feedback is monitored and addressed promptly';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for review management plugins (40 points).
		$review_plugins = array(
			'wp-review-slider/wp-review-slider.php'            => 'WP Review Slider',
			'site-reviews/site-reviews.php'                    => 'Site Reviews',
			'yet-another-stars-rating/yet-another-stars-rating.php' => 'YASR',
			'wp-customer-reviews/wp-customer-reviews.php'      => 'WP Customer Reviews',
		);

		$active_review = array();
		foreach ( $review_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_review[]    = $plugin_name;
				$earned_points     += 20; // Up to 40 points.
			}
		}

		if ( count( $active_review ) > 0 ) {
			$stats['review_plugins'] = implode( ', ', $active_review );
		} else {
			$issues[] = 'No review management plugins detected';
		}

		// Check for notification/monitoring plugins (30 points).
		$notification_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'                    => 'WP Mail SMTP',
			'email-log/email-log.php'                          => 'Email Log',
			'better-notifications-for-wp/better-notifications-for-wp.php' => 'Better Notifications',
			'notification/notification.php'                    => 'Notification',
		);

		$active_notifications = array();
		foreach ( $notification_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_notifications[] = $plugin_name;
				$earned_points         += 15; // Up to 30 points.
			}
		}

		if ( count( $active_notifications ) > 0 ) {
			$stats['notification_plugins'] = implode( ', ', $active_notifications );
		} else {
			$warnings[] = 'No notification monitoring plugins detected';
		}

		// Check for comment moderation settings (15 points).
		$comment_moderation = get_option( 'comment_moderation', '0' );
		$moderation_notify  = get_option( 'moderation_notify', '1' );

		if ( '1' === $comment_moderation && '1' === $moderation_notify ) {
			$earned_points                  += 15;
			$stats['comment_moderation']     = 'Enabled';
			$stats['moderation_notify']      = 'Enabled';
		} else {
			$warnings[] = 'Comment moderation or notifications not fully enabled';
		}

		// Check for helpdesk/support plugins (15 points).
		$support_plugins = array(
			'awesome-support/awesome-support.php'              => 'Awesome Support',
			'support-ticket-system/support-ticket-system.php'  => 'Support Ticket System',
			'wp-support-plus-responsive-ticket-system/wp-support-plus-responsive-ticket-system.php' => 'WP Support Plus',
		);

		$active_support = array();
		foreach ( $support_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_support[] = $plugin_name;
				$earned_points   += 8; // Up to 15 points.
			}
		}

		if ( count( $active_support ) > 0 ) {
			$stats['support_plugins'] = implode( ', ', $active_support );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 35%.
		if ( $score < 35 ) {
			$severity     = $score < 15 ? 'medium' : 'low';
			$threat_level = $score < 15 ? 40 : 30;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your feedback response system scored %s. Unaddressed negative feedback damages reputation and loses customers. Setting up review monitoring, comment notifications, and support systems helps you respond quickly and turn critics into advocates.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/negative-feedback-response?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
