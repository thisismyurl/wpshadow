<?php
/**
 * Content Reading Level Too Low Treatment
 *
 * Detects when content is overly simple for target audience.
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
 * Content Reading Level Too Low Treatment Class
 *
 * For technical/professional audiences, overly simple content damages credibility.
 * Grade 6 content on developer blog reduces authority.
 *
 * @since 1.6093.1200
 */
class Treatment_Content_Reading_Level_Too_Low extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-reading-level-too-low';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Level Too Low';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect when content is overly simple for professional/technical audiences';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Reading_Level_Too_Low' );
	}
}
