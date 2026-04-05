<?php
/**
 * Treatment: PHP Version
 *
 * Provides guidance for upgrading the PHP version. PHP upgrades must be
 * performed at the hosting or server level; WordPress plugins cannot
 * change the PHP interpreter version.
 *
 * Risk level: n/a (guidance only)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns hosting-level guidance for upgrading the PHP version.
 */
class Treatment_Php_Version extends Treatment_Base {

	/** @var string */
	protected static $slug = 'php-version';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'none';
	}

	/**
	 * Return PHP version upgrade guidance.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		$current_version   = PHP_VERSION;
		$recommended       = '8.2'; // Update as WP requirements evolve.

		return [
			'success' => false,
			'message' => sprintf(
				/* translators: 1: current PHP version, 2: recommended version */
				__(
					"Your current PHP version is %1\$s. WordPress recommends PHP %2\$s or higher.\n\n"
					. "PHP upgrades must be performed by your hosting provider or server admin.\n\n"
					. "BEFORE UPGRADING — Test your site:\n"
					. "  1. Create a staging/test site with the new PHP version.\n"
					. "  2. Check that your theme and all plugins are compatible.\n"
					. "  3. Run the site's main functionality to catch deprecation errors.\n\n"
					. "OPTION 1 — cPanel / Shared Hosting:\n"
					. "  1. Log in to cPanel and find 'MultiPHP Manager' or 'PHP Version'.\n"
					. "  2. Select your domain and choose PHP %2\$s (or the latest available).\n"
					. "  3. Click Apply. The change takes effect immediately.\n"
					. "  4. Check your site for any PHP errors (enable WP_DEBUG temporarily).\n\n"
					. "OPTION 2 — VPS/Dedicated (Debian/Ubuntu):\n"
					. "  sudo add-apt-repository ppa:ondrej/php\n"
					. "  sudo apt-get update\n"
					. "  sudo apt-get install php%2\$s php%2\$s-fpm php%2\$s-mysql php%2\$s-xml php%2\$s-mbstring\n"
					. "  sudo update-alternatives --set php /usr/bin/php%2\$s\n"
					. "  sudo service php%2\$s-fpm restart\n\n"
					. "OPTION 3 — Contact your hosting provider:\n"
					. "  Open a support ticket asking: 'Please upgrade my site to PHP %2\$s.'\n"
					. "  Most quality hosts can do this in minutes.\n\n"
					. "Re-run the WPShadow scan after upgrading.",
					'wpshadow'
				),
				$current_version,
				$recommended
			),
		];
	}

	/**
	 * No state to undo (guidance only).
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		return [
			'success' => true,
			'message' => __( 'This is a guidance-only treatment — no changes were made by WPShadow.', 'wpshadow' ),
		];
	}
}
