<?php
/**
 * Open Graph & Meta Tags for Social Sharing
 *
 * Validates proper Open Graph implementation for social media content preview.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Open_Graph_Meta_Tags Class
 *
 * Checks if Open Graph meta tags are properly implemented for social media sharing.
 * These tags control how content appears when shared on Facebook, LinkedIn, and other platforms.
 *
 * @since 0.6093.1200
 */
class Treatment_Open_Graph_Meta_Tags extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'open-graph-meta-tags';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Meta Tags';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Open Graph implementation for social sharing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Open_Graph_Meta_Tags' );
	}
}
