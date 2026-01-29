<?php
/**
 * Multisite Shared Media Library Diagnostic
 *
 * Multisite Shared Media Library misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.942.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Shared Media Library Diagnostic Class
 *
 * @since 1.942.0000
 */
class Diagnostic_MultisiteSharedMediaLibrary extends Diagnostic_Base {

	protected static $slug = 'multisite-shared-media-library';
	protected static $title = 'Multisite Shared Media Library';
	protected static $description = 'Multisite Shared Media Library misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-shared-media-library',
			);
		}
		
		return null;
	}
}
