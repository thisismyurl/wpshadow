<?php
/**
 * Theme Footer Widgets Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_Footer_Widgets extends Diagnostic_Base {
	protected static $slug = 'theme-footer-widgets';
	protected static $title = 'Theme Footer Widgets';
	protected static $description = 'Verifies footer widget areas are working';
	protected static $family = 'functionality';

	public static function check() {
		global $wp_registered_sidebars;

		$has_footer_widget = false;
		foreach ( $wp_registered_sidebars as $sidebar ) {
			if ( stripos( $sidebar['id'], 'footer' ) !== false || stripos( $sidebar['name'], 'footer' ) !== false ) {
				$has_footer_widget = true;
				break;
			}
		}

		if ( ! $has_footer_widget ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme does not register footer widget areas - limits customization options', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-footer-widgets',
			);
		}
		return null;
	}
}
