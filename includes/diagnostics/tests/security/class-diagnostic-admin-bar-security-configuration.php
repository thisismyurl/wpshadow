<?php
/**
 * Admin Bar Security Configuration
 *
 * Checks if WordPress admin bar is properly secured and doesn't expose sensitive information.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Bar Security Configuration
 *
 * @since 1.6093.1200
 */
class Diagnostic_Admin_Bar_Security_Configuration extends Diagnostic_Base {

	protected static $slug = 'admin-bar-security-configuration';
	protected static $title = 'Admin Bar Security Configuration';
	protected static $description = 'Verifies admin bar doesn\'t expose sensitive information';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check if admin bar is exposed to front-end
		$show_admin_bar = get_option( 'show_admin_bar_front', true );
		if ( $show_admin_bar && ! is_admin() ) {
			$issues[] = __( 'Admin bar is visible to non-administrators on front-end', 'wpshadow' );
		}

		// Check admin bar items for sensitive information
		global $wp_admin_bar;
		if ( ! empty( $wp_admin_bar ) ) {
			$sensitive_items = 0;
			// Count items that might expose information
			if ( method_exists( $wp_admin_bar, 'get_nodes' ) ) {
				$nodes = $wp_admin_bar->get_nodes();
				foreach ( $nodes as $node ) {
					if ( strpos( $node->id, 'debug' ) !== false || strpos( $node->id, 'error' ) !== false ) {
						$sensitive_items++;
					}
				}
			}

			if ( $sensitive_items > 0 ) {
				$issues[] = __( 'Admin bar contains items that may expose debug information', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/admin-bar-security-configuration',
			);
		}

		return null;
	}
}
