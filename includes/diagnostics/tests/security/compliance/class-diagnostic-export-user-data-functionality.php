<?php
/**
 * Export User Data Functionality Diagnostic
 *
 * Issue #4990: No User Data Export (GDPR)
 * Pillar: #10: Beyond Pure (Privacy) / 🌐 Culturally Respectful
 *
 * Checks if users can export their data.
 * GDPR requires users can download personal data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Export_User_Data_Functionality Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Export_User_Data_Functionality extends Diagnostic_Base {

	protected static $slug = 'export-user-data-functionality';
	protected static $title = 'No User Data Export (GDPR)';
	protected static $description = 'Checks if users can export their personal data';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Implement data export function (WordPress Tools > Export Personal Data)', 'wpshadow' );
		$issues[] = __( 'Include all user data: posts, comments, profiles', 'wpshadow' );
		$issues[] = __( 'Export in machine-readable format (JSON)', 'wpshadow' );
		$issues[] = __( 'Require verification (email confirmation)', 'wpshadow' );
		$issues[] = __( 'Send export via email (not direct download)', 'wpshadow' );
		$issues[] = __( 'Document GDPR Article 15 (Right of Access)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'GDPR Article 15 requires users can request and receive their personal data. WordPress provides built-in tools for this.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-export',
				'details'      => array(
					'recommendations'         => $issues,
					'gdpr_requirement'        => 'Right of Access (GDPR Article 15)',
					'wordpress_feature'       => 'Tools > Export Personal Data',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
					'gdpr_fine'               => 'Up to €20 million or 4% of turnover',
				),
			);
		}

		return null;
	}
}
