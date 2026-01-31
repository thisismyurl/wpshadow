<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Missing_Loadinglazy_On_Images extends Diagnostic_Base {
	protected static $slug = 'html-detect-missing-loadinglazy-on-images';
	protected static $title = 'Images Missing Lazy Loading';
	protected static $description = 'Detects images without lazy loading';
	protected static $family = 'performance';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$no_lazy = array();
		if ( preg_match_all( '/<img[^>]*(?!.*loading=["\']lazy["\'])[^>]*src=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
			$no_lazy = array_slice( array_map( 'basename', $matches[1] ), 0, 10 );
		}
		if ( count( $no_lazy ) < 3 ) 
		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
{ return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d image(s) without lazy loading. Add loading="lazy" to below-the-fold images to defer loading until needed, improving page speed.', 'wpshadow' ), count( $no_lazy ) ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-missing-loadinglazy-on-images',
			'meta' => array( 'count' => count( $no_lazy ) ),
		);
	}
}
