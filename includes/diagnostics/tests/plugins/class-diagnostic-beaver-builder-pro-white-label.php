<?php
/**
 * Beaver Builder Pro White Label Diagnostic
 *
 * Beaver Builder Pro White Label issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.801.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro White Label Diagnostic Class
 *
 * @since 1.801.0000
 */
class Diagnostic_BeaverBuilderProWhiteLabel extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-white-label';
	protected static $title = 'Beaver Builder Pro White Label';
	protected static $description = 'Beaver Builder Pro White Label issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();
		$branding = get_option( 'fl_builder_branding', array() );

		// Check 1: Verify white label is enabled
		$white_label = isset( $branding['enabled'] ) ? (bool) $branding['enabled'] : false;
		if ( ! $white_label ) {
			$issues[] = 'White label not enabled';
		}

		// Check 2: Check for custom plugin name
		$plugin_name = isset( $branding['name'] ) ? $branding['name'] : '';
		if ( $white_label && empty( $plugin_name ) ) {
			$issues[] = 'Custom plugin name not configured';
		}

		// Check 3: Verify support links hidden
		$hide_support = isset( $branding['hide_support_links'] ) ? (bool) $branding['hide_support_links'] : false;
		if ( $white_label && ! $hide_support ) {
			$issues[] = 'Support links not hidden for white label';
		}

		// Check 4: Check for logo replacement
		$logo = isset( $branding['logo'] ) ? $branding['logo'] : '';
		if ( $white_label && empty( $logo ) ) {
			$issues[] = 'Custom branding logo not configured';
		}

		// Check 5: Verify admin menu rename
		$menu_label = isset( $branding['menu_label'] ) ? $branding['menu_label'] : '';
		if ( $white_label && empty( $menu_label ) ) {
			$issues[] = 'Admin menu label not customized';
		}

		// Check 6: Check for builder UI branding
		$ui_branding = isset( $branding['ui_branding'] ) ? (bool) $branding['ui_branding'] : false;
		if ( $white_label && ! $ui_branding ) {
			$issues[] = 'Builder UI branding not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Beaver Builder white label issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-pro-white-label',
			);
		}

		return null;
	}
}
