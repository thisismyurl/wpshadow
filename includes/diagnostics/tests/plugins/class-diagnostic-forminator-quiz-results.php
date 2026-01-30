<?php
/**
 * Forminator Quiz Results Diagnostic
 *
 * Forminator Quiz Results issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1206.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forminator Quiz Results Diagnostic Class
 *
 * @since 1.1206.0000
 */
class Diagnostic_ForminatorQuizResults extends Diagnostic_Base {

	protected static $slug = 'forminator-quiz-results';
	protected static $title = 'Forminator Quiz Results';
	protected static $description = 'Forminator Quiz Results issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Forminator plugin
		$has_forminator = defined( 'FORMINATOR_VERSION' ) ||
		                  class_exists( 'Forminator' ) ||
		                  function_exists( 'forminator_plugin_dir' );
		
		if ( ! $has_forminator ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Quiz results count
		$results_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}frmt_form_entry 
			WHERE entry_type = 'quiz'"
		);
		
		if ( $results_count === 0 ) {
			return null; // No quiz results
		}
		
		// Check 2: Data retention
		$retention_days = get_option( 'forminator_quiz_retention_days', 0 );
		if ( $retention_days === 0 ) {
			$issues[] = __( 'Unlimited retention (database bloat)', 'wpshadow' );
		}
		
		// Check 3: Export protection
		$export_capability = get_option( 'forminator_export_capability', 'manage_options' );
		if ( 'edit_posts' === $export_capability ) {
			$issues[] = __( 'Low export capability (data exposure)', 'wpshadow' );
		}
		
		// Check 4: Result caching
		$cache_results = get_option( 'forminator_cache_quiz_results', 'no' );
		if ( 'no' === $cache_results && $results_count > 1000 ) {
			$issues[] = __( 'Results not cached (slow queries)', 'wpshadow' );
		}
		
		// Check 5: Personal data in results
		$store_ip = get_option( 'forminator_store_ip_address', 'yes' );
		if ( 'yes' === $store_ip ) {
			$issues[] = __( 'IP addresses stored (GDPR concern)', 'wpshadow' );
		}
		
		// Check 6: Result emails
		$email_results = get_option( 'forminator_email_quiz_results', 'yes' );
		if ( 'yes' === $email_results ) {
			$issues[] = __( 'Results emailed (insecure transmission)', 'wpshadow' );
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
				/* translators: %s: list of Forminator quiz result issues */
				__( 'Forminator quiz results have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/forminator-quiz-results',
		);
	}
}
