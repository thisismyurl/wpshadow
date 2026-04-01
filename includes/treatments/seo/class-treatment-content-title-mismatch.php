<?php
/**
 * Content Title Mismatch Treatment
 *
 * Detects when content doesn't match title promises.
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
 * Content Title Mismatch Treatment Class
 *
 * Detects when titles over-promise and under-deliver (e.g., \"How to\" without
 * steps, \"Complete Guide\" under 1,000 words). Misleading titles increase bounce rate 58%.
 *
 * @since 0.6093.1200
 */
class Treatment_Content_Title_Mismatch extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-title-mismatch';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Content Doesn\'t Match Title Promise';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect over-promising titles with under-delivering content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Title_Mismatch' );
	}
}
