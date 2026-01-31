<?php
/**
 * GDPR Cookie Consent Not Implemented Diagnostic
 *
 * Checks if GDPR cookie consent is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Cookie Consent Not Implemented Diagnostic Class
 *
 * Detects missing GDPR cookie consent implementation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_GDPR_Cookie_Consent_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-cookie-consent-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Cookie Consent Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cookie consent is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for cookie consent plugins
		$consent_plugins = array(
			'cookie-consent/cookie-consent.php',
			'cookie-law-info/cookie-law-info.php',
			'gdpr-cookie-compliance/gdpr-cookie-compliance.php',
			'borlabs-cookie/borlabs-cookie.php',
			'iubenda-cookie-solution/iubenda-cookie-solution.php',
		);

		$consent_plugin_active = false;
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$consent_plugin_active = true;
				break;
			}
		}

		if ( ! $consent_plugin_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No cookie consent plugin is active. GDPR compliance requires explicit user consent before tracking cookies are set.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gdpr-cookie-consent-not-implemented',
			);
		}

		return null;
	}
}
