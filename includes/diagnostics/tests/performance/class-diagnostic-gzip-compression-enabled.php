<?php
/**
 * GZIP Compression Enabled Diagnostic
 *
 * Verifies GZIP compression is enabled on the web server to reduce
 * file sizes and improve page load performance.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_GZIP_Compression_Enabled Class
 *
 * Checks if GZIP compression is enabled.
 *
 * @since 1.2601.2148
 */
class Diagnostic_GZIP_Compression_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gzip-compression-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GZIP Compression Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GZIP compression is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if GZIP not enabled, null otherwise.
	 */
	public static function check() {
		$gzip_status = self::check_gzip_status();

		if ( $gzip_status['enabled'] ) {
			return null; // GZIP is enabled
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'GZIP compression not enabled. Text files (HTML, CSS, JS) sent uncompressed, wasting 60-70% bandwidth and slowing page loads.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/enable-gzip',
			'family'       => self::$family,
			'meta'         => array(
				'gzip_enabled'         => false,
				'file_size_reduction'  => __( '60-70% smaller text files' ),
				'bandwidth_savings'    => __( '50-60% total bandwidth reduction' ),
				'page_speed_impact'    => __( '30-40% faster page loads' ),
			),
			'details'      => array(
				'how_gzip_works'          => array(
					__( 'Server compresses text files before sending' ),
					__( 'Browser decompresses on arrival' ),
					__( 'HTML, CSS, JS reduced by 60-70%' ),
					__( 'Images (JPG, PNG) already compressed, no benefit' ),
				),
				'compression_examples'    => array(
					'HTML (100KB)' => 'GZIP → 25-30KB (70% reduction)',
					'CSS (50KB)' => 'GZIP → 10-15KB (70% reduction)',
					'JavaScript (200KB)' => 'GZIP → 50-70KB (65% reduction)',
					'Images (JPG/PNG)' => 'Already compressed (no change)',
				),
				'enabling_gzip'           => array(
					'Apache (.htaccess)' => array(
						'Add to .htaccess file:',
						'<IfModule mod_deflate.c>',
						'  AddOutputFilterByType DEFLATE text/html text/css text/javascript',
						'  AddOutputFilterByType DEFLATE application/javascript application/json',
						'</IfModule>',
					),
					'Nginx (nginx.conf)' => array(
						'Add to nginx.conf:',
						'gzip on;',
						'gzip_types text/plain text/css application/json application/javascript;',
						'gzip_min_length 1000;',
						'Restart: sudo systemctl restart nginx',
					),
					'WordPress Plugin' => array(
						'WP Rocket: Enables GZIP automatically',
						'W3 Total Cache: Performance → Browser Cache → Enable HTTP compression',
						'Fast Velocity Minify: Automatically enables GZIP',
					),
					'Hosting Control Panel' => array(
						'cPanel: Software → Optimize Website → Compress All Content',
						'Plesk: Hosting Settings → Apache & nginx → Enable compression',
					),
				),
				'testing_gzip'            => array(
					'Method 1: Online Tool (Easiest)' => array(
						'Visit: https://checkgzipcompression.com/',
						'Enter your site URL',
						'Results show: GZIP enabled or disabled',
					),
					'Method 2: Browser DevTools' => array(
						'Open page → DevTools → Network',
						'Reload page',
						'Click any CSS/JS file',
						'Headers tab → Look for: Content-Encoding: gzip',
					),
					'Method 3: curl Command' => array(
						'curl -H "Accept-Encoding: gzip" -I https://yoursite.com',
						'Look for: content-encoding: gzip',
					),
				),
				'troubleshooting'         => array(
					'.htaccess Not Working' => array(
						'Problem: mod_deflate not enabled',
						'Check: Ask hosting to enable mod_deflate',
						'Alternative: Use plugin instead',
					),
					'Nginx Not Compressing' => array(
						'Problem: gzip module not loaded',
						'Check: nginx -V | grep gzip',
						'Fix: Recompile nginx with gzip',
					),
					'Still Seeing Large Files' => array(
						'Problem: Browser cache showing old uncompressed',
						'Fix: Hard refresh (Ctrl+Shift+R)',
					),
				),
			),
		);
	}

	/**
	 * Check GZIP status.
	 *
	 * @since  1.2601.2148
	 * @return array GZIP status.
	 */
	private static function check_gzip_status() {
		$home_url = home_url();
		$response = wp_remote_get(
			$home_url,
			array(
				'headers' => array(
					'Accept-Encoding' => 'gzip, deflate',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'enabled' => false );
		}

		$headers = wp_remote_retrieve_headers( $response );
		if ( isset( $headers['content-encoding'] ) ) {
			$encoding = strtolower( $headers['content-encoding'] );
			if ( strpos( $encoding, 'gzip' ) !== false || strpos( $encoding, 'deflate' ) !== false ) {
				return array( 'enabled' => true );
			}
		}

		return array( 'enabled' => false );
	}
}
