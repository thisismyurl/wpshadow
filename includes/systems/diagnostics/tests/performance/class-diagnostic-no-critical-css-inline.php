<?php
/**
 * No Critical CSS Inline Diagnostic
 *
 * Detects when critical CSS is not inlined,
 * causing render-blocking CSS to delay first paint.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Critical CSS Inline
 *
 * Checks whether critical CSS is inlined
 * to speed up initial page rendering.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Critical_CSS_Inline extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-critical-css-inline';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Inlining';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether critical CSS is inlined';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

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
		// Check homepage for inline critical CSS
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Look for critical CSS plugins
		$has_critical_css_plugin = is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			is_plugin_active( 'fast-velocity-minify/fvm.php' );

		// Check for inline style tag in head (indicator of critical CSS)
		$has_inline_critical = preg_match( '/<head[^>]*>.*?<style[^>]*>.*?<\/style>.*?<\/head>/is', $body );

		if ( ! $has_critical_css_plugin && ! $has_inline_critical ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Critical CSS isn\'t inlined, which delays initial page rendering. Critical CSS is the minimum CSS needed to render above-the-fold content. By inlining it in <head>, pages appear instantly while full CSS loads in background. Without it, browsers wait for entire CSS file before showing anything. Critical CSS optimization improves First Contentful Paint by 30-50%. Advanced optimization plugins handle this automatically.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'First Paint Speed',
					'potential_gain' => '+30-50% faster first contentful paint',
					'roi_explanation' => 'Critical CSS inlining speeds up initial rendering by 30-50%, improving perceived load time.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/critical-css-inline',
			);
		}

		return null;
	}
}
