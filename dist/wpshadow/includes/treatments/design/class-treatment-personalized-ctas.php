<?php
/**
 * Personalized CTAs Treatment
 *
 * Tests whether the site personalizes calls-to-action based on user context to outperform generic CTAs.
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
 * Personalized CTAs Treatment Class
 *
 * Personalized CTAs can outperform generic CTAs by 202%. Tailoring messages
 * to user behavior, source, or stage in the journey dramatically improves conversion.
 *
 * @since 1.6093.1200
 */
class Treatment_Personalized_Ctas extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'personalized-ctas';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Personalized CTAs';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site personalizes calls-to-action based on user context to outperform generic CTAs';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Personalized_Ctas' );
	}
}
