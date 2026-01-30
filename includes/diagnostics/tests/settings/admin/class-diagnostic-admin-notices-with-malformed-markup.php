<?php
/**
 * Admin Notices With Malformed Markup Diagnostic
 *
 * Checks if admin notices have malformed HTML markup.
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
 * Admin Notices With Malformed Markup Diagnostic Class
 *
 * Detects admin notices with malformed HTML structure.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Notices_With_Malformed_Markup extends Diagnostic_Base {

	protected static $slug = 'admin-notices-with-malformed-markup';
	protected static $title = 'Admin Notices With Malformed Markup';
	protected static $description = 'Checks if admin notices have proper HTML structure';
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

		// Find all notice divs.
		preg_match_all( '/<div[^>]*class=["\'][^"\']*notice[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $matches );
		
		$malformed_notices = 0;
		
		foreach ( $matches[0] as $notice_html ) {
			// Check for unclosed tags.
			$open_p_tags = substr_count( $notice_html, '<p>' ) + substr_count( $notice_html, '<p ' );
			$close_p_tags = substr_count( $notice_html, '</p>' );
			
			if ( $open_p_tags !== $close_p_tags ) {
				$malformed_notices++;
			}
		}

		if ( $malformed_notices > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d admin notice(s) with malformed HTML markup. This can cause layout issues and accessibility problems.', 'wpshadow' ),
					$malformed_notices
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
