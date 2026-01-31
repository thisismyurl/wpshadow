<?php
/**
 * Robots.txt Missing or Misconfigured Diagnostic
 *
 * Checks if robots.txt is properly configured.
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
 * Robots.txt Missing or Misconfigured Diagnostic Class
 *
 * Detects missing or invalid robots.txt files.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Robots_Txt_Missing_Or_Misconfigured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'robots-txt-missing-or-misconfigured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Robots.txt Missing or Misconfigured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if robots.txt is accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$site_url = get_option( 'siteurl', '' );
		if ( empty( $site_url ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Site URL is not configured. Cannot verify robots.txt.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/robots-txt-missing-or-misconfigured',
			);
		}

		// Check if robots.txt is accessible
		$response = wp_remote_head( trailingslashit( $site_url ) . 'robots.txt' );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'robots.txt file is not accessible. Search engines may have difficulty crawling your site properly.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/robots-txt-missing-or-misconfigured',
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 404 === $code || 403 === $code ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'robots.txt returned HTTP %d. Search engines cannot read crawl directives.', 'wpshadow' ),
					absint( $code )
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/robots-txt-missing-or-misconfigured',
			);
		}

		return null;
	}
}
