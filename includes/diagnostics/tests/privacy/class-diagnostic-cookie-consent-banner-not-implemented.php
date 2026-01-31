<?php
/**
 * Cookie Consent Banner Not Implemented Diagnostic
 *
 * Checks if cookie consent banner is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Consent Banner Not Implemented Diagnostic Class
 *
 * Detects missing cookie consent banner.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Cookie_Consent_Banner_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-consent-banner-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Banner Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cookie consent banner is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for cookie consent plugins
		$consent_plugins = array(
			'cookiebot/cookiebot.php',
			'cookie-law-info/cookie-law-info.php',
			'cookie-notice/cookie-notice.php',
		);

		$consent_active = false;
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$consent_active = true;
				break;
			}
		}

		if ( ! $consent_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Cookie consent banner is not implemented. Add a GDPR-compliant cookie consent banner to your site.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cookie-consent-banner-not-implemented',
			);
		}

		return null;
	}
}
