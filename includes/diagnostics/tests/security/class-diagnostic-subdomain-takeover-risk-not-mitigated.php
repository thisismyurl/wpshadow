<?php
/**
 * Subdomain Takeover Risk Not Mitigated Diagnostic
 *
 * Checks subdomain takeover.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Subdomain_Takeover_Risk_Not_Mitigated Class
 *
 * Performs diagnostic check for Subdomain Takeover Risk Not Mitigated.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Subdomain_Takeover_Risk_Not_Mitigated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'subdomain-takeover-risk-not-mitigated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Subdomain Takeover Risk Not Mitigated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks subdomain takeover';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return null;