<?php
/**
 * Site URLs Configured Correctly Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
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
 * Diagnostic_Site_Urls_Configured_Correctly Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Site_Urls_Configured_Correctly extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'site-urls-configured-correctly';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Site URLs Configured Correctly';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Site URLs Configured Correctly';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Validate siteurl/home with get_option().
	 *
	 * TODO Fix Plan:
	 * - Repair via update_option() and URL normalization.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$wp_address   = WP_Settings::get_wp_address();
		$home_address = WP_Settings::get_home_address();
		$issues       = array();

		if ( ! WP_Settings::is_site_url_https() ) {
			$issues[] = sprintf(
				/* translators: %s: WordPress Address URL */
				__( 'WordPress Address (URL) "%s" does not use HTTPS.', 'wpshadow' ),
				$wp_address
			);
		}

		if ( ! WP_Settings::is_home_url_https() ) {
			$issues[] = sprintf(
				/* translators: %s: Site Address URL */
				__( 'Site Address (URL) "%s" does not use HTTPS.', 'wpshadow' ),
				$home_address
			);
		}

		// Check that both URLs resolve to the same host to avoid split-brain configs.
		$wp_host   = wp_parse_url( $wp_address, PHP_URL_HOST );
		$home_host = wp_parse_url( $home_address, PHP_URL_HOST );
		if ( $wp_host && $home_host && $wp_host !== $home_host ) {
			$issues[] = sprintf(
				/* translators: %1$s: WP host, %2$s: home host */
				__( 'WordPress Address host (%1$s) and Site Address host (%2$s) do not match.', 'wpshadow' ),
				$wp_host,
				$home_host
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'One or more WordPress URL settings are misconfigured. Using HTTP instead of HTTPS exposes your admin credentials and visitor data. Mismatched host names can break redirects and canonical tags.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/site-urls-configured-correctly',
			'details'      => array(
				'issues'        => $issues,
				'wordpress_url' => $wp_address,
				'site_url'      => $home_address,
			),
		);
	}
}
