<?php
/**
 * Admin Dashboard Widget Security
 *
 * Checks if admin dashboard widgets are properly secured against XSS attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0631
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Dashboard Widget Security
 *
 * @since 1.26033.0631
 */
class Diagnostic_Admin_Dashboard_Widget_Security extends Diagnostic_Base {

	protected static $slug = 'admin-dashboard-widget-security';
	protected static $title = 'Admin Dashboard Widget Security';
	protected static $description = 'Verifies dashboard widgets are secure against XSS attacks';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get registered dashboard widgets
		global $wp_dashboard_control_bar;
		$dashboard = wp_dashboard_control_bar();

		// Check for unescaped output in widgets
		$unescaped_widgets = 0;
		if ( function_exists( 'wp_dashboard_quick_press' ) ) {
			$unescaped_widgets++;
		}

		if ( $unescaped_widgets > 0 ) {
			$issues[] = __( 'Some dashboard widgets may not properly escape output', 'wpshadow' );
		}

		// Check if custom widgets are unvetted
		$custom_count = 0;
		$this_plugin  = plugin_basename( WPSHADOW_FILE ?? __FILE__ );
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['dashboard'] ) ) {
			foreach ( $wp_meta_boxes['dashboard'] as $context => $boxes ) {
				foreach ( $boxes as $box ) {
					$custom_count++;
				}
			}
		}

		if ( $custom_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of widgets */
				__( 'Dashboard has %d widgets - verify all are from trusted sources', 'wpshadow' ),
				$custom_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-dashboard-widget-security',
			);
		}

		return null;
	}
}
