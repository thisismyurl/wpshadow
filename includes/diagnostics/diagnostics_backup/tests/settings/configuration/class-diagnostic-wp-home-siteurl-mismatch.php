<?php
/**
 * Diagnostic: WP_HOME/WP_SITEURL Mismatch
 *
 * Checks if WP_HOME and WP_SITEURL are configured consistently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Home_Siteurl_Mismatch
 *
 * Tests for configuration mismatches between WP_HOME and WP_SITEURL.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Home_Siteurl_Mismatch extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-home-siteurl-mismatch';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP_HOME/WP_SITEURL Mismatch';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WP_HOME and WP_SITEURL are configured consistently';

	/**
	 * Check for WP_HOME/WP_SITEURL mismatch.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$wp_home    = get_option( 'home' );
		$wp_siteurl = get_option( 'siteurl' );

		if ( $wp_home !== $wp_siteurl ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WP_HOME and WP_SITEURL are mismatched. This can cause redirect loops, broken assets, and login issues. Ensure both URLs point to the same domain and protocol.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp_home_siteurl_mismatch',
				'meta'        => array(
					'wp_home'    => $wp_home,
					'wp_siteurl' => $wp_siteurl,
				),
			);
		}

		// Check if both use HTTPS if site is using SSL.
		if ( is_ssl() ) {
			if ( ! str_starts_with( $wp_home, 'https://' ) || ! str_starts_with( $wp_siteurl, 'https://' ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'Site uses HTTPS but WP_HOME or WP_SITEURL is not set to HTTPS. This causes mixed content warnings. Update both to use https://', 'wpshadow' ),
					'severity'    => 'medium',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/wp_home_siteurl_mismatch',
					'meta'        => array(
						'wp_home'    => $wp_home,
						'wp_siteurl' => $wp_siteurl,
						'is_ssl'     => true,
					),
				);
			}
		}

		return null;
	}
}
