<?php
/**
 * Admin Notices Missing Dismiss Classes Diagnostic
 *
 * Checks if admin notices are missing proper dismiss classes.
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
 * Admin Notices Missing Dismiss Classes Diagnostic Class
 *
 * Detects notices with dismiss buttons but missing is-dismissible class.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Notices_Missing_Dismiss_Classes extends Diagnostic_Base {

	protected static $slug = 'admin-notices-missing-dismiss-classes';
	protected static $title = 'Admin Notices Missing Dismiss Classes';
	protected static $description = 'Checks if dismissible notices have proper WordPress classes';
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

		// Find notices with dismiss buttons but missing is-dismissible class.
		preg_match_all( '/<div[^>]*class=["\']([^"\']*notice[^"\']*)["\'][^>]*>(.*?)<\/div>/is', $html, $matches );
		
		$incorrect_notices = 0;
		
		foreach ( $matches[0] as $idx => $notice_html ) {
			$classes = $matches[1][ $idx ];
			
			// Has dismiss button but missing is-dismissible class.
			if ( false !== strpos( $notice_html, 'notice-dismiss' ) && 
			     false === strpos( $classes, 'is-dismissible' ) ) {
				$incorrect_notices++;
			}
		}

		if ( $incorrect_notices > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d notice(s) with dismiss buttons missing the is-dismissible class. This causes improper styling and functionality.', 'wpshadow' ),
					$incorrect_notices
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
