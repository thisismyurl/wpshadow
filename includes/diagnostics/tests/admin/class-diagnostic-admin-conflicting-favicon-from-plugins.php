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

		// Check if multiple plugins are hooking into admin_head to add favicons.
		$favicon_actions = array();
		global $wp_filter;

		if ( isset( $wp_filter['admin_head'] ) && is_object( $wp_filter['admin_head'] ) ) {
			foreach ( $wp_filter['admin_head']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( isset( $callback['function'] ) ) {
						$function = $callback['function'];
						
						// Check if callback name suggests favicon injection.
						if ( is_string( $function ) && 
						     ( stripos( $function, 'favicon' ) !== false || stripos( $function, 'icon' ) !== false ) ) {
							$favicon_actions[] = $function;
						} elseif ( is_array( $function ) && isset( $function[1] ) && is_string( $function[1] ) &&
						           ( stripos( $function[1], 'favicon' ) !== false || stripos( $function[1], 'icon' ) !== false ) ) {
							$favicon_actions[] = ( is_object( $function[0] ) ? get_class( $function[0] ) : $function[0] ) . '::' . $function[1];
						}
					}
				}
			}
		}

		// If multiple favicon-related actions found, flag as potential conflict.
		if ( count( $favicon_actions ) > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d plugins/themes hooking into admin_head to potentially add favicons: %s. Multiple favicon declarations can cause conflicts and browser inconsistencies.', 'wpshadow' ),
					count( $favicon_actions ),
					implode( ', ', array_slice( $favicon_actions, 0, 3 ) ) . ( count( $favicon_actions ) > 3 ? '...' : '' )
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				'meta'         => array(
					'favicon_actions' => $favicon_actions,
				),
			);
		}

		return null; // No conflicting favicon actions detected.
	}
}
