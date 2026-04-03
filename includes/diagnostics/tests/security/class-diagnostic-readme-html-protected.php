<?php
/**
 * Readme HTML Protected Diagnostic
 *
 * WordPress ships a readme.html file in the webroot that prints the exact
 * WordPress version number in its page title. Unlike the generator meta tag,
 * this file is unaffected by remove_action() or SEO plugin settings — it is
 * a static file that sits on disk. Vulnerability scanners actively request
 * /readme.html to fingerprint WordPress targets without needing to parse HTML.
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
 * Diagnostic_Readme_Html_Protected Class
 *
 * Sends a HEAD request to {home_url}/readme.html with redirect following
 * disabled. A direct 200 response means the file is publicly accessible.
 * Any redirect, 404, 403, or request failure is treated as healthy.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Readme_Html_Protected extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'readme-html-protected';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'readme.html Not Publicly Accessible';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the WordPress readme.html file is not publicly accessible. The file prints the exact WordPress version number and is actively sought by automated vulnerability scanners.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'low';

	/**
	 * Run the diagnostic check.
	 *
	 * Sends a single HEAD request to {home_url}/readme.html with redirect
	 * following disabled. A 200 response code indicates direct accessibility.
	 * Any other status (redirect, 403, 404) or a request error is treated as
	 * healthy to avoid false positives on sites with custom routing.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when readme.html is accessible, null when protected.
	 */
	public static function check() {
		$url = home_url( '/readme.html' );

		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => 7,
				'redirection' => 0, // Do not follow redirects — a redirect means the file is not directly served.
				'user-agent'  => 'WPShadow-Diagnostic/1.0',
				'sslverify'   => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Cannot connect — skip to avoid false positives.
		}

		$status = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The WordPress readme.html file is publicly accessible at /readme.html. This file includes the exact WordPress version number in its title, giving automated scanners a precise fingerprint to match against known vulnerabilities. The wp-generator-tag diagnostic covers the meta tag version disclosure separately — this is a distinct, complementary check.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'kb_link'      => 'https://wpshadow.com/kb/readme-html-protected?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'accessible_url' => $url,
				'fix'            => __( 'Delete or rename /readme.html, or block access via server rules. Apache: add <Files "readme.html"><IfModule mod_authz_core.c>Require all denied</IfModule></Files> to .htaccess. Nginx: add location = /readme.html { deny all; return 404; } to your server block. Some security plugins (Wordfence, iThemes Security) can block this automatically.', 'wpshadow' ),
			),
		);
	}
}
