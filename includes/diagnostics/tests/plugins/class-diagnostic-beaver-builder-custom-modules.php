<?php
/**
 * Beaver Builder Custom Modules Diagnostic
 *
 * Beaver Builder custom modules insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.342.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Custom Modules Diagnostic Class
 *
 * @since 1.342.0000
 */
class Diagnostic_BeaverBuilderCustomModules extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-custom-modules';
	protected static $title = 'Beaver Builder Custom Modules';
	protected static $description = 'Beaver Builder custom modules insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Custom modules directory.
		$custom_dir = get_option( '_fl_builder_custom_modules_dir', '' );
		if ( ! empty( $custom_dir ) && is_dir( $custom_dir ) ) {
			// Check 2: Directory permissions.
			if ( substr( sprintf( '%o', fileperms( $custom_dir ) ), -4 ) === '0777' ) {
				$issues[] = 'custom modules directory too permissive (777)';
			}
			
			// Check 3: .htaccess protection.
			if ( ! file_exists( $custom_dir . '/.htaccess' ) ) {
				$issues[] = 'no .htaccess protection';
			}
		}
		
		// Check 4: Module validation.
		$validate_modules = get_option( '_fl_builder_validate_custom_modules', '1' );
		if ( '0' === $validate_modules ) {
			$issues[] = 'module validation disabled';
		}
		
		// Check 5: Module sanitization.
		$sanitize = get_option( '_fl_builder_sanitize_module_output', '1' );
		if ( '0' === $sanitize ) {
			$issues[] = 'output sanitization disabled';
		}
		
		// Check 6: Allow unsafe code.
		$allow_unsafe = get_option( '_fl_builder_allow_unsafe_code', '0' );
		if ( '1' === $allow_unsafe ) {
			$issues[] = 'unsafe code allowed';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Beaver Builder security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-custom-modules',
			);
		}
		
		return null;
	}
}
