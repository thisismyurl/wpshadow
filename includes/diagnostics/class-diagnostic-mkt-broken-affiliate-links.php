<?php
declare( strict_types=1 );
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Affiliate Link Revenue Loss
 * 
 * Tests affiliate links, calculates lost commission from broken URLs.
 * 
 * Philosophy: Commandment #9, 1 - Show Value (KPIs) - Track impact, Helpful Neighbor - Anticipate needs
 * Priority: 2 (1=Must-Have, 2=Should-Have, 3=Nice-to-Have)
 * Threat Level: 55/100
 * 
 * Impact: Shows \"23 broken Amazon links = $890/month lost commission\".
 */
class Diagnostic_MktBrokenAffiliateLinks extends Diagnostic_Base {
	
	/**
	 * Get diagnostic ID
	 * 
	 * @return string
	 */
	public static function get_id(): string {
		return 'mkt-broken-affiliate-links';
	}
	
	/**
	 * Get diagnostic name
	 * 
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Affiliate Link Revenue Loss', 'wpshadow' );
	}
	
	/**
	 * Get diagnostic description
	 * 
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Tests affiliate links, calculates lost commission from broken URLs.', 'wpshadow' );
	}
	
	/**
	 * Get diagnostic category
	 * 
	 * @return string
	 */
	public static function get_category(): string {
		return 'marketing_growth';
	}
	
	/**
	 * Get threat level (0-100)
	 * Higher = more critical
	 * 
	 * @return int
	 */
	public static function get_threat_level(): int {
		return 55;
	}
	
	/**
	 * Run the diagnostic
	 * 
	 * @return array Result with status, message, and data
	 */
	public static function run(): array {
		// TODO: Implement mkt-broken-affiliate-links diagnostic
		// 
		// This is a KILLER test that delivers "Holy Sh*t" moments:
		// Shows \"23 broken Amazon links = $890/month lost commission\".
		// 
		// Implementation notes:
		// - Quantify impact with real numbers (dollar amounts, percentages)
		// - Show specific examples (file names, URLs, exact problems)
		// - Provide actionable fix recommendations
		// - Link to KB article explaining why this matters
		// - Track KPI: time saved, revenue impact, disaster prevented
		
		return array(
			'status' => 'todo',
			'message' => __('Not yet implemented - Priority 2 killer test', 'wpshadow'),
			'data' => array(
				'impact' => 'Shows \"23 broken Amazon links = $890/month lost commission\".',
				'priority' => 2,
			),
		);
	}
	
	/**
	 * Get KB article URL
	 * 
	 * @return string
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/broken-affiliate-links';
	}
	
	/**
	 * Get training video URL
	 * 
	 * @return string
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/broken-affiliate-links';
	}
}
