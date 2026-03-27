<?php
/**
 * Gzip Compression Enabled Treatment
 *
 * Issue #4966: Gzip Compression Not Enabled
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if server uses Gzip compression.
 * Uncompressed responses are 70% larger and slower.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Gzip_Compression_Enabled Class
 *
 * @since 1.6093.1200
 */
class Treatment_Gzip_Compression_Enabled extends Treatment_Base {

	protected static $slug = 'gzip-compression-enabled';
	protected static $title = 'Gzip Compression Not Enabled';
	protected static $description = 'Checks if server compresses responses with Gzip';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Gzip_Compression_Enabled' );
	}
}
