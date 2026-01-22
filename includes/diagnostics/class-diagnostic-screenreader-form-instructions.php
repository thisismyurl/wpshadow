<?php
declare( strict_types=1 );

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are form instructions present?
 *
 * Category: Accessibility & Inclusivity
 * Priority: 2
 * Philosophy: 7, 8
 *
 * Test Description:
 * Are form instructions present?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */
class Diagnostic_Screenreader_Form_Instructions extends Diagnostic_Base {
	
	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'screenreader-form-instructions';
	}
	
	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __('Are form instructions present?', 'wpshadow');
	}
	
	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __('Are form instructions present?. Part of Accessibility & Inclusivity analysis.', 'wpshadow');
	}
	
	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'accessibility';
	}
	
	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// TODO: Implement Are form instructions present? test
		// This is a stub for Accessibility & Inclusivity category
		// Philosophy focus: 7, 8
		//
		// IMPLEMENTATION NOTES:
		// - Check if Are form instructions present?
		// - Return finding with severity, threat level, and resolution advice
		// - Link to knowledge base article for user education
		// - Consider business impact (commandment #9: Show Value)
		// - Make sure output is user-friendly (commandment #1: Helpful Neighbor)
		
		return array();
	}
	
	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// TODO: Set appropriate threat level
		// 0-30: Low
		// 31-60: Medium
		// 61-100: High/Critical
		return 50;
	}
	
	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/screenreader-form-instructions/';
	}
	
	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/screenreader-form-instructions/';
	}
}