<?php
/**
 * Loco Translate Pot Generation Diagnostic
 *
 * Loco Translate Pot Generation misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1165.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loco Translate Pot Generation Diagnostic Class
 *
 * @since 1.1165.0000
 */
class Diagnostic_LocoTranslatePotGeneration extends Diagnostic_Base {

	protected static $slug = 'loco-translate-pot-generation';
	protected static $title = 'Loco Translate Pot Generation';
	protected static $description = 'Loco Translate Pot Generation misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'LOCO_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: File save location
		$file_location = get_option( 'loco_file_location', 'author' );
		if ( 'author' === $file_location ) {
			$issues[] = __( 'Saving to plugin directory (lost on updates)', 'wpshadow' );
		}
		
		// Check 2: Backup enabled
		$backup_enabled = get_option( 'loco_backup', 0 );
		if ( ! $backup_enabled ) {
			$issues[] = __( 'Translation backups disabled (data loss risk)', 'wpshadow' );
		}
		
		// Check 3: POT generation API
		$api_provider = get_option( 'loco_api_provider', '' );
		if ( empty( $api_provider ) ) {
			$issues[] = __( 'No translation API configured (manual translations only)', 'wpshadow' );
		}
		
		// Check 4: String extraction
		$extract_method = get_option( 'loco_extract_method', 'php' );
		if ( 'php' === $extract_method ) {
			$issues[] = __( 'PHP extraction (may miss JavaScript strings)', 'wpshadow' );
		}
		
		// Check 5: POT file permissions
		$theme_pot = get_template_directory() . '/languages/' . get_template() . '.pot';
		if ( file_exists( $theme_pot ) && ! is_writable( $theme_pot ) ) {
			$issues[] = __( 'Theme POT file not writable', 'wpshadow' );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of POT generation issues */
				__( 'Loco Translate has %d POT generation issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/loco-translate-pot-generation',
		);
	}
}
