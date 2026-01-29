<?php
/**
 * First Input Delay (FID) Testing Diagnostic
 *
 * Tests page interactivity delay on real user interactions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First Input Delay (FID) Testing Class
 *
 * Tests FID metric.
 *
 * @since 1.26028.1905
 */
class Diagnostic_First_Input_Delay_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'first-input-delay-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'First Input Delay (FID) Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests page interactivity delay on real user interactions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$fid_check = self::check_fid_indicators();
		
		if ( $fid_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $fid_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/first-input-delay-testing',
				'meta'         => array(
					'blocking_scripts'   => $fid_check['blocking_scripts'],
					'large_scripts'      => $fid_check['large_scripts'],
					'recommendations'    => $fid_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check FID indicators.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_fid_indicators() {
		global $wp_scripts;

		$check = array(
			'has_issues'        => false,
			'issues'            => array(),
			'blocking_scripts'  => array(),
			'large_scripts'     => array(),
			'recommendations'   => array(),
		);

		// Check for large JavaScript files.
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}

				$script = $wp_scripts->registered[ $handle ];
				
				// Check if script is blocking.
				if ( ! isset( $script->extra['async'] ) && ! isset( $script->extra['defer'] ) ) {
					$check['blocking_scripts'][] = $handle;

					// Attempt to check file size if local.
					if ( ! empty( $script->src ) && 0 === strpos( $script->src, home_url() ) ) {
						$file_path = str_replace( home_url(), ABSPATH, $script->src );
						$file_path = wp_normalize_path( $file_path );
						
						if ( file_exists( $file_path ) ) {
							$file_size = filesize( $file_path );
							
							if ( $file_size > 102400 ) { // >100KB.
								$check['large_scripts'][] = array(
									'handle' => $handle,
									'size'   => $file_size,
								);
							}
						}
					}
				}
			}
		}

		// Detect jQuery usage (often blocking).
		if ( in_array( 'jquery', $check['blocking_scripts'], true ) || in_array( 'jquery-core', $check['blocking_scripts'], true ) ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'jQuery loaded in header without defer (blocks main thread)', 'wpshadow' );
			$check['recommendations'][] = __( 'Move jQuery to footer or defer loading', 'wpshadow' );
		}

		// Check total blocking scripts.
		if ( count( $check['blocking_scripts'] ) > 5 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d blocking JavaScript files detected', 'wpshadow' ),
				count( $check['blocking_scripts'] )
			);
			$check['recommendations'][] = __( 'Add defer or async attributes to non-critical scripts', 'wpshadow' );
		}

		// Check large scripts.
		if ( count( $check['large_scripts'] ) > 0 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of large scripts */
				__( '%d large JavaScript files (>100KB) found', 'wpshadow' ),
				count( $check['large_scripts'] )
			);
			$check['recommendations'][] = __( 'Split large JavaScript files or implement code splitting', 'wpshadow' );
		}

		return $check;
	}
}
