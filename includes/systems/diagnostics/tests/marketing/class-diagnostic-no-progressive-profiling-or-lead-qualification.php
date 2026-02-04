<?php
/**
 * Progressive Profiling and Lead Qualification Diagnostic
 *
 * Detects when progressive profiling or lead qualification
 * systems are not implemented to segment and qualify leads.
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
 * Diagnostic: No Progressive Profiling or Lead Qualification
 *
 * Checks whether progressive profiling or lead qualification
 * systems are in place to segment and qualify prospects.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Progressive_Profiling_Or_Lead_Qualification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-progressive-profiling-qualification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive Profiling & Lead Qualification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether progressive profiling or lead qualification is implemented';

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
		// Check for CRM or automation platforms
		$has_profiling_system = is_plugin_active( 'fluentcrm/fluent-crm.php' ) ||
			is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
			is_plugin_active( 'formidable-forms/formidable.php' ) ||
			is_plugin_active( 'gravity-forms/gravity-forms.php' );

		// Check for custom implementation
		$has_custom_profiling = get_option( 'wpshadow_lead_qualification_system' );

		if ( ! $has_profiling_system && ! $has_custom_profiling ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not qualifying your leads systematically yet. Think of it as the difference between a restaurant that serves everyone the same menu vs. one that asks "Vegetarian or meat?" to provide better recommendations. Progressive profiling asks a few questions at a time to segment leads: budget, company size, timeline, pain points. This lets you send targeted content that\'s more likely to convert. Sales can focus on qualified leads instead of unqualified prospects.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Lead Quality & Conversion',
					'potential_gain' => '25-40% better conversion rates',
					'roi_explanation' => 'Lead qualification increases conversion by 25-40% by ensuring that sales focuses on qualified prospects and marketing sends relevant content.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/progressive-profiling-lead-qualification',
			);
		}

		return null;
	}
}
