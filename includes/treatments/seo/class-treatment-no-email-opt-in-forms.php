<?php
/**
 * No Email Opt-in Forms Treatment
 *
 * Detects missing email capture forms, losing opportunities to
 * build audience and drive conversions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Engagement
 * @since      1.6034.2216
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Email Opt-in Forms Treatment Class
 *
 * Checks for email list building mechanisms to ensure visitor
 * capture and ongoing engagement opportunities.
 *
 * **Why This Matters:**
 * - Email has 40x higher conversion than social
 * - You own your email list (platform independent)
 * - Email ROI: $42 for every $1 spent
 * - Visitors leave and never return without capture
 * - Email enables ongoing relationships
 *
 * **Email Capture Best Practices:**
 * - Popup after 30 seconds or scroll 50%
 * - Inline forms mid-content
 * - Exit-intent popups
 * - Content upgrades (lead magnets)
 * - Welcome bar at top
 *
 * @since 1.6034.2216
 */
class Treatment_No_Email_Opt_In_Forms extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-opt-in-forms';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Email Opt-in Forms';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Missing email capture forms, losing opportunities to build your audience';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2216
	 * @return array|null Finding array if no email forms detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Email_Opt_In_Forms' );
	}

	/**
	 * Check if email marketing plugin is installed
	 *
	 * @since  1.6034.2216
	 * @return bool True if plugin detected, false otherwise.
	 */
	private static function has_email_marketing_plugin() {
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailpoet/mailpoet.php',
			'convertkit/wp-convertkit.php',
			'newsletter/plugin.php',
			'email-subscribers/email-subscribers.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if email form exists in widgets
	 *
	 * @since  1.6034.2216
	 * @return bool True if email widget found, false otherwise.
	 */
	private static function has_email_form_widget() {
		$sidebars = wp_get_sidebars_widgets();

		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( empty( $widgets ) || 'wp_inactive_widgets' === $sidebar ) {
				continue;
			}

			foreach ( $widgets as $widget ) {
				// Check for common email widget prefixes
				if ( strpos( $widget, 'mc4wp' ) === 0 ||
				     strpos( $widget, 'mailpoet' ) === 0 ||
				     strpos( $widget, 'newsletter' ) === 0 ) {
					return true;
				}
			}
		}

		return false;
	}
}
