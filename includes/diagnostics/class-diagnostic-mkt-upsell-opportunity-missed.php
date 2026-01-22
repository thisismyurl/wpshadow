<?php
declare( strict_types=1 );
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missed Upsell Opportunities
 * 
 * Analyzes purchases that lacked upsells. Instant revenue increase potential.
 * 
 * Philosophy: Commandment #9, 7 - Show Value (KPIs) - Track impact, Ridiculously Good - Better than premium
 * Priority: 2 (1=Must-Have, 2=Should-Have, 3=Nice-to-Have)
 * Threat Level: 65/100
 * 
 * Impact: Shows \"Customers who buy X also buy Y 67% of time (missed $4K)\".
 */
class Diagnostic_MktUpsellOpportunityMissed extends Diagnostic_Base {
	
	/**
	 * Get diagnostic ID
	 * 
	 * @return string
	 */
	public static function get_id(): string {
		return 'mkt-upsell-opportunity-missed';
	}
	
	/**
	 * Get diagnostic name
	 * 
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Missed Upsell Opportunities', 'wpshadow' );
	}
	
	/**
	 * Get diagnostic description
	 * 
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Analyzes purchases that lacked upsells. Instant revenue increase potential.', 'wpshadow' );
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
		return 65;
	}
	
	/**
	 * Run the diagnostic
	 * 
	 * @return array Result with status, message, and data
	 */
	public static function run(): array {
		// TODO: Implement mkt-upsell-opportunity-missed diagnostic
		// 
		// This is a KILLER test that delivers "Holy Sh*t" moments:
		// Shows \"Customers who buy X also buy Y 67% of time (missed $4K)\".
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
				'impact' => 'Shows \"Customers who buy X also buy Y 67% of time (missed $4K)\".',
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
		return 'https://wpshadow.com/kb/upsell-opportunity-missed';
	}
	
	/**
	 * Get training video URL
	 * 
	 * @return string
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/upsell-opportunity-missed';
	}
}
