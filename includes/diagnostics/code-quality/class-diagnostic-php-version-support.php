<?php
declare(strict_types=1);
/**
 * PHP Version Support Status Diagnostic
 *
 * Philosophy: Platform security - use supported PHP versions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if PHP version is still receiving security updates.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Version_Support extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$php_version = PHP_VERSION;
		$major_minor = substr( $php_version, 0, strrpos( $php_version, '.' ) );
		
		// PHP End of Life dates (as of 2026)
		$eol_dates = array(
			'7.0' => '2019-01-10',
			'7.1' => '2019-12-01',
			'7.2' => '2020-11-30',
			'7.3' => '2021-12-06',
			'7.4' => '2022-11-28',
			'8.0' => '2023-11-26',
			'8.1' => '2024-11-25',
			'8.2' => '2025-12-08',
			'8.3' => '2026-11-23',
		);
		
		if ( isset( $eol_dates[ $major_minor ] ) ) {
			$eol_date = $eol_dates[ $major_minor ];
			$eol_timestamp = strtotime( $eol_date );
			
			// Check if PHP version is past EOL
			if ( time() > $eol_timestamp ) {
				$months_unsupported = floor( ( time() - $eol_timestamp ) / ( 30 * DAY_IN_SECONDS ) );
				
				return array(
					'id'          => 'php-version-support',
					'title'       => 'Unsupported PHP Version',
					'description' => sprintf(
						'Your site runs PHP %s which reached end-of-life on %s (%d months ago). This version no longer receives security patches. Upgrade to PHP 8.1+ immediately.',
						$php_version,
						$eol_date,
						$months_unsupported
					),
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/upgrade-php-version/',
					'training_link' => 'https://wpshadow.com/training/php-security/',
					'auto_fixable' => false,
					'threat_level' => 85,
				);
			}
			
			// Warn if EOL is within 6 months
			$months_until_eol = floor( ( $eol_timestamp - time() ) / ( 30 * DAY_IN_SECONDS ) );
			if ( $months_until_eol < 6 && $months_until_eol > 0 ) {
				return array(
					'id'          => 'php-version-support',
					'title'       => 'PHP Version Approaching End-of-Life',
					'description' => sprintf(
						'Your PHP %s will reach end-of-life on %s (%d months away). Plan your upgrade to PHP 8.1+ now to avoid running unsupported software.',
						$php_version,
						$eol_date,
						$months_until_eol
					),
					'severity'    => 'medium',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/upgrade-php-version/',
					'training_link' => 'https://wpshadow.com/training/php-security/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
			}
		}
		
		return null;
	}

}