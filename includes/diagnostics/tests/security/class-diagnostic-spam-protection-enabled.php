<?php
/**
 * Spam Protection Enabled Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 30.
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
 * Spam Protection Enabled Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Spam_Protection_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'spam-protection-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Spam Protection Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Spam Protection Enabled. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check anti-spam plugin activation and API key readiness.
	 *
	 * TODO Fix Plan:
	 * Fix by enabling Akismet/alternative and configuring keys.
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
		// Check WordPress option signatures of common anti-spam plugins.
		$option_indicators = array(
			'wordpress_api_key',  // Akismet (stores the API key)
			'antispam_bee',       // Antispam Bee
			'cleantalk_license',  // CleanTalk
			'wparmour_settings',  // WP Armour (honeypot)
			'bws_spam_options',   // Anti-Spam by BestWebSoft
		);

		foreach ( $option_indicators as $option ) {
			if ( false !== get_option( $option, false ) ) {
				return null;
			}
		}

		// Class-based indicator (faster than option check for loaded plugins).
		$class_indicators = array(
			'Akismet',
			'AntispamBee',
			'CleanTalk',
		);

		foreach ( $class_indicators as $class ) {
			if ( class_exists( $class, false ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No spam protection plugin was detected on your site. Without it, comment sections, contact forms, and user registration pages are vulnerable to bot-submitted spam that clutters your database, wastes storage, and may harm your email reputation.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/spam-protection',
			'details'      => array(
				'note' => __( 'Install Akismet, Antispam Bee, CleanTalk, or a similar plugin to filter spam submissions.', 'wpshadow' ),
			),
		);
	}
}
