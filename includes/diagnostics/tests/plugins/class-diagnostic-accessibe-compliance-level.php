<?php
/**
 * Accessibe Compliance Level Diagnostic
 *
 * Accessibe Compliance Level not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1105.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibe Compliance Level Diagnostic Class
 *
 * @since 1.1105.0000
 */
class Diagnostic_AccessibeComplianceLevel extends Diagnostic_Base {

	protected static $slug = 'accessibe-compliance-level';
	protected static $title = 'Accessibe Compliance Level';
	protected static $description = 'Accessibe Compliance Level not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ACCESSIBE_VERSION' ) && ! class_exists( 'AccessiBe' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Compliance level setting.
		$compliance_level = get_option( 'accessibe_compliance_level', '' );
		if ( empty( $compliance_level ) ) {
			$issues[] = 'compliance level not configured';
		} elseif ( ! in_array( $compliance_level, array( 'AA', 'AAA' ), true ) ) {
			$issues[] = "compliance level set to {$compliance_level} (WCAG AA or AAA recommended)";
		}

		// Check 2: Installation ID configured.
		$installation_id = get_option( 'accessibe_installation_id', '' );
		if ( empty( $installation_id ) ) {
			$issues[] = 'accessiBe installation ID not configured (widget will not load)';
		}

		// Check 3: Widget script loaded.
		global $wp_scripts;
		$widget_loaded = isset( $wp_scripts->registered['accessibe-widget'] );
		if ( ! $widget_loaded && ! empty( $installation_id ) ) {
			$issues[] = 'installation ID set but widget script not enqueued';
		}

		// Check 4: Statement page configured.
		$statement_page = get_option( 'accessibe_statement_page', 0 );
		if ( empty( $statement_page ) || 'publish' !== get_post_status( $statement_page ) ) {
			$issues[] = 'accessibility statement page not configured or published';
		}

		// Check 5: Last compliance scan date.
		$last_scan = get_option( 'accessibe_last_scan', 0 );
		if ( $last_scan > 0 ) {
			$days_since_scan = round( ( time() - $last_scan ) / DAY_IN_SECONDS );
			if ( $days_since_scan > 30 ) {
				$issues[] = "compliance not scanned in {$days_since_scan} days (monthly scans recommended)";
			}
		}

		// Check 6: Widget position and visibility.
		$widget_position = get_option( 'accessibe_widget_position', 'bottom-right' );
		$widget_visible = get_option( 'accessibe_widget_visible', '1' );
		if ( '0' === $widget_visible ) {
			$issues[] = 'accessibility widget hidden (users cannot access features)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'accessiBe compliance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessibe-compliance-level',
			);
		}

		return null;
	}
}
