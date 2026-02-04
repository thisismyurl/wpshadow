<?php
/**
 * No GZIP Compression Diagnostic
 *
 * Detects when GZIP compression is not enabled,
 * causing unnecessarily large page transfers.
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
 * Diagnostic: No GZIP Compression
 *
 * Checks whether GZIP compression is enabled
 * to reduce page transfer size.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_GZIP_Compression extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-gzip-compression';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GZIP Compression';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether GZIP compression is enabled';

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
		// Check for GZIP compression
		$homepage = wp_remote_head( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $homepage );
		$has_gzip = isset( $headers['content-encoding'] ) && strpos( $headers['content-encoding'], 'gzip' ) !== false;

		if ( ! $has_gzip ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'GZIP compression isn\'t enabled, which means pages are 60-80% larger than necessary. GZIP compresses text files (HTML, CSS, JavaScript) before sending them to browsers, then browsers decompress them. A 100KB HTML page becomes 20-30KB compressed. This is transparent to users (browsers handle it automatically) and makes pages load 60-80% faster. Most hosts enable this in one click in cPanel or .htaccess.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Page Transfer Size',
					'potential_gain' => '60-80% smaller page size',
					'roi_explanation' => 'GZIP compression reduces text file size by 60-80% with zero effort, directly improving page load speed.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/gzip-compression',
			);
		}

		return null;
	}
}
