<?php
/**
 * Multisite Registration Anti-Spam Protection Diagnostic
 *
 * Verifies network registration has anti-spam measures
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Multisite
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_MultisiteRegistrationAntispam Class
 *
 * Checks for: CAPTCHA, email verification, banned domains
 *
 * @since 1.6031.1445
 */
class Diagnostic_MultisiteRegistrationAntispam extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-registration-antispam';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Registration Anti-Spam Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies network registration has anti-spam measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'captcha', 'recaptcha', 'anti-spam' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: Email verification not required

		// Additional checks would go here for: No banned domain list

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Registration spam concerns: %s. Multisite networks need anti-spam protection.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-registration-antispam',
		);
	}
}
