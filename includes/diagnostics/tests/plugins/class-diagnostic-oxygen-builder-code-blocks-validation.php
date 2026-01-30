<?php
/**
 * Oxygen Builder Code Blocks Validation Diagnostic
 *
 * Oxygen Builder Code Blocks Validation issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.813.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder Code Blocks Validation Diagnostic Class
 *
 * @since 1.813.0000
 */
class Diagnostic_OxygenBuilderCodeBlocksValidation extends Diagnostic_Base {

	protected static $slug = 'oxygen-builder-code-blocks-validation';
	protected static $title = 'Oxygen Builder Code Blocks Validation';
	protected static $description = 'Oxygen Builder Code Blocks Validation issues found';
	protected static $family = 'security';

	public static function check() {
		if ( ! get_option( 'oxygen_code_blocks_enabled', '' ) && ! get_option( 'oxygen_builder_active', '' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Code validation enabled
		$code_validation = get_option( 'oxygen_code_validation_enabled', 0 );
		if ( ! $code_validation ) {
			$issues[] = 'Code validation not enabled';
		}
		
		// Check 2: Security scanning
		$security_scan = get_option( 'oxygen_code_security_scanning', 0 );
		if ( ! $security_scan ) {
			$issues[] = 'Security scanning not enabled';
		}
		
		// Check 3: Syntax checking
		$syntax_check = get_option( 'oxygen_code_syntax_checking', 0 );
		if ( ! $syntax_check ) {
			$issues[] = 'Syntax checking not enabled';
		}
		
		// Check 4: Block library validation
		$lib_validation = get_option( 'oxygen_block_lib_validation', 0 );
		if ( ! $lib_validation ) {
			$issues[] = 'Block library validation not enabled';
		}
		
		// Check 5: Custom code review
		$code_review = get_option( 'oxygen_custom_code_review', 0 );
		if ( ! $code_review ) {
			$issues[] = 'Custom code review not enabled';
		}
		
		// Check 6: Code error logging
		$error_logging = get_option( 'oxygen_code_error_logging', 0 );
		if ( ! $error_logging ) {
			$issues[] = 'Code error logging not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d code validation issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/oxygen-builder-code-blocks-validation',
			);
		}
		
		return null;
	}
}
