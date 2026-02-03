<?php
/**
 * Cumulative Layout Shift (CLS) Diagnostic
 *
 * Measures Cumulative Layout Shift for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2060
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cumulative Layout Shift Diagnostic Class
 *
 * Measures factors causing CLS (Cumulative Layout Shift).
 * CLS measures visual stability during page load.
 *
 * @since 1.26033.2060
 */
class Diagnostic_Cumulative_Layout_Shift extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cumulative-layout-shift';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cumulative Layout Shift (CLS)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Cumulative Layout Shift (Core Web Vital)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks factors causing layout shifts:
	 * - Images without dimensions
	 * - Web fonts without font-display
	 * - Dynamic content injection
	 * - Ads without reserved space
	 *
	 * Thresholds:
	 * - Good: <0.1
	 * - Needs Improvement: 0.1-0.25
	 * - Poor: >0.25
	 *
	 * @since  1.26033.2060
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$score  = 0;
		
		// Check for images without dimensions in theme
		$theme_supports_responsive = current_theme_supports( 'html5', 'style' );
		
		if ( ! add_filter( 'wp_img_tag_add_width_and_height_attr', '__return_true' ) ) {
			$issues[] = __( 'WordPress not adding width/height to images', 'wpshadow' );
			$score += 25;
		}
		
		// Check if featured image has dimensions
		if ( is_singular() && has_post_thumbnail() ) {
			$thumbnail_id = get_post_thumbnail_id();
			$image_meta   = wp_get_attachment_metadata( $thumbnail_id );
			
			if ( empty( $image_meta['width'] ) || empty( $image_meta['height'] ) ) {
				$issues[] = __( 'Featured image missing dimension metadata', 'wpshadow' );
				$score += 20;
			}
		}
		
		// Check for web fonts without font-display
		$theme_dir = get_stylesheet_directory();
		$css_files = glob( $theme_dir . '/{style,*.css}', GLOB_BRACE );
		$font_issues = 0;
		
		if ( $css_files ) {
			foreach ( $css_files as $css_file ) {
				if ( ! file_exists( $css_file ) ) {
					continue;
				}
				
				$content = file_get_contents( $css_file );
				
				// Count @font-face without font-display
				preg_match_all( '/@font-face\s*{([^}]*)}/s', $content, $matches );
				if ( ! empty( $matches[1] ) ) {
					foreach ( $matches[1] as $font_face ) {
						if ( strpos( $font_face, 'font-display' ) === false ) {
							$font_issues++;
						}
					}
				}
			}
		}
		
		if ( $font_issues > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of web fonts */
				__( '%d web fonts without font-display (causes invisible text flash)', 'wpshadow' ),
				$font_issues
			);
			$score += 20;
		}
		
		// Check for embeds that might shift layout
		global $wp_scripts;
		$has_embeds = false;
		
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && isset( $script->src ) ) {
					$src_lower = strtolower( $script->src );
					if ( strpos( $src_lower, 'embed' ) !== false || 
					     strpos( $src_lower, 'twitter' ) !== false ||
					     strpos( $src_lower, 'facebook' ) !== false ||
					     strpos( $src_lower, 'instagram' ) !== false ) {
						$has_embeds = true;
						break;
					}
				}
			}
		}
		
		if ( $has_embeds ) {
			$issues[] = __( 'Social media embeds detected (reserve space to prevent shifts)', 'wpshadow' );
			$score += 15;
		}
		
		// Check for ads
		$ad_scripts = array( 'adsense', 'doubleclick', 'adthrive', 'mediavine', 'ezoic' );
		$has_ads    = false;
		
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && isset( $script->src ) ) {
					foreach ( $ad_scripts as $ad_keyword ) {
						if ( stripos( $script->src, $ad_keyword ) !== false ) {
							$has_ads = true;
							break 2;
						}
					}
				}
			}
		}
		
		if ( $has_ads ) {
			$issues[] = __( 'Ad scripts detected (use min-height to reserve space)', 'wpshadow' );
			$score += 25;
		}
		
		// Check for dynamically injected content
		$active_plugins = get_option( 'active_plugins', array() );
		$injection_plugins = 0;
		
		foreach ( $active_plugins as $plugin ) {
			if ( strpos( $plugin, 'notification' ) !== false ||
			     strpos( $plugin, 'popup' ) !== false ||
			     strpos( $plugin, 'banner' ) !== false ||
			     strpos( $plugin, 'cookie' ) !== false ) {
				$injection_plugins++;
			}
		}
		
		if ( $injection_plugins > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugins inject dynamic content (may cause shifts)', 'wpshadow' ),
				$injection_plugins
			);
			$score += 15;
		}
		
		// Check for lazy loading without placeholders
		$lazy_load_plugins = 0;
		foreach ( $active_plugins as $plugin ) {
			if ( strpos( $plugin, 'lazy' ) !== false ) {
				$lazy_load_plugins++;
			}
		}
		
		if ( $lazy_load_plugins > 0 && ! $theme_supports_responsive ) {
			$issues[] = __( 'Lazy loading without dimension preservation causes shifts', 'wpshadow' );
			$score += 20;
		}
		
		// If significant issues found
		if ( $score > 30 ) {
			$severity = 'medium';
			if ( $score > 50 ) {
				$severity = 'high';
			}
			if ( $score > 70 ) {
				$severity = 'critical';
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of CLS issues */
					__( 'Factors causing Cumulative Layout Shift (Core Web Vital): %s. CLS measures visual stability, preventing unexpected content shifts that frustrate users.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $score ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cumulative-layout-shift',
				'meta'         => array(
					'font_issues'          => $font_issues,
					'has_embeds'           => $has_embeds,
					'has_ads'              => $has_ads,
					'injection_plugins'    => $injection_plugins,
					'lazy_load_plugins'    => $lazy_load_plugins,
					'theme_responsive'     => $theme_supports_responsive,
					'score'                => $score,
					'good_threshold'       => '0.1',
					'poor_threshold'       => '0.25',
					'primary_causes'       => 'Images without dimensions, web fonts, ads, embeds',
				),
			);
		}
		
		return null;
	}
}
