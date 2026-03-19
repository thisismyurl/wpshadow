<?php
/**
 * Content No Long-Form Treatment
 *
 * Detects absence of long-form content for SEO authority.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content No Long-Form Treatment Class
 *
 * Detects absence of long-form content (2,000+ words) which ranks better,
 * earns more backlinks, and establishes greater authority.
 *
 * @since 1.6093.1200
 */
class Treatment_Content_No_Longform extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-longform';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Long-Form Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect missing long-form content proven to rank better and earn backlinks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_No_Longform' );
	}
}
