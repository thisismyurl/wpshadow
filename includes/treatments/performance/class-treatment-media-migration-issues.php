<?php
/**
 * Media Migration Issues Treatment
 *
 * Detects broken media links after site migrations by
 * checking for hardcoded or mismatched domains.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Migration_Issues Class
 *
 * Identifies media URLs that reference old domains or
 * cause mixed content after migrations.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_Migration_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-migration-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Migration Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects broken media links after migrations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Migration_Issues' );
	}
}
