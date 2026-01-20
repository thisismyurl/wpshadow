<?php
/**
 * SSL Certificate Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check SSL/HTTPS configuration.
 */
class Diagnostic_SSL {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( ! is_ssl() ) {
			return array(
				'id'           => 'ssl-missing',
				'title'        => 'SSL Certificate Not Active',
				'description'  => 'Your site is not using HTTPS. This reduces security and may impact SEO.',
				'color'        => '#f44336',
				'bg_color'     => '#ffebee',
				'kb_link'      => 'https://wpshadow.com/kb/enable-https-ssl-on-your-site/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ssl',
				'auto_fixable' => true,
				'threat_level' => 90,
			);
		}
		
		return null;
	}
}
