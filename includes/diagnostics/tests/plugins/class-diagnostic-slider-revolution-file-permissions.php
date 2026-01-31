<?php
/**
 * Slider Revolution File Permissions Diagnostic
 *
 * Slider Revolution files have insecure permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.279.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution File Permissions Diagnostic Class
 *
 * @since 1.279.0000
 */
class Diagnostic_SliderRevolutionFilePermissions extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-file-permissions';
	protected static $title = 'Slider Revolution File Permissions';
	protected static $description = 'Slider Revolution files have insecure permissions';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify file permissions
		$file_perms = get_option( 'rs_file_permissions_set', false );
		if ( ! $file_perms ) {
			$issues[] = __( 'Slider Revolution file permissions not secured', 'wpshadow' );
		}

		// Check 2: Check directory permissions
		$dir_perms = get_option( 'rs_directory_permissions_set', false );
		if ( ! $dir_perms ) {
			$issues[] = __( 'Slider Revolution directory permissions not secured', 'wpshadow' );
		}

		// Check 3: Verify uploads folder security
		$uploads_secure = get_option( 'rs_uploads_security', false );
		if ( ! $uploads_secure ) {
			$issues[] = __( 'Slider Revolution uploads directory not protected', 'wpshadow' );
		}

		// Check 4: Check .htaccess protection
		$htaccess_set = get_option( 'rs_htaccess_protection', false );
		if ( ! $htaccess_set ) {
			$issues[] = __( 'Slider Revolution .htaccess protection not configured', 'wpshadow' );
		}

		// Check 5: Verify cache folder security
		$cache_secure = get_option( 'rs_cache_security', false );
		if ( ! $cache_secure ) {
			$issues[] = __( 'Slider Revolution cache folder not secured', 'wpshadow' );
		}

		// Check 6: Check configuration file access
		$config_secure = get_option( 'rs_config_file_security', false );
		if ( ! $config_secure ) {
			$issues[] = __( 'Slider Revolution configuration files not protected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Slider Revolution file permission security issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/slider-revolution-file-permissions',
			);
		}

		return null;
	}
}

	}
}
