<?php
/**
 * Mobile Menu Diagnostic
 *
 * Over 60% of web traffic is from mobile devices. A site without a
 * usable mobile navigation makes it difficult or impossible for mobile
 * visitors to discover content. This diagnostic looks for the common
 * patterns that indicate responsive/hamburger menu support in the theme.
 *
 * @package WPShadow
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
 * Diagnostic_Mobile_Menu Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Menu extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-menu';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Menu';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks the active theme for responsive navigation patterns (mobile menu toggle buttons or media-query-driven nav styles) so mobile visitors can browse the site.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Class names commonly used by mobile menu toggle buttons.
	 */
	private const TOGGLE_CLASSES = array(
		'menu-toggle',
		'nav-toggle',
		'hamburger',
		'mobile-nav',
		'mobile-menu',
		'toggle-menu',
		'navbar-toggle',
		'navbar-toggler',
		'offcanvas-toggle',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Scans the active theme's PHP and JS files for mobile menu patterns:
	 * toggle button classes or JavaScript that manages visible/hidden nav
	 * states. Also checks CSS for min/max-width media queries that target
	 * navigation elements.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$stylesheet_dir = get_stylesheet_directory();
		$template_dir   = get_template_directory();

		$dirs = array_unique(
			array_filter( array( $stylesheet_dir, $template_dir ), 'is_dir' )
		);

		$has_toggle_in_php = false;
		$has_toggle_in_js  = false;
		$has_responsive_css = false;

		$toggle_pattern_php = implode( '|', array_map( 'preg_quote', self::TOGGLE_CLASSES ) );
		$toggle_pattern_js  = $toggle_pattern_php;

		foreach ( $dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
			} catch ( \Exception $e ) {
				continue;
			}

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() ) {
					continue;
				}

				$ext     = strtolower( $file->getExtension() );
				$content = file_get_contents( $file->getPathname() );
				if ( false === $content ) {
					continue;
				}

				if ( 'php' === $ext ) {
					if ( preg_match( '/(' . $toggle_pattern_php . ')/i', $content ) ) {
						$has_toggle_in_php = true;
					}
				} elseif ( 'js' === $ext ) {
					if ( preg_match( '/(' . $toggle_pattern_js . ')/i', $content ) ) {
						$has_toggle_in_js = true;
					}
				} elseif ( 'css' === $ext ) {
					// Check for a media query containing nav/menu styles.
					if ( preg_match( '/@media[^{]+(?:max-width|min-width)[^{]+\{[^}]*(?:nav|menu|navigation)/is', $content ) ) {
						$has_responsive_css = true;
					}
				}

				if ( $has_toggle_in_php || $has_toggle_in_js || $has_responsive_css ) {
					return null;
				}
			}
		}

		if ( $has_toggle_in_php || $has_toggle_in_js || $has_responsive_css ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No responsive mobile menu patterns were detected in the active theme. Mobile visitors may encounter a full desktop navigation layout that is difficult to use on small screens.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'kb_link'      => '',
			'details'      => array(
				'fix' => __( 'If your theme lacks a mobile menu, consider switching to a theme that includes responsive navigation, or install a mobile menu plugin such as "WP Responsive Menu". Ensure the toggle button has a visible label and is keyboard-accessible.', 'wpshadow' ),
			),
		);
	}
}
