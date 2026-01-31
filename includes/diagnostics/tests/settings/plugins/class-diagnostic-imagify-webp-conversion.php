<?php
/**
 * Imagify Webp Conversion Diagnostic
 *
 * Imagify Webp Conversion detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.740.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imagify Webp Conversion Diagnostic Class
 *
 * @since 1.740.0000
 */
class Diagnostic_ImagifyWebpConversion extends Diagnostic_Base {

	protected static $slug = 'imagify-webp-conversion';
	protected static $title = 'Imagify Webp Conversion';
	protected static $description = 'Imagify Webp Conversion detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'IMAGIFY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Imagify settings
		$imagify_settings = get_option( 'imagify_settings', array() );

		// Check WebP conversion
		$webp_enabled = isset( $imagify_settings['convert_to_webp'] ) ? $imagify_settings['convert_to_webp'] : false;
		if ( ! $webp_enabled ) {
			$issues[] = 'webp_conversion_disabled';
			$threat_level += 25;
		}

		// Check WebP method
		$webp_method = isset( $imagify_settings['webp_method'] ) ? $imagify_settings['webp_method'] : 'picture';
		if ( $webp_method === 'none' ) {
			$issues[] = 'webp_method_not_configured';
			$threat_level += 15;
		}

		// Check for WebP files
		if ( $webp_enabled ) {
			$upload_dir = wp_upload_dir();
			$webp_files = glob( $upload_dir['basedir'] . '/**/*.webp' );
			if ( ! $webp_files || count( $webp_files ) === 0 ) {
				$issues[] = 'no_webp_files_generated';
				$threat_level += 20;
			}
		}

		// Check .htaccess rules for WebP
		$htaccess_file = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_file ) && $webp_enabled ) {
			$htaccess_content = file_get_contents( $htaccess_file );
			if ( strpos( $htaccess_content, 'image/webp' ) === false ) {
				$issues[] = 'webp_htaccess_rules_missing';
				$threat_level += 15;
			}
		}

		// Check optimization level
		$optimization_level = isset( $imagify_settings['optimization_level'] ) ? $imagify_settings['optimization_level'] : 1;
		if ( $optimization_level < 1 ) {
			$issues[] = 'optimization_level_too_low';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of WebP conversion issues */
				__( 'Imagify WebP conversion has problems: %s. This prevents modern image format delivery and slows page loads.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagify-webp-conversion',
			);
		}
		
		return null;
	}
}
