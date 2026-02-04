<?php
/**
 * Mobile Render-Blocking CSS Detection
 *
 * Finds CSS files blocking render on mobile.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Render-Blocking CSS Detection
 *
 * Identifies CSS files loaded in <head> that block render
 * and should be deferred or inlined for mobile.
 *
 * @since 1.602.1600
 */
class Diagnostic_Mobile_Render_Blocking_CSS extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-render-blocking-css';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Render-Blocking CSS Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Finds CSS files blocking render on mobile';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_render_blocking_css();

		if ( empty( $issues ) ) {
			return null; // No render-blocking CSS found
		}

		$threat = 75;
		if ( count( $issues ) > 5 ) {
			$threat = 85; // Critical - many blocking files
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: count of render-blocking resources */
				__( 'Found %d render-blocking CSS files', 'wpshadow' ),
				count( $issues )
			),
			'severity'        => 'high',
			'threat_level'    => $threat,
			'blocking_css'    => array_slice( $issues, 0, 5 ),
			'total_blocking'  => count( $issues ),
			'estimated_delay' => round( count( $issues ) * 400, 0 ) . ' ms FCP delay',
			'user_impact'     => __( 'CSS blocks render by 400-1000ms on 3G', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/render-blocking-css',
		);
	}

	/**
	 * Find render-blocking CSS files.
	 *
	 * @since  1.602.1600
	 * @return array Issues found.
	 */
	private static function find_render_blocking_css(): array {
		global $wp_styles;

		$issues = array();

		if ( ! isset( $wp_styles ) || ! is_object( $wp_styles ) ) {
			return $issues;
		}

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
				continue;
			}

			$style = $wp_styles->registered[ $handle ];

			// Check if style is render-blocking (not async/defer equivalent for CSS)
			// For CSS, we check if it's in head and doesn't have media query limiting
			if ( empty( $style->src ) ) {
				continue; // Inline styles
			}

			// Get CSS file
			$src = $style->src;
			if ( 0 === strpos( $src, '/' ) ) {
				$src = ABSPATH . substr( $src, 1 );
			} elseif ( 0 === strpos( $src, 'http' ) && strpos( $src, home_url() ) !== false ) {
				$src = str_replace( home_url(), ABSPATH, $src );
			} else {
				continue; // External or unresolvable
			}

			if ( ! file_exists( $src ) ) {
				continue;
			}

			$size = filesize( $src );

			// Check if likely render-blocking
			$is_blocking = self::is_render_blocking( $style, $size );

			if ( $is_blocking ) {
				$issues[] = array(
					'handle'   => $handle,
					'size'     => $size,
					'size_kb'  => round( $size / 1024, 1 ),
					'path'     => substr( $src, strlen( ABSPATH ) ),
					'media'    => $style->media ?? 'all',
					'severity' => $size > 50 * 1024 ? 'critical' : 'high',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check if style is render-blocking.
	 *
	 * @since  1.602.1600
	 * @param  object $style WordPress style object.
	 * @param  int    $size  File size in bytes.
	 * @return bool Is render-blocking.
	 */
	private static function is_render_blocking( object $style, int $size ): bool {
		// If media is print-only, it doesn't block render
		if ( isset( $style->media ) && 'print' === $style->media ) {
			return false;
		}

		// If media is limited (e.g., (min-width: 768px)), less critical on mobile
		if ( isset( $style->media ) && ! empty( $style->media ) && 'all' !== $style->media ) {
			// Still consider it blocking if it's a mobile media query
			if ( false === strpos( $style->media, 'min-width' ) ) {
				return true;
			}
			return false;
		}

		// Files >50KB in head block render
		if ( $size > 50 * 1024 ) {
			return true;
		}

		// Core WordPress styles are generally optimized, skip
		$critical_handles = array( 'wp-admin', 'wp-common', 'dashicons' );
		foreach ( $critical_handles as $critical ) {
			if ( false !== strpos( $style->handle ?? '', $critical ) ) {
				return false;
			}
		}

		return true;
	}
}
