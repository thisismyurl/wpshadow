<?php
/**
 * Theme Documentation Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_Documentation extends Diagnostic_Base {
	protected static $slug = 'theme-documentation';
	protected static $title = 'Theme Documentation';
	protected static $description = 'Checks if theme has built-in help or documentation';
	protected static $family = 'functionality';

	public static function check() {
		$theme     = wp_get_theme();
		$theme_uri = $theme->get( 'ThemeURI' );
		$has_docs  = ! empty( $theme_uri ) && ( strpos( $theme_uri, 'doc' ) !== false || strpos( $theme_uri, 'support' ) !== false );

		if ( ! $has_docs && ! file_exists( get_template_directory() . '/README.md' ) && ! file_exists( get_template_directory() . '/readme.txt' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme has no documentation or help resources - may be difficult for users', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-documentation',
			);
		}
		return null;
	}
}
