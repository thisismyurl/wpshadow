<?php
/**
 * No Browser Caching Headers Diagnostic
 *
 * Detects when browser caching headers are missing,
 * causing repeat visitors to re-download unchanged files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Browser Caching Headers
 *
 * Checks whether browser caching headers are set
 * to allow repeat visitors to cache resources.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Browser_Caching_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-browser-caching-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Caching Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether browser caching is configured';

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
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check headers for caching directives
		$homepage = wp_remote_head( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $homepage );
		$has_cache_control = isset( $headers['cache-control'] ) && 
			strpos( $headers['cache-control'], 'max-age' ) !== false;
		$has_expires = isset( $headers['expires'] );

		if ( ! $has_cache_control && ! $has_expires ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Browser caching headers aren\'t set, which means repeat visitors re-download everything every time. Browser caching tells visitors\' browsers: "Keep this CSS file for 1 month, don\'t download it again." For repeat visitors, this makes pages load instantly (files already cached). Good caching headers: Cache-Control: max-age=31536000 for CSS/JS/images, Cache-Control: max-age=3600 for HTML. Most caching plugins set this automatically.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Repeat Visitor Speed',
					'potential_gain' => '80-90% faster for repeat visitors',
					'roi_explanation' => 'Browser caching eliminates re-downloads for repeat visitors, making pages load 80-90% faster.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/browser-caching-headers',
			);
		}

		return null;
	}
}
