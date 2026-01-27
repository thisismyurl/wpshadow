<?php
/**
 * Admin Persistent Notices That Should Be Dismissible Diagnostic
 *
 * Checks if persistent admin notices should be dismissible.
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
 * Admin Persistent Notices That Should Be Dismissible Diagnostic Class
 *
 * Detects persistent notices that lack dismissible functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Persistent_Notices_That_Should_Be_Dismissible extends Diagnostic_Base {

	protected static $slug = 'admin-persistent-notices-that-should-be-dismissible';
	protected static $title = 'Persistent Notices That Should Be Dismissible';
	protected static $description = 'Checks if persistent notices can be dismissed';
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

		// Find notices without is-dismissible class or dismiss button.
		preg_match_all( '/<div[^>]*class=["\'][^"\']*notice[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $matches );
		
		$non_dismissible_notices = 0;
		
		foreach ( $matches[0] as $notice_html ) {
			// Skip if already dismissible.
			if ( false !== strpos( $notice_html, 'is-dismissible' ) ) {
				continue;
			}
			
			// Skip if has explicit dismiss button.
			if ( false !== strpos( $notice_html, 'notice-dismiss' ) ) {
				continue;
			}
			
			// Count informational notices that should be dismissible.
			if ( false !== strpos( $notice_html, 'notice-info' ) || 
			     false !== strpos( $notice_html, 'notice-success' ) ) {
				$non_dismissible_notices++;
			}
		}

		if ( $non_dismissible_notices > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d persistent notice(s) that should be dismissible. This creates visual clutter for users.', 'wpshadow' ),
					$non_dismissible_notices
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
