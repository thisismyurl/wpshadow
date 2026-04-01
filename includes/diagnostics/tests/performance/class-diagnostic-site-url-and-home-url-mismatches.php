<?php
/**
 * Site URL and Home URL Mismatches Diagnostic
 *
 * Tests for site URL and home URL configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site URL and Home URL Mismatches Diagnostic Class
 *
 * Tests for site URL and home URL consistency.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Site_URL_And_Home_URL_Mismatches extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-url-and-home-url-mismatches';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site URL and Home URL Mismatches';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for site URL and home URL configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get both URLs.
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );

		// Check if URLs are empty.
		if ( empty( $site_url ) ) {
			$issues[] = __( 'Site URL is not set', 'wpshadow' );
		}

		if ( empty( $home_url ) ) {
			$issues[] = __( 'Home URL is not set', 'wpshadow' );
		}

		// Check if URLs match.
		if ( ! empty( $site_url ) && ! empty( $home_url ) && $site_url !== $home_url ) {
			$issues[] = sprintf(
				/* translators: %s: site URL, %s: home URL */
				__( 'Site URL and Home URL do not match: %s vs %s - this is intentional for site in subdirectory', 'wpshadow' ),
				$site_url,
				$home_url
			);
		}

		// Check for protocol mismatch (http vs https).
		if ( ! empty( $site_url ) && ! empty( $home_url ) ) {
			$site_protocol = Diagnostic_URL_And_Pattern_Helper::get_scheme( $site_url );
			$home_protocol = Diagnostic_URL_And_Pattern_Helper::get_scheme( $home_url );

			if ( $site_protocol !== $home_protocol ) {
				$issues[] = sprintf(
					/* translators: %s: site protocol, %s: home protocol */
					__( 'Protocol mismatch between Site URL (%s) and Home URL (%s)', 'wpshadow' ),
					$site_protocol,
					$home_protocol
				);
			}
		}

		// Check for trailing slash mismatch.
		if ( ! empty( $site_url ) && ! empty( $home_url ) && $site_url !== $home_url ) {
			$site_has_slash = substr( $site_url, -1 ) === '/';
			$home_has_slash = substr( $home_url, -1 ) === '/';

			if ( $site_has_slash !== $home_has_slash ) {
				$issues[] = __( 'Trailing slash mismatch between Site URL and Home URL', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-url-and-home-url-mismatches?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
