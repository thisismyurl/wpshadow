<?php
/**
 * Site Address URL Diagnostic
 *
 * Verifies the Site Address (URL) setting is correctly configured
 * and matches the home page location where visitors access the site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1745
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Address URL Diagnostic Class
 *
 * Checks that the Site Address URL setting (home) is properly configured.
 *
 * @since 1.26032.1745
 */
class Diagnostic_Site_Address_URL extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-address-url';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Address URL';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site home URL is correct';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$home_url = get_option( 'home' );
		$site_url = get_option( 'siteurl' );

		// Check if URL is set.
		if ( empty( $home_url ) ) {
			$issues[] = __( 'Site Address URL is not configured', 'wpshadow' );
		} else {
			// Check for trailing slash.
			if ( '/' === substr( $home_url, -1 ) ) {
				$issues[] = __( 'Site Address URL has a trailing slash which can cause issues', 'wpshadow' );
			}

			// Check protocol.
			if ( 0 === strpos( $home_url, 'http://' ) ) {
				$issues[] = __( 'Site Address URL uses HTTP instead of HTTPS', 'wpshadow' );
			}

			// Check if WP_HOME constant overrides database value.
			if ( defined( 'WP_HOME' ) ) {
				if ( WP_HOME !== $home_url ) {
					$issues[] = __( 'WP_HOME constant is defined and differs from database value', 'wpshadow' );
				}
			}

			// Check consistency with WordPress Address.
			if ( ! empty( $site_url ) ) {
				$home_protocol = wp_parse_url( $home_url, PHP_URL_SCHEME );
				$site_protocol = wp_parse_url( $site_url, PHP_URL_SCHEME );

				if ( $home_protocol !== $site_protocol ) {
					$issues[] = sprintf(
						/* translators: 1: home URL scheme, 2: site URL scheme */
						__( 'Site Address uses %1$s but WordPress Address uses %2$s', 'wpshadow' ),
						$home_protocol,
						$site_protocol
					);
				}
			}

			// Check for development URLs.
			$dev_patterns = array( 'localhost', '127.0.0.1', '.local', '.test', '.dev' );
			foreach ( $dev_patterns as $pattern ) {
				if ( false !== strpos( $home_url, $pattern ) ) {
					$issues[] = sprintf(
						/* translators: %s: development URL pattern */
						__( 'Site Address URL contains development pattern "%s"', 'wpshadow' ),
						$pattern
					);
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-address-url',
			);
		}

		return null;
	}
}
