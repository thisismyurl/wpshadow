<?php
/**
 * Site Title Configuration
 *
 * Checks if site title is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Site_Title_Configuration Class
 *
 * Validates site title configuration.
 *
 * @since 1.6093.1200
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
	protected static $description = 'Validates site title configuration for SEO and branding';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests site title configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$site_title = get_option( 'blogname', '' );

		// Check 1: Title is not empty
		if ( empty( $site_title ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site title is not configured', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/site-title-configuration',
				'recommendations' => array(
					__( 'Set a clear, descriptive site title', 'wpshadow' ),
					__( 'Include main keywords for SEO', 'wpshadow' ),
					__( 'Keep title under 60 characters', 'wpshadow' ),
					__( 'Make it memorable and brand-related', 'wpshadow' ),
				),
			);
		}

		// Check 2: Title length optimization
		$title_length = strlen( $site_title );
		if ( $title_length < 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site title is too short (less than 5 characters)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-title-length',
				'recommendations' => array(
					__( 'Expand site title to be more descriptive', 'wpshadow' ),
					__( 'Include your business name and main keyword', 'wpshadow' ),
					__( 'Aim for 20-60 characters', 'wpshadow' ),
				),
			);
		}

		if ( $title_length > 75 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site title is too long (over 75 characters)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-title-length',
				'recommendations' => array(
					__( 'Shorten the site title for better display', 'wpshadow' ),
					__( 'Google displays 50-60 characters in search results', 'wpshadow' ),
					__( 'Use tagline option for additional description', 'wpshadow' ),
				),
			);
		}

		// Check 3: Title has keyword diversity
		if ( ! self::has_keyword_diversity( $site_title ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site title could include relevant keywords', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-title-keywords',
				'recommendations' => array(
					__( 'Include main keyword in site title', 'wpshadow' ),
					__( 'Front-load important words', 'wpshadow' ),
					__( 'Example: "Professional Logo Design | Your Company Name"', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for keyword diversity.
	 *
	 * @since 1.6093.1200
	 * @param  string $title Site title.
	 * @return bool True if keywords detected.
	 */
	private static function has_keyword_diversity( $title ) {
		// Check if title contains multiple words
		$words = array_filter( explode( ' ', $title ) );
		if ( count( $words ) >= 2 ) {
			return true;
		}

		// Check for separator indicating category/type
		if ( strpos( $title, '|' ) !== false || strpos( $title, '-' ) !== false ) {
			return true;
		}

		return false;
	}
}
