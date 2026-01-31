<?php
/**
 * Lingotek Translation Workflow Diagnostic
 *
 * Lingotek Translation Workflow misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1180.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lingotek Translation Workflow Diagnostic Class
 *
 * @since 1.1180.0000
 */
class Diagnostic_LingotekTranslationWorkflow extends Diagnostic_Base {

	protected static $slug = 'lingotek-translation-workflow';
	protected static $title = 'Lingotek Translation Workflow';
	protected static $description = 'Lingotek Translation Workflow misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Lingotek Translation plugin
		$has_lingotek = class_exists( 'Lingotek' ) ||
		                defined( 'LINGOTEK_VERSION' ) ||
		                get_option( 'lingotek_community_id', '' ) !== '';
		
		if ( ! $has_lingotek ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API token configured
		$api_token = get_option( 'lingotek_access_token', '' );
		if ( empty( $api_token ) ) {
			$issues[] = __( 'No API token (workflow disabled)', 'wpshadow' );
		}
		
		// Check 2: Workflow templates
		$workflows = get_option( 'lingotek_workflows', array() );
		if ( empty( $workflows ) ) {
			$issues[] = __( 'No workflow templates (manual translation)', 'wpshadow' );
		}
		
		// Check 3: Translation memory
		$use_tm = get_option( 'lingotek_use_translation_memory', 'yes' );
		if ( 'no' === $use_tm ) {
			$issues[] = __( 'Translation memory disabled (higher costs)', 'wpshadow' );
		}
		
		// Check 4: Quality settings
		$quality_level = get_option( 'lingotek_quality_level', 'standard' );
		if ( 'none' === $quality_level ) {
			$issues[] = __( 'No quality checks (poor translations)', 'wpshadow' );
		}
		
		// Check 5: Job management
		global $wpdb;
		$pending_jobs = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'lingotek_status' AND meta_value = 'pending'"
		);
		
		if ( $pending_jobs > 50 ) {
			$issues[] = sprintf( __( '%d pending translation jobs', 'wpshadow' ), $pending_jobs );
		}
		
		// Check 6: Auto-download
		$auto_download = get_option( 'lingotek_auto_download', 'no' );
		if ( 'no' === $auto_download ) {
			$issues[] = __( 'Manual download (delayed publishing)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of translation workflow issues */
				__( 'Lingotek workflow has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/lingotek-translation-workflow',
		);
	}
}
