<?php
/**
 * GZIP/Brotli Compression Treatment
 *
 * Checks if text compression is enabled for faster transfers.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GZIP/Brotli Compression Treatment Class
 *
 * Verifies text compression is enabled. Compression reduces
 * transfer size by 70-80% for text resources.
 *
 * @since 0.6093.1200
 */
class Treatment_GZIP_Brotli_Compression extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gzip-brotli-compression';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'GZIP/Brotli Compression';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if text compression is enabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests server response headers for compression.
	 * GZIP/Brotli can reduce transfer by 70-80%.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_GZIP_Brotli_Compression' );
	}
}
