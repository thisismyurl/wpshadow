<?php
/**
 * Abandoned Feature Removal Not Tracked Diagnostic
 *
 * Checks feature removal.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Abandoned_Feature_Removal_Not_Tracked Class
 *
 * Performs diagnostic check for Abandoned Feature Removal Not Tracked.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Abandoned_Feature_Removal_Not_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'abandoned-feature-removal-not-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Abandoned Feature Removal Not Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks feature removal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'track_abandoned_features' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Abandoned feature removal tracking is not configured yet. Gradual deprecation tracking helps avoid sudden user impact.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 5,
				'auto_fixable' => false,
				'details'     => array(
					'explanation_sections' => array(
						'summary'              => __( 'This diagnostic checks whether your site has a simple tracking step for features you are phasing out. In everyday terms, it helps you avoid surprises by keeping a clear record of what is being retired and when.', 'wpshadow' ),
						'how_wp_shadow_tested' => __( 'WPShadow looked for the WordPress hook that indicates your site is recording abandoned feature removals during initialization. If that tracking hook is missing, WPShadow reports this as a warning so you can add a safer transition process.', 'wpshadow' ),
						'why_it_matters'       => __( 'Without retirement tracking, old features can disappear without clear visibility, which may confuse visitors or teammates and create hard-to-debug issues later. Tracking this process makes updates more predictable and protects trust in your site experience.', 'wpshadow' ),
						'how_to_fix_it'        => __( 'Create a lightweight deprecation tracking step in your codebase, then log each feature retirement with a planned timeline and owner. Start with one shared checklist your team can follow for every removal, and rerun this diagnostic to confirm the tracking hook is active.', 'wpshadow' ),
					),
				),
				'kb_link'     => 'https://wpshadow.com/kb/abandoned-feature-removal-not-tracked?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
