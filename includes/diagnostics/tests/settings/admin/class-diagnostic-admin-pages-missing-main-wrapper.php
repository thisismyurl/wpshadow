<?php
/**
 * Admin Pages Missing Main Wrapper Diagnostic
 *
 * Checks if modern admin pages are missing semantic <main> wrapper.
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
 * Admin Pages Missing Main Wrapper Diagnostic Class
 *
 * Detects missing semantic <main> element in admin pages.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Pages_Missing_Main_Wrapper extends Diagnostic_Base {

	protected static $slug = 'admin-pages-missing-main-wrapper';
	protected static $title = 'Admin Pages Missing Main Wrapper';
	protected static $description = 'Checks if admin pages use semantic <main> element';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$pages_to_check = array(
			'index.php'    => 'Dashboard',
			'edit.php'     => 'Posts',
		);

		$pages_missing_main = array();

		foreach ( $pages_to_check as $page_slug => $page_name ) {
			$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( $page_slug );
			
			if ( false === $html ) {
				continue;
			}

			// Check for <main> element or role="main".
			$has_main = ( false !== strpos( $html, '<main' ) || 
			              preg_match( '/role=["\']main["\']/', $html ) );

			if ( ! $has_main ) {
				$pages_missing_main[] = $page_name;
			}
		}

		if ( ! empty( $pages_missing_main ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin pages missing <main> element: %s. This affects accessibility and semantic HTML structure.', 'wpshadow' ),
					implode( ', ', $pages_missing_main )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
