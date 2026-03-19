<?php
/**
 * Discussion Settings Creating Spam Risk Treatment
 *
 * Tests for discussion and notification settings.
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
 * Discussion Settings Creating Spam Risk Treatment Class
 *
 * Tests for discussion and notification configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_Discussion_Settings_Creating_Spam_Risk extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'discussion-settings-creating-spam-risk';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Discussion Settings Creating Spam Risk';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for discussion and notification settings';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Discussion_Settings_Creating_Spam_Risk' );
	}
}
