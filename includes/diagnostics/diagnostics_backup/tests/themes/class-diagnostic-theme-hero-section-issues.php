<?php
/**
 * Theme Hero Section Issues Diagnostic
 *
 * Detects hero section/slider performance problems that impact page load
 * and Largest Contentful Paint metrics.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2203
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Hero Section Issues Diagnostic Class
 *
 * Checks for:
 * - Large unoptimized hero images
 * - Missing lazy-loading attributes
 * - Slider plugins with performance issues
 * - Excessive autoplay animations
 * - Missing image dimension attributes
 *
 * @since 1.2601.2203
 */
class Diagnostic_Theme_Hero_Section_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-hero-section-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Hero Section Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hero section/slider performance problems';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2203
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Fetch homepage HTML.
		$response = wp_remote_get( home_url() );
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for slider plugins.
		$slider_plugins = array(
			'revolution-slider' => 'Slider Revolution',
			'layerslider'       => 'LayerSlider',
			'metaslider'        => 'MetaSlider',
			'smart-slider'      => 'Smart Slider',
			'soliloquy'         => 'Soliloquy',
		);

		$active_sliders = array();
		foreach ( $slider_plugins as $class => $name ) {
			if ( strpos( $html, $class ) !== false ) {
				$active_sliders[] = $name;
			}
		}

		if ( ! empty( $active_sliders ) ) {
			$issues[] = sprintf(
				__( 'Slider plugins detected: %s (can impact LCP and CLS)', 'wpshadow' ),
				implode( ', ', $active_sliders )
			);
		}

		// Check for large hero images without lazy loading.
		preg_match_all( '/<img[^>]+class="[^"]*hero[^"]*"[^>]+>/i', $html, $hero_images );
		if ( ! empty( $hero_images[0] ) ) {
			foreach ( $hero_images[0] as $img_tag ) {
				// Check for lazy loading.
				if ( strpos( $img_tag, 'loading=' ) === false ) {
					$issues[] = __( 'Hero image missing lazy-loading attribute', 'wpshadow' );
					break;
				}

				// Check for dimensions.
				if ( strpos( $img_tag, 'width=' ) === false || strpos( $img_tag, 'height=' ) === false ) {
					$issues[] = __( 'Hero image missing width/height attributes (causes CLS)', 'wpshadow' );
					break;
				}
			}
		}

		// Check for autoplay videos in hero.
		if ( strpos( $html, 'autoplay' ) !== false && strpos( $html, '<video' ) !== false ) {
			$issues[] = __( 'Autoplay video detected in hero section (impacts performance and accessibility)', 'wpshadow' );
		}

		// Check for multiple background images.
		preg_match_all( '/background(-image)?\s*:\s*url\(/i', $html, $bg_images );
		if ( isset( $bg_images[0] ) && count( $bg_images[0] ) > 3 ) {
			$issues[] = sprintf(
				__( 'Excessive background images detected: %d (recommended: 3 or fewer)', 'wpshadow' ),
				count( $bg_images[0] )
			);
		}

		// Check for parallax effects.
		if ( strpos( $html, 'parallax' ) !== false ) {
			$issues[] = __( 'Parallax effect detected (can cause jank and poor mobile performance)', 'wpshadow' );
		}

		// Check for heavy animation libraries.
		$animation_libs = array(
			'aos.js'            => 'AOS (Animate On Scroll)',
			'wow.js'            => 'WOW.js',
			'animate.css'       => 'Animate.css',
			'gsap'              => 'GSAP',
			'velocity'          => 'Velocity.js',
		);

		$active_animations = array();
		foreach ( $animation_libs as $lib => $name ) {
			if ( stripos( $html, $lib ) !== false ) {
				$active_animations[] = $name;
			}
		}

		if ( count( $active_animations ) > 1 ) {
			$issues[] = sprintf(
				__( 'Multiple animation libraries loaded: %s (consider consolidating)', 'wpshadow' ),
				implode( ', ', $active_animations )
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/hero-section-optimization',
		);
	}
}
