<?php
/**
 * Admin Duplicate Notices From Plugins Diagnostic
 *
 * Checks if plugins are displaying duplicate admin notices.
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
 * Admin Duplicate Notices From Plugins Diagnostic Class
 *
 * Detects duplicate admin notice content.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Duplicate_Notices_From_Plugins extends Diagnostic_Base {

	protected static $slug = 'admin-duplicate-notices-from-plugins';
	protected static $title = 'Duplicate Admin Notices From Plugins';
	protected static $description = 'Checks if plugins are showing duplicate notices';
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

		// Find all notices and their content.
		preg_match_all( '/<div[^>]*class=["\'][^"\']*notice[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $matches );
		
		$notice_contents = array();
		$duplicates = 0;
		
		foreach ( $matches[1] as $content ) {
			$normalized = trim( strip_tags( $content ) );
			
			if ( empty( $normalized ) ) {
				continue;
			}
			
			if ( isset( $notice_contents[ $normalized ] ) ) {
				$duplicates++;
			} else {
				$notice_contents[ $normalized ] = true;
			}
		}

		if ( $duplicates > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d duplicate admin notice(s). Multiple plugins may be showing the same message.', 'wpshadow' ),
					$duplicates
				),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
