<?php
/**
 * Unused Themes Removed Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 08.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unused Themes Removed Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Unused_Themes_Removed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'unused-themes-removed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Unused Themes Removed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Unused Themes Removed. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use wp_get_themes and stylesheet/template options to find inactive themes.
	 *
	 * TODO Fix Plan:
	 * Fix by deleting non-active themes while keeping fallback default.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$all_themes        = wp_get_themes();
		$active_stylesheet = (string) get_option( 'stylesheet', '' );
		$active_template   = (string) get_option( 'template', '' );

		// Theme slugs in active use (active child + its parent, if any).
		$in_use     = array_filter( array_unique( array( $active_stylesheet, $active_template ) ) );
		$all_slugs  = array_keys( $all_themes );
		$inactive   = array_diff( $all_slugs, $in_use );
		$count      = count( $inactive );

		// Allow one additional theme (WordPress recommends keeping a default theme as fallback).
		if ( $count <= 1 ) {
			return null;
		}

		$names = array();
		foreach ( $inactive as $slug ) {
			$names[] = isset( $all_themes[ $slug ] ) ? $all_themes[ $slug ]->get( 'Name' ) : $slug;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of inactive themes */
				_n(
					'%d unused theme is installed. Inactive themes can contain exploitable vulnerabilities even when not active. Remove all themes you are not using.',
					'%d unused themes are installed. Inactive themes can contain exploitable vulnerabilities even when not active. Remove all themes you are not using.',
					$count,
					'wpshadow'
				),
				$count
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/unused-themes-removed',
			'details'      => array(
				'inactive_count'  => $count,
				'inactive_themes' => $names,
				'active_theme'    => $active_stylesheet,
			),
		);
	}
}
