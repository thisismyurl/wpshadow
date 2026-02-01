<?php
/**
 * Site Title Configuration Diagnostic
 *
 * Verifies that the site title (blog name) is properly configured with appropriate
 * length and content for SEO and branding purposes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Title Configuration Diagnostic Class
 *
 * Ensures site title is configured and meets SEO best practices.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Site_Title_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-title-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Title Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site title is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Site title is set and not empty
	 * - Title length is reasonable (40-60 chars recommended)
	 * - Title is not just default WordPress text
	 * - Title is not too generic
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get site title.
		$site_title = get_option( 'blogname', '' );

		// Check if title is set.
		if ( empty( $site_title ) ) {
			$issues[] = __( 'Site title (blog name) is not configured', 'wpshadow' );
		} else {
			// Check title length.
			$title_length = strlen( $site_title );

			if ( $title_length < 3 ) {
				$issues[] = __( 'Site title is too short (minimum 3 characters recommended)', 'wpshadow' );
			} elseif ( $title_length > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: current title length */
					__( 'Site title is too long (%d characters); search engines truncate titles over 60 characters', 'wpshadow' ),
					$title_length
				);
			}

			// Check if title is just default text.
			$default_titles = array( 'just another wordpress site', 'wordpress', 'my blog', 'my site', 'blog' );
			if ( in_array( strtolower( $site_title ), $default_titles, true ) ) {
				$issues[] = __( 'Site title appears to be the default WordPress text; please customize it', 'wpshadow' );
			}

			// Check if title contains special characters that might confuse browsers.
			if ( preg_match( '/[<>"\']/', $site_title ) ) {
				$issues[] = __( 'Site title contains special characters that may cause display issues', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/site-title-configuration',
			);
		}

		return null;
	}
}
