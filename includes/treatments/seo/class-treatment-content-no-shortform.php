<?php
/**
 * Content No Short-Form Treatment
 *
 * Identifies sites with no short-form content opportunity.
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
 * Content No Short-Form Treatment Class
 *
 * Detects sites with only long-form content (all posts > 1,500 words) that miss
 * opportunities for quick, high-velocity content serving different user intents.
 *
 * @since 1.6093.1200
 */
class Treatment_Content_No_Shortform extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-shortform';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Short-Form Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identify sites with only long-form content missing short-form opportunities';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_No_Shortform' );
	}
}
