<?php
/**
 * OptinMonster Campaign Publishing Diagnostic
 *
 * OptinMonster campaigns not published or not displaying.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.219.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OptinMonster Campaign Publishing Diagnostic Class
 *
 * @since 1.219.0000
 */
class Diagnostic_OptinmonsterCampaignPublishing extends Diagnostic_Base {

	protected static $slug = 'optinmonster-campaign-publishing';
	protected static $title = 'OptinMonster Campaign Publishing';
	protected static $description = 'OptinMonster campaigns not published or not displaying';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'OMAPI_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/optinmonster-campaign-publishing',
			);
		}
		
		return null;
	}
}
