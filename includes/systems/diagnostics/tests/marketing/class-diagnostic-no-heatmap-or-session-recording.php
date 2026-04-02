<?php
/**
 * No Heatmap or Session Recording Diagnostic
 *
 * Detects when user behavior tracking is not implemented,
 * missing insights into how users interact with site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Heatmap or Session Recording
 *
 * Checks whether user behavior tracking tools
 * are implemented for UX insights.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Heatmap_Or_Session_Recording extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-heatmap-session-recording';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heatmap & Session Recording';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether behavior tracking exists';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for behavior tracking plugins
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for common tracking services
		$has_tracking = preg_match( '/hotjar|crazyegg|mouseflow|fullstory|clarity\.ms/i', $body );

		if ( ! $has_tracking ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not tracking user behavior, which means flying blind on UX issues. Heatmaps show: where users click, how far they scroll, where attention goes. Session recordings replay: real user sessions, see where users struggle, identify confusing elements. These tools reveal: broken elements users click expecting action, forms abandoned at specific fields, content users skip. Services: Hotjar, Microsoft Clarity (free), Crazy Egg. Start with free tools.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'UX Insights & Conversion Optimization',
					'potential_gain' => 'Identify and fix UX issues causing abandonment',
					'roi_explanation' => 'Heatmaps and session recordings reveal exactly where users struggle, enabling targeted improvements.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/heatmap-session-recording',
			);
		}

		return null;
	}
}
