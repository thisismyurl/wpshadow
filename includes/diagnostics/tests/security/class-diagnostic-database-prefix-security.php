<?php
/**
 * Database Prefix Security Diagnostic
 *
 * Issue #4911: Database Prefix Still Default "wp_"
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if database prefix is default wp_.
 * Custom prefix adds minor security through obscurity.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Prefix_Security Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Prefix_Security extends Diagnostic_Base {

	protected static $slug = 'database-prefix-security';
	protected static $title = 'Database Prefix Still Default "wp_"';
	protected static $description = 'Checks if database table prefix is customized';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;

		if ( $wpdb->prefix === 'wp_' ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The default "wp_" prefix makes SQL injection attacks slightly easier. Change it to something unique during installation.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-prefix',
				'details'      => array(
					'current_prefix'          => $wpdb->prefix,
					'security_benefit'        => 'Minor defense-in-depth measure (obscurity)',
					'when_to_change'          => 'Set during installation (difficult to change later)',
					'recommendation'          => 'Use random prefix like "wp_a4k9_" for new sites',
					'note'                    => 'This is NOT a substitute for proper SQL injection prevention',
				),
			);
		}

		return null;
	}
}
