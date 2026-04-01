<?php
/**
 * WordPress Address URL Diagnostic
 *
 * Verifies the WordPress Address (URL) setting is correctly configured
 * and matches the actual installation location.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Address URL Diagnostic Class
 *
 * Checks that the WordPress Address URL setting (siteurl) is properly configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_WordPress_Address_URL extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-address-url';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Address URL';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress installation URL is correct';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$site_url = get_option( 'siteurl' );

		// Check if URL is set.
		if ( empty( $site_url ) ) {
			$issues[] = __( 'WordPress Address URL is not configured', 'wpshadow' );
		} else {
			// Check for trailing slash (should not have one).
			if ( '/' === substr( $site_url, -1 ) ) {
				$issues[] = __( 'WordPress Address URL has a trailing slash which can cause issues', 'wpshadow' );
			}

			// Check protocol (should use HTTPS in 2026).
			if ( 0 === strpos( $site_url, 'http://' ) ) {
				$issues[] = __( 'WordPress Address URL uses HTTP instead of HTTPS', 'wpshadow' );
			}

			// Check if WP_SITEURL constant overrides database value.
			if ( defined( 'WP_SITEURL' ) ) {
				if ( WP_SITEURL !== $site_url ) {
					$issues[] = __( 'WP_SITEURL constant is defined and differs from database value', 'wpshadow' );
				}
			}

			// Check for localhost/development URLs in production.
			$dev_patterns = array( 'localhost', '127.0.0.1', '.local', '.test', '.dev' );
			foreach ( $dev_patterns as $pattern ) {
				if ( false !== strpos( $site_url, $pattern ) ) {
					$issues[] = sprintf(
						/* translators: %s: development URL pattern */
						__( 'WordPress Address URL contains development pattern "%s"', 'wpshadow' ),
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
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-address-url?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
