<?php
/**
 * HTTPS Enabled Diagnostic (Stub)
 *
 * TODO stub mapped to the security gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Https_Enabled Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Https_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'https-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'HTTPS Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for HTTPS Enabled';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check wp_is_using_https() and option URLs.
	 *
	 * TODO Fix Plan:
	 * - Enable HTTPS settings and enforce secure URLs.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( WP_Settings::is_site_url_https() && WP_Settings::is_home_url_https() ) {
			return null;
		}

		$issues = array();
		if ( ! WP_Settings::is_site_url_https() ) {
			$issues[] = sprintf(
				/* translators: %s: WordPress Address URL */
				__( 'WordPress Address (siteurl) is "%s" — not using HTTPS.', 'wpshadow' ),
				WP_Settings::get_wp_address()
			);
		}
		if ( ! WP_Settings::is_home_url_https() ) {
			$issues[] = sprintf(
				/* translators: %s: Site Address URL */
				__( 'Site Address (home) is "%s" — not using HTTPS.', 'wpshadow' ),
				WP_Settings::get_home_address()
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site is not configured to serve pages over HTTPS. All data exchanged between your visitors and the server — including login credentials, contact form submissions, and payment details — is transmitted unencrypted. Install an SSL certificate and update both URL settings to https://.', 'wpshadow' ),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/https-enabled',
			'details'      => array(
				'issues'        => $issues,
				'site_url'      => WP_Settings::get_wp_address(),
				'home_url'      => WP_Settings::get_home_address(),
			),
		);
	}
}
