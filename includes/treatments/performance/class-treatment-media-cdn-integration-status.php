<?php
/**
 * Media CDN Integration Status Treatment
 *
 * Tests whether a CDN is serving media files by validating
 * URL rewriting and CDN plugin configuration.
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
 * Treatment_Media_CDN_Integration_Status Class
 *
 * Checks for CDN plugins and verifies media URLs are
 * being rewritten to CDN hosts.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_CDN_Integration_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cdn-integration-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Integration Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CDN is properly serving media files';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_CDN_Integration_Status' );
	}
}
