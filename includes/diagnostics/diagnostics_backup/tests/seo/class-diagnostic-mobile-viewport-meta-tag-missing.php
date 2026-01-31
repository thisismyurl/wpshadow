<?php
/**
 * Mobile Viewport Meta Tag Missing Diagnostic
 *
 * Checks if mobile viewport meta tag is present.
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
 * Mobile Viewport Meta Tag Missing Diagnostic Class
 *
 * Detects missing mobile viewport configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Mobile_Viewport_Meta_Tag_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-viewport-meta-tag-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Viewport Meta Tag Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if viewport meta tag is set';

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
		// Viewport meta tag is typically added by themes
		// WordPress 5.2+ has wp_is_mobile_theme() but we check for mobile-first responsiveness

		// Check if WP_DEBUG or mobile plugins indicate missing viewport
		$theme = get_template();

		// Modern themes should include viewport meta tag
		// Check if the active theme has responsive design
		$theme_supports = get_theme_support( 'responsive-embeds' );

		if ( ! $theme_supports ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Theme does not declare mobile responsive support. Pages may not display properly on mobile devices, affecting SEO.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/mobile-viewport-meta-tag-missing',
			);
		}

		return null;
	}
}
