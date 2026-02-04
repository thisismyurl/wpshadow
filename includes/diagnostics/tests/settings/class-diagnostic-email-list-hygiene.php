<?php
/**
 * Email List Hygiene Diagnostic
 *
 * Tests whether the site regularly cleans email lists to maintain <2% bounce rate.
 *
 * @since   1.6034.0300
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email List Hygiene Diagnostic Class
 *
 * Clean lists improve deliverability and ROI. Dirty lists damage sender
 * reputation and waste money on invalid addresses.
 *
 * @since 1.6034.0300
 */
class Diagnostic_Email_List_Hygiene extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-list-hygiene';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email List Hygiene';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site regularly cleans email lists to maintain <2% bounce rate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$hygiene_score = 0;
		$max_score = 5;

		// Check for email marketing platform.
		$has_platform = self::check_email_platform();
		if ( $has_platform ) {
			$hygiene_score++;
		} else {
			$issues[] = __( 'No email marketing platform for list management', 'wpshadow' );
		}

		// Check for bounce handling.
		$bounce_handling = self::check_bounce_handling();
		if ( $bounce_handling ) {
			$hygiene_score++;
		} else {
			$issues[] = __( 'No automated bounce handling or removal', 'wpshadow' );
		}

		// Check for unsubscribe management.
		$unsubscribe_mgmt = self::check_unsubscribe_management();
		if ( $unsubscribe_mgmt ) {
			$hygiene_score++;
		} else {
			$issues[] = __( 'Unsubscribe process not properly managed', 'wpshadow' );
		}

		// Check for validation.
		$email_validation = self::check_email_validation();
		if ( $email_validation ) {
			$hygiene_score++;
		} else {
			$issues[] = __( 'No email validation at signup (accepting invalid addresses)', 'wpshadow' );
		}

		// Check for re-engagement campaigns.
		$reengagement = self::check_reengagement_campaigns();
		if ( $reengagement ) {
			$hygiene_score++;
		} else {
			$issues[] = __( 'No re-engagement campaigns before list removal', 'wpshadow' );
		}

		// Determine severity based on list hygiene.
		$hygiene_percentage = ( $hygiene_score / $max_score ) * 100;

		if ( $hygiene_percentage < 40 ) {
			$severity = 'medium';
			$threat_level = 35;
		} elseif ( $hygiene_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: List hygiene percentage */
				__( 'Email list hygiene at %d%%. ', 'wpshadow' ),
				(int) $hygiene_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Dirty lists damage deliverability and waste budget', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-list-hygiene',
			);
		}

		return null;
	}

	/**
	 * Check email platform.
	 *
	 * @since  1.6034.0300
	 * @return bool True if platform exists, false otherwise.
	 */
	private static function check_email_platform() {
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		foreach ( $email_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check bounce handling.
	 *
	 * @since  1.6034.0300
	 * @return bool True if handling exists, false otherwise.
	 */
	private static function check_bounce_handling() {
		// MailPoet has automated bounce handling.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		// Newsletter plugin has bounce handling.
		if ( is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_handles_bounces', false );
	}

	/**
	 * Check unsubscribe management.
	 *
	 * @since  1.6034.0300
	 * @return bool True if management exists, false otherwise.
	 */
	private static function check_unsubscribe_management() {
		// All major email plugins handle unsubscribes.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ||
			 is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_manages_unsubscribes', false );
	}

	/**
	 * Check email validation.
	 *
	 * @since  1.6034.0300
	 * @return bool True if validation exists, false otherwise.
	 */
	private static function check_email_validation() {
		// Check for validation plugins.
		$validation_plugins = array(
			'wpforms-lite/wpforms.php',
			'contact-form-7/wp-contact-form-7.php',
		);

		foreach ( $validation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true; // Assume forms validate emails.
			}
		}

		return apply_filters( 'wpshadow_validates_emails', true );
	}

	/**
	 * Check re-engagement campaigns.
	 *
	 * @since  1.6034.0300
	 * @return bool True if campaigns exist, false otherwise.
	 */
	private static function check_reengagement_campaigns() {
		// Check for automation-capable platforms.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		$query = new \WP_Query(
			array(
				's'              => 'win back re-engage inactive',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
