<?php
/**
 * Gzip Compression Enabled Diagnostic
 *
 * Issue #4966: Gzip Compression Not Enabled
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if server uses Gzip compression.
 * Uncompressed responses are 70% larger and slower.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Gzip_Compression_Enabled Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Gzip_Compression_Enabled extends Diagnostic_Base {

	protected static $slug = 'gzip-compression-enabled';
	protected static $title = 'Gzip Compression Not Enabled';
	protected static $description = 'Checks if server compresses responses with Gzip';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Enable Gzip compression in server configuration', 'wpshadow' );
		$issues[] = __( 'Compress HTML, CSS, JavaScript, XML, JSON', 'wpshadow' );
		$issues[] = __( 'Do NOT compress images (already compressed)', 'wpshadow' );
		$issues[] = __( 'Apache: mod_deflate or mod_gzip', 'wpshadow' );
		$issues[] = __( 'Nginx: gzip on; gzip_types text/css text/javascript;', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Gzip reduces text file sizes by 70%. Without compression, pages load slower and waste bandwidth.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/gzip-compression',
				'details'      => array(
					'recommendations'         => $issues,
					'size_reduction'          => '70% smaller for text files',
					'check_header'            => 'Content-Encoding: gzip',
					'apache_htaccess'         => 'AddOutputFilterByType DEFLATE text/html text/css application/javascript',
				),
			);
		}

		return null;
	}
}
