<?php
/**
 * Admin Conflicting Favicon From Plugins Diagnostic
 *
 * Checks if plugins are overriding the WordPress admin favicon.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Conflicting Favicon From Plugins Diagnostic Class
 *
 * Detects when multiple favicons are defined, causing conflicts.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Conflicting_Favicon_From_Plugins extends Diagnostic_Base {

	protected static $slug = 'admin-conflicting-favicon-from-plugins';
	protected static $title = 'Conflicting Favicon From Plugins';
	protected static $description = 'Checks if plugins are adding conflicting favicons';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( 'index.php' );
		
		if ( false === $html ) {
			return null;
		}

		// Count favicon link tags.
		$favicon_count = preg_match_all( '/rel=(["\'])(?:shortcut )?icon\1/i', $html, $matches );

		if ( $favicon_count > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d favicon declarations in admin. Multiple favicons cause conflicts and browser inconsistencies.', 'wpshadow' ),
					$favicon_count
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
