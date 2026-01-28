<?php
/**
 * Theme Asset Loading Errors Diagnostic
 *
 * Detects broken CSS/JS file references in the active theme,
 * identifying 404 errors that impact functionality and performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1715
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Asset Loading Errors Diagnostic Class
 *
 * Checks if theme CSS/JS files are loading correctly by:
 * - Fetching homepage HTML
 * - Extracting enqueued asset URLs
 * - Testing each asset for 404 errors
 * - Reporting broken links
 *
 * @since 1.6028.1715
 */
class Diagnostic_Theme_Asset_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1715
	 * @var   string
	 */
	protected static $slug = 'theme-asset-loading-errors';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1715
	 * @var   string
	 */
	protected static $title = 'Theme CSS/JS Loading Errors';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1715
	 * @var   string
	 */
	protected static $description = 'Detects broken CSS and JavaScript file references';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1715
	 * @var   string
	 */
	protected static $family = 'design';

	/**
	 * Cache duration (1 hour)
	 *
	 * @since 1.6028.1715
	 * @var   int
	 */
	private const CACHE_DURATION = 3600;

	/**
	 * Request timeout in seconds
	 *
	 * @since 1.6028.1715
	 * @var   int
	 */
	private const REQUEST_TIMEOUT = 5;

	/**
	 * Run the diagnostic check.
	 *
	 * Tests all enqueued theme CSS/JS assets for loading errors.
	 *
	 * @since  1.6028.1715
	 * @return array|null Finding array if broken assets found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_theme_asset_errors_check' );
		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_theme_assets();

		if ( empty( $analysis['broken_assets'] ) ) {
			set_transient( 'wpshadow_theme_asset_errors_check', null, self::CACHE_DURATION );
			return null;
		}

		$result = self::build_finding( $analysis );

		set_transient( 'wpshadow_theme_asset_errors_check', $result, self::CACHE_DURATION );

		return $result;
	}

	/**
	 * Analyze theme assets for loading errors.
	 *
	 * Extracts CSS/JS assets from enqueued files and tests each for 404s.
	 *
	 * @since  1.6028.1715
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array $broken_assets   List of broken asset URLs.
	 *     @type array $working_assets  List of working asset URLs.
	 *     @type int   $total_tested    Total assets tested.
	 *     @type int   $total_broken    Total broken assets.
	 * }
	 */
	private static function analyze_theme_assets(): array {
		global $wp_scripts, $wp_styles;

		$broken_assets  = array();
		$working_assets = array();

		// Get theme directory URL for filtering.
		$theme_url = get_template_directory_uri();

		// Test CSS files.
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];
				$src   = $style->src;

				// Skip if not theme asset.
				if ( false === strpos( $src, $theme_url ) ) {
					continue;
				}

				// Convert relative URL to absolute.
				if ( 0 === strpos( $src, '//' ) ) {
					$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
				} elseif ( '/' === $src[0] ) {
					$src = home_url( $src );
				}

				$status = self::test_asset_url( $src );

				if ( 200 === $status ) {
					$working_assets[] = array(
						'url'    => $src,
						'handle' => $handle,
						'type'   => 'css',
					);
				} else {
					$broken_assets[] = array(
						'url'    => $src,
						'handle' => $handle,
						'type'   => 'css',
						'status' => $status,
					);
				}
			}
		}

		// Test JS files.
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}

				$script = $wp_scripts->registered[ $handle ];
				$src    = $script->src;

				// Skip if not theme asset.
				if ( false === strpos( $src, $theme_url ) ) {
					continue;
				}

				// Convert relative URL to absolute.
				if ( 0 === strpos( $src, '//' ) ) {
					$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
				} elseif ( '/' === $src[0] ) {
					$src = home_url( $src );
				}

				$status = self::test_asset_url( $src );

				if ( 200 === $status ) {
					$working_assets[] = array(
						'url'    => $src,
						'handle' => $handle,
						'type'   => 'js',
					);
				} else {
					$broken_assets[] = array(
						'url'    => $src,
						'handle' => $handle,
						'type'   => 'js',
						'status' => $status,
					);
				}
			}
		}

		return array(
			'broken_assets'  => $broken_assets,
			'working_assets' => $working_assets,
			'total_tested'   => count( $broken_assets ) + count( $working_assets ),
			'total_broken'   => count( $broken_assets ),
		);
	}

	/**
	 * Test if asset URL is accessible.
	 *
	 * Performs HEAD request to check if asset returns 200 status.
	 *
	 * @since  1.6028.1715
	 * @param  string $url Asset URL to test.
	 * @return int HTTP status code.
	 */
	private static function test_asset_url( string $url ): int {
		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => self::REQUEST_TIMEOUT,
				'redirection' => 0,
				'sslverify'   => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return 0; // Connection error.
		}

		return wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Build finding array from analysis.
	 *
	 * @since  1.6028.1715
	 * @param  array $analysis Analysis results.
	 * @return array Finding array.
	 */
	private static function build_finding( array $analysis ): array {
		$broken_count = $analysis['total_broken'];
		$severity     = 'low';
		$threat       = 30;

		if ( $broken_count >= 5 ) {
			$severity = 'medium';
			$threat   = 45;
		}

		if ( $broken_count >= 10 ) {
			$severity = 'high';
			$threat   = 50;
		}

		$description = sprintf(
			/* translators: 1: broken asset count, 2: total tested */
			_n(
				'Found %1$d broken CSS/JS file reference (%2$d total tested)',
				'Found %1$d broken CSS/JS file references (%2$d total tested)',
				$broken_count,
				'wpshadow'
			),
			$broken_count,
			$analysis['total_tested']
		);

		// Group broken assets by type.
		$broken_css = array_filter( $analysis['broken_assets'], fn( $asset ) => 'css' === $asset['type'] );
		$broken_js  = array_filter( $analysis['broken_assets'], fn( $asset ) => 'js' === $asset['type'] );

		$recommendations = array(
			__( 'Review theme file paths and correct broken references', 'wpshadow' ),
			__( 'Check if files were deleted or renamed during updates', 'wpshadow' ),
			__( 'Verify theme files exist in expected locations', 'wpshadow' ),
			__( 'Test theme functionality on staging environment', 'wpshadow' ),
		);

		if ( count( $broken_css ) > 0 ) {
			$recommendations[] = sprintf(
				/* translators: %d: number of broken CSS files */
				_n(
					'%d CSS file not loading - may cause styling issues',
					'%d CSS files not loading - may cause styling issues',
					count( $broken_css ),
					'wpshadow'
				),
				count( $broken_css )
			);
		}

		if ( count( $broken_js ) > 0 ) {
			$recommendations[] = sprintf(
				/* translators: %d: number of broken JS files */
				_n(
					'%d JavaScript file not loading - may cause functionality issues',
					'%d JavaScript files not loading - may cause functionality issues',
					count( $broken_js ),
					'wpshadow'
				),
				count( $broken_js )
			);
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/theme-asset-loading-errors',
			'family'      => self::$family,
			'meta'        => array(
				'total_tested'   => $analysis['total_tested'],
				'total_broken'   => $broken_count,
				'broken_css'     => count( $broken_css ),
				'broken_js'      => count( $broken_js ),
			),
			'details'     => array(
				'broken_assets'    => $analysis['broken_assets'],
				'recommendations'  => $recommendations,
				'impact'           => __( 'Broken assets cause styling issues, functionality problems, and poor user experience', 'wpshadow' ),
			),
		);
	}
}
