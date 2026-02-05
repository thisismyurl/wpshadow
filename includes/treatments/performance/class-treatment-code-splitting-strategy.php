<?php
/**
 * Code Splitting Strategy Treatment
 *
 * Tests if JavaScript and CSS are properly split to reduce
 * initial bundle sizes and improve page load performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1505
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Code Splitting Strategy Treatment Class
 *
 * Evaluates whether the site uses code splitting techniques
 * to optimize JavaScript and CSS delivery.
 *
 * @since 1.6035.1505
 */
class Treatment_Code_Splitting_Strategy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-code-splitting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Code Splitting Strategy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if JavaScript and CSS are properly split';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the code splitting strategy treatment check.
	 *
	 * @since  1.6035.1505
	 * @return array|null Finding array if code splitting issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Get enqueued scripts and styles using WordPress globals.
		global $wp_scripts, $wp_styles;

		if ( ! $wp_scripts instanceof \WP_Scripts ) {
			wp_scripts();
		}
		if ( ! $wp_styles instanceof \WP_Styles ) {
			wp_styles();
		}

		// Analyze enqueued scripts.
		$total_scripts = 0;
		$async_scripts = 0;
		$defer_scripts = 0;
		$conditional_scripts = 0;

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				$total_scripts++;

				// Check for async/defer attributes.
				if ( isset( $script->extra['async'] ) && $script->extra['async'] ) {
					$async_scripts++;
				}
				if ( isset( $script->extra['defer'] ) && $script->extra['defer'] ) {
					$defer_scripts++;
				}

				// Check for conditional loading.
				if ( ! empty( $script->extra['conditional'] ) ) {
					$conditional_scripts++;
				}
			}
		}

		$stats['total_scripts'] = $total_scripts;
		$stats['async_scripts'] = $async_scripts;
		$stats['defer_scripts'] = $defer_scripts;
		$stats['conditional_scripts'] = $conditional_scripts;
		$stats['non_blocking_percentage'] = $total_scripts > 0 
			? round( ( ( $async_scripts + $defer_scripts ) / $total_scripts ) * 100, 1 ) 
			: 0;

		// Analyze enqueued styles.
		$total_styles = 0;
		$conditional_styles = 0;
		$media_query_styles = 0;

		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				$total_styles++;

				// Check for conditional loading.
				if ( ! empty( $style->extra['conditional'] ) ) {
					$conditional_styles++;
				}

				// Check for media queries.
				if ( ! empty( $style->args ) && $style->args !== 'all' ) {
					$media_query_styles++;
				}
			}
		}

		$stats['total_styles'] = $total_styles;
		$stats['conditional_styles'] = $conditional_styles;
		$stats['media_query_styles'] = $media_query_styles;

		// Check for code splitting plugins.
		$code_splitting_plugins = array(
			'autoptimize/autoptimize.php'                    => 'Autoptimize',
			'wp-rocket/wp-rocket.php'                        => 'WP Rocket',
			'async-javascript/async-javascript.php'          => 'Async JavaScript',
			'perfmatters/perfmatters.php'                    => 'Perfmatters',
		);

		$active_splitting_plugins = array();
		foreach ( $code_splitting_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_splitting_plugins[] = $name;
			}
		}

		$stats['active_splitting_plugins'] = $active_splitting_plugins;
		$has_code_splitting_plugin = ! empty( $active_splitting_plugins );

		// Check homepage for code splitting indicators.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$inline_scripts_count = 0;
		$external_scripts_count = 0;
		$inline_styles_count = 0;
		$external_styles_count = 0;
		$has_dynamic_imports = false;
		$has_preload = false;
		$has_prefetch = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Count inline vs external scripts.
			preg_match_all( '/<script[^>]*>(?!<\/script>)/i', $html, $script_matches );
			foreach ( $script_matches[0] as $script_tag ) {
				if ( strpos( $script_tag, 'src=' ) !== false ) {
					$external_scripts_count++;
				} else {
					$inline_scripts_count++;
				}
			}

			// Count inline vs external styles.
			preg_match_all( '/<link[^>]*rel=["\']stylesheet["\'][^>]*>/i', $html, $link_matches );
			$external_styles_count = count( $link_matches[0] );

			preg_match_all( '/<style[^>]*>/i', $html, $style_matches );
			$inline_styles_count = count( $style_matches[0] );

			// Check for dynamic imports (import()).
			if ( preg_match( '/import\s*\(/i', $html ) ) {
				$has_dynamic_imports = true;
			}

			// Check for resource hints.
			if ( preg_match( '/<link[^>]*rel=["\']preload["\'][^>]*>/i', $html ) ) {
				$has_preload = true;
			}
			if ( preg_match( '/<link[^>]*rel=["\']prefetch["\'][^>]*>/i', $html ) ) {
				$has_prefetch = true;
			}
		}

		$stats['inline_scripts_count'] = $inline_scripts_count;
		$stats['external_scripts_count'] = $external_scripts_count;
		$stats['inline_styles_count'] = $inline_styles_count;
		$stats['external_styles_count'] = $external_styles_count;
		$stats['has_dynamic_imports'] = $has_dynamic_imports;
		$stats['has_preload'] = $has_preload;
		$stats['has_prefetch'] = $has_prefetch;

		// Check if theme uses modern build tools.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		
		$has_webpack = file_exists( $theme_dir . '/webpack.config.js' );
		$has_gulp = file_exists( $theme_dir . '/gulpfile.js' );
		$has_grunt = file_exists( $theme_dir . '/Gruntfile.js' );
		$has_vite = file_exists( $theme_dir . '/vite.config.js' );
		$has_package_json = file_exists( $theme_dir . '/package.json' );

		$stats['has_webpack'] = $has_webpack;
		$stats['has_gulp'] = $has_gulp;
		$stats['has_grunt'] = $has_grunt;
		$stats['has_vite'] = $has_vite;
		$stats['has_package_json'] = $has_package_json;
		$has_build_tools = $has_webpack || $has_gulp || $has_grunt || $has_vite;

		// Calculate code splitting score.
		$code_splitting_features = 0;
		$total_features = 8;

		if ( $stats['non_blocking_percentage'] > 50 ) {
			$code_splitting_features++;
		}
		if ( $media_query_styles > 0 ) {
			$code_splitting_features++;
		}
		if ( $has_code_splitting_plugin ) {
			$code_splitting_features++;
		}
		if ( $has_dynamic_imports ) {
			$code_splitting_features++;
		}
		if ( $has_preload || $has_prefetch ) {
			$code_splitting_features++;
		}
		if ( $has_build_tools ) {
			$code_splitting_features++;
		}
		if ( $external_scripts_count > $inline_scripts_count ) {
			$code_splitting_features++;
		}
		if ( $conditional_scripts > 0 || $conditional_styles > 0 ) {
			$code_splitting_features++;
		}

		$stats['code_splitting_score'] = round( ( $code_splitting_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( $stats['non_blocking_percentage'] < 25 ) {
			$issues[] = sprintf(
				/* translators: %s: percentage */
				__( 'Only %s%% of scripts use async/defer - most scripts are render-blocking', 'wpshadow' ),
				$stats['non_blocking_percentage']
			);
		}

		if ( ! $has_code_splitting_plugin ) {
			$warnings[] = __( 'No code splitting plugin active - consider Autoptimize or WP Rocket', 'wpshadow' );
		}

		if ( $inline_scripts_count > $external_scripts_count ) {
			$warnings[] = sprintf(
				/* translators: 1: inline count, 2: external count */
				__( 'Too many inline scripts (%1$d vs %2$d external) - reduces caching effectiveness', 'wpshadow' ),
				$inline_scripts_count,
				$external_scripts_count
			);
		}

		if ( ! $has_preload && ! $has_prefetch ) {
			$warnings[] = __( 'No resource hints (preload/prefetch) detected - consider for critical resources', 'wpshadow' );
		}

		if ( ! $has_build_tools ) {
			$warnings[] = __( 'No build tools detected - consider Webpack, Vite, or Gulp for code splitting', 'wpshadow' );
		}

		if ( $media_query_styles === 0 && $total_styles > 5 ) {
			$warnings[] = __( 'No media query-based CSS splitting - all styles load regardless of device', 'wpshadow' );
		}

		if ( ! $has_dynamic_imports ) {
			$warnings[] = __( 'No dynamic imports detected - consider lazy loading JavaScript modules', 'wpshadow' );
		}

		if ( $conditional_scripts === 0 && $conditional_styles === 0 ) {
			$warnings[] = __( 'No conditional loading detected - all assets load on every page', 'wpshadow' );
		}

		if ( $stats['code_splitting_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Code splitting score is low (%s%%) - implement proper code splitting strategy', 'wpshadow' ),
				$stats['code_splitting_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Code splitting strategy has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/code-splitting',
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
				'description'  => __( 'Code splitting has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/code-splitting',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Code splitting is properly implemented.
	}
}
