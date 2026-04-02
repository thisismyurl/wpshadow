<?php
/**
 * Treatment: Missing Nofollow on Affiliate Links
 *
 * Detects affiliate links without rel="nofollow" or rel="sponsored", which
 * violates FTC guidelines and risks Google penalties.
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
 * Affiliate No Nofollow Treatment Class
 *
 * Checks for proper affiliate link attributes.
 *
 * Detection methods:
 * - Affiliate URL pattern matching
 * - rel attribute verification
 * - Affiliate link plugins
 *
 * @since 1.6093.1200
 */
class Treatment_Affiliate_No_Nofollow extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'affiliate-no-nofollow';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Nofollow on Affiliate Links';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'FTC violation, Google penalty risk - Must use rel="sponsored"';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'external-linking';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Affiliate link plugin installed
	 * - 2 points: No affiliate links without proper rel
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Affiliate_No_Nofollow' );
	}
}
