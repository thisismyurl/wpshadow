<?php
declare( strict_types=1 );

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CPA increasing/decreasing?
 *
 * Category: Business Impact & Revenue
 * Priority: 1
 * Philosophy: 9, 11
 *
 * Test Description:
 * CPA increasing/decreasing?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */
class Diagnostic_Cost_Per_Acquisition_Trend extends Diagnostic_Base {
	
	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'cost-per-acquisition-trend';
	}
	
	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __('CPA increasing/decreasing?', 'wpshadow');
	}
	
	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __('CPA increasing/decreasing?. Part of Business Impact & Revenue analysis.', 'wpshadow');
	}
	
	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'business_impact';
	}
	
	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// TODO: Implement CPA increasing/decreasing? test
		// This is a stub for Business Impact & Revenue category
		// Philosophy focus: 9, 11
		//
		// IMPLEMENTATION NOTES:
		// - Check if CPA increasing/decreasing?
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
		return 'https://wpshadow.com/kb/cost-per-acquisition-trend/';
	}
	
	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/cost-per-acquisition-trend/';
	}
}