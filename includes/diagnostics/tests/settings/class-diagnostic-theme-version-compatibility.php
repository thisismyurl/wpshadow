<?php
/**
 * Theme Version Compatibility Diagnostic
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

class Diagnostic_Theme_Version_Compatibility extends Diagnostic_Base {
	protected static $slug = 'theme-version-compatibility';
	protected static $title = 'Theme Version Compatibility';
	protected static $description = 'Verifies theme is compatible with WordPress version';
	protected static $family = 'functionality';

	public static function check() {
		global $wp_version;
		$theme       = wp_get_theme();
		$requires_wp = $theme->get( 'RequiresWP' );
		$tested_up   = $theme->get( 'TestedUpTo' );

		$issues = array();

		if ( ! empty( $requires_wp ) && version_compare( $wp_version, $requires_wp, '<' ) ) {
			$issues[] = array(
				'issue'       => 'wp_version_too_low',
				'description' => sprintf(
					__( 'Theme requires WordPress %s but site is running %s', 'wpshadow' ),
					$requires_wp,
					$wp_version
				),
				'severity'    => 'high',
			);
		}

		if ( ! empty( $tested_up ) && version_compare( $wp_version, $tested_up, '>' ) ) {
			$issues[] = array(
				'issue'       => 'not_tested_with_current',
				'description' => sprintf(
					__( 'Theme only tested up to WordPress %s, running %s - may have compatibility issues', 'wpshadow' ),
					$tested_up,
					$wp_version
				),
				'severity'    => 'medium',
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d theme compatibility issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 55,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/theme-version-compatibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
