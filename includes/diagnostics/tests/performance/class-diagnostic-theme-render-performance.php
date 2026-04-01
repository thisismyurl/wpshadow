<?php
/**
 * Theme Render Performance Diagnostic
 *
 * Checks active theme for indicators of heavy render complexity.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Render Performance Diagnostic
 *
 * Flags themes with unusually large template counts.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Render_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-render-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Render Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks active theme for indicators of heavy render complexity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$template_files = glob( $theme_dir . '/*.php' );

		if ( false === $template_files ) {
			return null;
		}

		$template_count = count( $template_files );
		if ( $template_count < 60 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Theme includes many template files, which may impact render performance', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-render-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'template_count' => $template_count,
			),
		);
	}
}
