<?php
/**
 * Tagline Optimization
 *
 * Checks if site tagline is optimized for SEO and usability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tagline_Optimization Class
 *
 * Validates site tagline for SEO and branding effectiveness.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Tagline_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tagline-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tagline Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates site tagline for SEO and branding effectiveness';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests tagline configuration and optimization.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$tagline = get_option( 'blogdescription', '' );

		// Check 1: Tagline is configured
		if ( empty( $tagline ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site tagline is not configured', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/site-tagline-optimization',
				'recommendations' => array(
					__( 'Create a compelling site tagline', 'wpshadow' ),
					__( 'Describe what your site is about in one sentence', 'wpshadow' ),
					__( 'Include relevant keywords naturally', 'wpshadow' ),
					__( 'Keep it under 160 characters', 'wpshadow' ),
				),
			);
		}

		// Check 2: Tagline length optimization
		$tagline_length = strlen( $tagline );
		if ( $tagline_length < 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site tagline is too short to be effective', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tagline-length',
				'recommendations' => array(
					__( 'Expand tagline to be more descriptive', 'wpshadow' ),
					__( 'Aim for 50-160 characters', 'wpshadow' ),
					__( 'Explain your value proposition', 'wpshadow' ),
				),
			);
		}

		if ( $tagline_length > 160 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site tagline is too long', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tagline-length',
				'recommendations' => array(
					__( 'Shorten the tagline for better impact', 'wpshadow' ),
					__( 'Focus on main value proposition', 'wpshadow' ),
					__( 'Ideal length: 50-160 characters', 'wpshadow' ),
				),
			);
		}

		// Check 3: Tagline matches theme
		if ( ! self::tagline_matches_context( $tagline ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Tagline may be generic WordPress default', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tagline-customization',
				'recommendations' => array(
					__( 'Create unique, branded tagline', 'wpshadow' ),
					__( 'Avoid generic phrases like "Just another WordPress site"', 'wpshadow' ),
					__( 'Make it specific to your business or niche', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if tagline matches site context.
	 *
	 * @since 1.6093.1200
	 * @param  string $tagline Site tagline.
	 * @return bool True if tagline is custom/branded.
	 */
	private static function tagline_matches_context( $tagline ) {
		// Check for generic WordPress defaults
		$generic_phrases = array(
			'just another wordpress',
			'wordpress theme',
			'powered by wordpress',
			'another site powered by',
		);

		$tagline_lower = strtolower( $tagline );

		foreach ( $generic_phrases as $phrase ) {
			if ( strpos( $tagline_lower, $phrase ) !== false ) {
				return false;
			}
		}

		return true;
	}
}
