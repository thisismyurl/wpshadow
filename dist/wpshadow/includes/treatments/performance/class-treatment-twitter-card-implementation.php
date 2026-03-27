<?php
/**
 * Twitter Card Implementation for Social Sharing
 *
 * Validates Twitter Card meta tags for optimal Twitter thread sharing.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Twitter_Card_Implementation Class
 *
 * Checks for proper Twitter Card implementation which controls content previews on Twitter/X.
 *
 * @since 1.6093.1200
 */
class Treatment_Twitter_Card_Implementation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'twitter-card-implementation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Twitter Card Implementation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Twitter Card setup for social sharing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Twitter_Card_Implementation' );
	}
}
