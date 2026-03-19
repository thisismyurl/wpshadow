<?php
/**
 * Media Attachment Page SEO Treatment
 *
 * Tests SEO optimization of media attachment pages by
 * checking indexing controls and title handling.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Attachment_Page_SEO Class
 *
 * Validates SEO controls for attachment pages.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Attachment_Page_SEO extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-attachment-page-seo';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Attachment Page SEO';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests SEO optimization of media attachment pages';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Attachment_Page_SEO' );
	}
}
