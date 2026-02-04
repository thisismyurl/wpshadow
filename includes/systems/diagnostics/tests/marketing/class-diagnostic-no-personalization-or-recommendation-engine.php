<?php
/**
 * No Personalization or Recommendation Engine Diagnostic
 *
 * Detects when personalization is not implemented,
 * missing opportunity to increase engagement and AOV.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Personalization or Recommendation Engine
 *
 * Checks whether personalization or product recommendation
 * engine is implemented.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Personalization_Or_Recommendation_Engine extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-personalization-recommendation-engine';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personalization & Recommendation Engine';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether personalization is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *

	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for personalization plugins
		$has_personalization = is_plugin_active( 'pathwright/pathwright.php' ) ||
			is_plugin_active( 'dynamic-content-for-elementor/dynamic-content-for-elementor.php' ) ||
			is_plugin_active( 'woocommerce-product-recommendations/woocommerce-product-recommendations.php' );

		if ( ! $has_personalization ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not personalizing your content or showing product recommendations, which means everyone sees the same content. Personalization shows different content based on: what they viewed, where they\'re from, what they bought before, what others like them bought. Amazon made $35 billion (35% of revenue) from recommendations. Even basic recommendations like "customers also bought X" or "related articles" increase engagement 20-40% and AOV 10-30%.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Engagement & Revenue',
					'potential_gain' => '+20-40% engagement, +10-30% AOV',
					'roi_explanation' => 'Personalization and recommendations increase engagement and average order value. Amazon credits 35% of revenue to recommendations.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/personalization-recommendation-engine',
			);
		}

		return null;
	}
}
