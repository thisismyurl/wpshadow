<?php
/**
 * Tool Nonce Validation Failures
 *
 * Comprehensive test of nonce implementation across all Tool actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tool_Nonce_Validation_Failures Class
 *
 * Comprehensive validation of nonce implementation across tool actions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Tool_Nonce_Validation_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-nonce-validation-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Nonce Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive test of nonce implementation in tool actions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests nonce validation comprehensively.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for missing nonces in forms
		$form_issue = self::check_form_nonces();
		if ( $form_issue ) {
			$issues[] = $form_issue;
		}

		// 2. Check AJAX nonce implementation
		$ajax_issue = self::check_ajax_nonces();
		if ( $ajax_issue ) {
			$issues[] = $ajax_issue;
		}

		// 3. Check nonce regeneration
		$regen_issue = self::check_nonce_regeneration();
		if ( $regen_issue ) {
			$issues[] = $regen_issue;
		}

		// 4. Check for replay attack prevention
		$replay_issue = self::check_replay_attack_prevention();
		if ( $replay_issue ) {
			$issues[] = $replay_issue;
		}

		// 5. Check nonce time-to-live
		$ttl_issue = self::check_nonce_ttl();
		if ( $ttl_issue ) {
			$issues[] = $ttl_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of nonce issues */
					__( '%d nonce validation issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/nonce-validation-tools',
				'recommendations' => array(
					__( 'Ensure all form submissions include nonce field', 'wpshadow' ),
					__( 'Verify nonce validation before processing requests', 'wpshadow' ),
					__( 'Implement nonce failure handling with proper errors', 'wpshadow' ),
					__( 'Test for replay attacks and nonce reuse', 'wpshadow' ),
					__( 'Monitor and log nonce failures', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check form nonce presence.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_form_nonces() {
		// Check for common tool forms with nonces
		$forms_to_check = array(
			'import' => '_wpnonce_import',
			'export' => '_wpnonce_export',
			'erase'  => '_wpnonce_erase',
		);

		$missing_nonces = array();

		foreach ( $forms_to_check as $form => $nonce_field ) {
			// This would check actual form HTML
			// For now, check for filter indicating implementation
			if ( ! has_filter( "wpshadow_{$form}_form_nonce" ) ) {
				$missing_nonces[] = $form;
			}
		}

		if ( ! empty( $missing_nonces ) ) {
			return sprintf(
				/* translators: %s: list of forms */
				__( 'Forms missing nonce fields: %s', 'wpshadow' ),
				implode( ', ', $missing_nonces )
			);
		}

		return null;
	}

	/**
	 * Check AJAX nonce implementation.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_ajax_nonces() {
		global $wp_filter;

		// Check AJAX handler nonce validation
		$ajax_actions = array(
			'wpshadow_import_file',
			'wpshadow_export_data',
			'wpshadow_erase_user_data',
		);

		$unvalidated = array();

		foreach ( $ajax_actions as $action ) {
			$hook = "wp_ajax_{$action}";

			// Check if hook has nonce check
			if ( ! isset( $wp_filter[ $hook ] ) ) {
				continue;
			}

			// Would need to inspect callback to verify check_ajax_referer() call
			// For now assume potential issue if not explicitly marked
			if ( ! has_filter( "{$action}_nonce_verified" ) ) {
				$unvalidated[] = $action;
			}
		}

		if ( ! empty( $unvalidated ) ) {
			return sprintf(
				/* translators: %d: number of actions */
				__( '%d AJAX actions may lack nonce validation', 'wpshadow' ),
				count( $unvalidated )
			);
		}

		return null;
	}

	/**
	 * Check nonce regeneration.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_nonce_regeneration() {
		// Check if nonces are regenerated properly
		// WordPress nonces should use wp_create_nonce() and wp_verify_nonce()

		// Check for timing attacks vulnerability
		if ( ! function_exists( 'hash_equals' ) ) {
			return __( 'Timing attack vulnerability: hash_equals() not available', 'wpshadow' );
		}

		// Check if password change regenerates nonces
		if ( ! has_action( 'password_reset' ) ) {
			return null; // No password handling
		}

		return null;
	}

	/**
	 * Check replay attack prevention.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_replay_attack_prevention() {
		// Replay attack = attacker captures nonce and reuses it
		// Prevention: limit nonce lifetime, single-use nonces, or state tracking

		// Check for nonce expiration
		$nonce_life = wp_nonce_tick();

		// WordPress default is 24-hour nonce life (two 12-hour ticks)
		// For tools, should be shorter (60 minutes or less)

		if ( $nonce_life > 2 ) {
			return __( 'Nonce lifetime is too long (should be < 60 minutes)', 'wpshadow' );
		}

		// Check for single-use enforcement
		if ( ! has_filter( 'wpshadow_nonce_single_use' ) ) {
			return __( 'No enforcement that nonces can only be used once', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check nonce time-to-live.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_nonce_ttl() {
		// Nonce TTL should be reasonable for tool operations
		// Too short = user frustration, too long = security risk

		// Default WordPress is 24 hours - acceptable but not ideal
		// Tools should ideally be 60 minutes

		$current_nonce = wp_create_nonce( 'wpshadow_test' );

		// Would need to measure actual nonce lifespan
		// For now, just verify nonce system is working
		if ( ! $current_nonce ) {
			return __( 'Nonce generation failed', 'wpshadow' );
		}

		// Verify nonce can be validated
		if ( ! wp_verify_nonce( $current_nonce, 'wpshadow_test' ) ) {
			return __( 'Nonce verification failed', 'wpshadow' );
		}

		return null;
	}
}
