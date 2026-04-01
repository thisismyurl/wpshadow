<?php
/**
 * Lazy Loading Implemented Diagnostic
 *
 * Tests if images and content are properly lazy loaded
 * for improved initial page load performance.
 *
 * @package    WPShadow
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
 * Lazy Loading Implemented Diagnostic Class
 *
 * Evaluates whether the site has proper lazy loading
 * implementation for images, iframes, and content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Lazy_Loading_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-lazy-loading';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if images and content are lazy loaded';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the lazy loading implementation diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if lazy loading issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check WordPress version for native lazy loading support.
		$wp_version = get_bloginfo( 'version' );
		$has_native_lazy_loading = version_compare( $wp_version, '5.5', '>=' );
		$stats['has_native_lazy_loading'] = $has_native_lazy_loading;
		$stats['wordpress_version'] = $wp_version;

		// Check for lazy loading plugins.
		$lazy_load_plugins = array(
			'rocket-lazy-load/rocket-lazy-load.php'          => 'Rocket Lazy Load',
			'a3-lazy-load/a3-lazy-load.php'                  => 'a3 Lazy Load',
			'lazy-load/lazy-load.php'                        => 'Lazy Load',
			'wp-smushit/wp-smush.php'                        => 'Smush (includes lazy load)',
			'autoptimize/autoptimize.php'                    => 'Autoptimize (includes lazy load)',
		);

		$active_lazy_load_plugins = array();
		foreach ( $lazy_load_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_lazy_load_plugins[] = $name;
			}
		}

		$stats['active_lazy_load_plugins'] = $active_lazy_load_plugins;
		$has_lazy_load_plugin = ! empty( $active_lazy_load_plugins );

		// Check homepage for lazy loading implementation.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$images_with_loading_lazy = 0;
		$iframes_with_loading_lazy = 0;
		$total_images = 0;
		$total_iframes = 0;
		$has_lazy_load_js = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Count images with loading="lazy" attribute.
			preg_match_all( '/<img[^>]*>/i', $html, $img_matches );
			$total_images = count( $img_matches[0] );

			foreach ( $img_matches[0] as $img_tag ) {
				if ( preg_match( '/loading=["\']lazy["\']/i', $img_tag ) ) {
					$images_with_loading_lazy++;
				}
			}

			// Count iframes with loading="lazy" attribute.
			preg_match_all( '/<iframe[^>]*>/i', $html, $iframe_matches );
			$total_iframes = count( $iframe_matches[0] );

			foreach ( $iframe_matches[0] as $iframe_tag ) {
				if ( preg_match( '/loading=["\']lazy["\']/i', $iframe_tag ) ) {
					$iframes_with_loading_lazy++;
				}
			}

			// Check for JavaScript-based lazy loading libraries.
			$lazy_load_libraries = array(
				'lazysizes',
				'lazy\.js',
				'lazyload\.js',
				'jquery\.lazy',
				'vanilla-lazyload',
				'lozad\.js',
				'yall\.js',
			);

			foreach ( $lazy_load_libraries as $library ) {
				if ( preg_match( '/' . $library . '/i', $html ) ) {
					$has_lazy_load_js = true;
					break;
				}
			}

			// Check for data-src attributes (common lazy loading pattern).
			$has_data_src = preg_match( '/data-src=/i', $html );
			if ( $has_data_src ) {
				$has_lazy_load_js = true;
			}
		}

		$stats['total_images'] = $total_images;
		$stats['images_with_loading_lazy'] = $images_with_loading_lazy;
		$stats['total_iframes'] = $total_iframes;
		$stats['iframes_with_loading_lazy'] = $iframes_with_loading_lazy;
		$stats['has_lazy_load_js'] = $has_lazy_load_js;

		// Calculate lazy loading coverage.
		$image_lazy_load_percentage = $total_images > 0
			? round( ( $images_with_loading_lazy / $total_images ) * 100, 1 )
			: 0;
		$iframe_lazy_load_percentage = $total_iframes > 0
			? round( ( $iframes_with_loading_lazy / $total_iframes ) * 100, 1 )
			: 0;

		$stats['image_lazy_load_percentage'] = $image_lazy_load_percentage;
		$stats['iframe_lazy_load_percentage'] = $iframe_lazy_load_percentage;

		// Check theme support.
		$theme_supports_lazy_loading = false;
		if ( function_exists( 'wp_lazy_loading_enabled' ) ) {
			$theme_supports_lazy_loading = wp_lazy_loading_enabled( 'img', 'the_content' );
		}
		$stats['theme_supports_lazy_loading'] = $theme_supports_lazy_loading;

		// Check for performance plugins with lazy loading.
		$performance_plugins_with_lazy_load = array(
			'wp-rocket/wp-rocket.php'                        => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'              => 'W3 Total Cache',
			'perfmatters/perfmatters.php'                    => 'Perfmatters',
		);

		$active_performance_plugins = array();
		foreach ( $performance_plugins_with_lazy_load as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_performance_plugins[] = $name;
			}
		}

		$stats['active_performance_plugins'] = $active_performance_plugins;

		// Check for image optimization plugins (often include lazy loading).
		$image_optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'imagify/imagify.php',
		);

		$has_image_optimization = false;
		foreach ( $image_optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_image_optimization = true;
				break;
			}
		}

		$stats['has_image_optimization'] = $has_image_optimization;

		// Calculate overall lazy loading score.
		$lazy_loading_features = 0;
		$total_features = 5;

		if ( $has_native_lazy_loading || $has_lazy_load_plugin || $has_lazy_load_js ) {
			$lazy_loading_features++;
		}
		if ( $image_lazy_load_percentage > 75 ) {
			$lazy_loading_features++;
		}
		if ( $iframe_lazy_load_percentage > 75 || $total_iframes === 0 ) {
			$lazy_loading_features++;
		}
		if ( ! empty( $active_performance_plugins ) ) {
			$lazy_loading_features++;
		}
		if ( $has_image_optimization ) {
			$lazy_loading_features++;
		}

		$stats['lazy_loading_score'] = round( ( $lazy_loading_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_native_lazy_loading && ! $has_lazy_load_plugin && ! $has_lazy_load_js ) {
			$issues[] = __( 'No lazy loading implementation detected - images load on initial page load', 'wpshadow' );
		}

		if ( $has_native_lazy_loading && $image_lazy_load_percentage < 25 ) {
			$warnings[] = sprintf(
				/* translators: %s: percentage */
				__( 'WordPress native lazy loading available but only %s%% of images use it', 'wpshadow' ),
				$image_lazy_load_percentage
			);
		}

		if ( $total_images > 0 && $image_lazy_load_percentage < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: percentage */
				__( 'Low image lazy loading coverage (%s%%) - most images load immediately', 'wpshadow' ),
				$image_lazy_load_percentage
			);
		}

		if ( $total_iframes > 0 && $iframe_lazy_load_percentage < 50 ) {
			$warnings[] = sprintf(
				/* translators: 1: percentage, 2: total count */
				__( 'Only %1$s%% of %2$d iframes are lazy loaded', 'wpshadow' ),
				$iframe_lazy_load_percentage,
				$total_iframes
			);
		}

		if ( ! $has_native_lazy_loading ) {
			$warnings[] = sprintf(
				/* translators: %s: WordPress version */
				__( 'WordPress %s does not support native lazy loading - upgrade to 5.5+', 'wpshadow' ),
				$wp_version
			);
		}

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization plugin - lazy loading works better with optimized images', 'wpshadow' );
		}

		if ( empty( $active_performance_plugins ) && ! $has_lazy_load_plugin ) {
			$warnings[] = __( 'Consider a performance plugin like WP Rocket for advanced lazy loading', 'wpshadow' );
		}

		if ( $stats['lazy_loading_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Lazy loading score is low (%s%%) - implement proper lazy loading', 'wpshadow' ),
				$stats['lazy_loading_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Lazy loading implementation has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lazy-loading?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Lazy loading has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lazy-loading?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Lazy loading is properly implemented.
	}
}
