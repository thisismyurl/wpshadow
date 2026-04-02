<?php
/**
 * Mail Sender Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 94.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mail Sender Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mail_Sender extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mail-sender';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mail Sender';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress outgoing mail sender name and email address have been customized from the generic WordPress defaults.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check wp_mail_from/wp_mail_from_name values.
	 *
	 * TODO Fix Plan:
	 * Fix by setting consistent sender identity.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// Get the sender name and email that WordPress would use for outgoing mail.
		// We probe via apply_filters() so active SMTP plugins and customisations are included.
		$default_email = 'wordpress@' . strtolower( preg_replace( '/^www\./', '', wp_parse_url( home_url(), PHP_URL_HOST ) ?? '' ) );
		$default_name  = 'WordPress';

		$active_email = apply_filters( 'wp_mail_from', $default_email );
		$active_name  = apply_filters( 'wp_mail_from_name', $default_name );

		// If both have been customised, sender identity is configured.
		if ( $active_email !== $default_email && $active_name !== $default_name ) {
			return null;
		}

		$issues = array();
		if ( $active_name === $default_name ) {
			$issues[] = __( 'Sender name is still the default "WordPress"', 'wpshadow' );
		}
		if ( $active_email === $default_email ) {
			$issues[] = sprintf(
				/* translators: %s: the default from email address */
				__( 'Sender email is the WordPress default (%s)', 'wpshadow' ),
				$active_email
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress is sending email with its default sender identity. The default "wordpress@yoursite.com" address is often flagged as spam and provides no branding. Set a custom sender name and email address via an SMTP plugin or the wp_mail_from / wp_mail_from_name filters.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mail-sender',
			'details'      => array(
				'active_email' => $active_email,
				'active_name'  => $active_name,
				'issues'       => $issues,
			),
		);
	}
}
