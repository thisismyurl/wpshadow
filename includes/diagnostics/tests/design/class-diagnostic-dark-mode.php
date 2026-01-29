<?php
/**
 * Dark Mode Support Diagnostic
 *
 * Verifies theme respects prefers-color-scheme media query.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1150
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dark Mode Support Class
 *
 * Validates theme supports prefers-color-scheme for dark mode.
 * 30%+ users prefer dark mode for reduced eye strain.
 *
 * @since 1.5029.1150
 */
class Diagnostic_Dark_Mode extends Diagnostic_Base {

	protected static $slug        = 'dark-mode-support';
	protected static $title       = 'Dark Mode Support';
	protected static $description = 'Validates theme supports prefers-color-scheme';
	protected static $family      = 'design';

	public static function check() {
		$cache_key = 'wpshadow_dark_mode_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Fetch stylesheet content using WordPress API (NO $wpdb).
		global $wp_styles;
		if ( ! $wp_styles instanceof \WP_Styles ) {
			wp_styles();
		}

		$has_dark_mode = false;
		$dark_mode_styles = array();

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( empty( $style->src ) ) {
				continue;
			}

			$src = $style->src;
			if ( 0 === strpos( $src, '/' ) || false !== strpos( $src, home_url() ) ) {
				$response = wp_remote_get( $src, array( 'timeout' => 10 ) );

				if ( ! is_wp_error( $response ) ) {
					$css = wp_remote_retrieve_body( $response );
					if ( preg_match( '/@media\s*\([^)]*prefers-color-scheme\s*:\s*dark[^)]*\)/i', $css ) ) {
						$has_dark_mode = true;
						$dark_mode_styles[] = $handle;
					}
				}
			}
		}

		if ( ! $has_dark_mode ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme does not support dark mode. 30%+ users prefer dark mode for reduced eye strain.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/design-dark-mode-support',
				'data'         => array(
					'has_dark_mode'    => false,
					'dark_mode_styles' => array(),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
