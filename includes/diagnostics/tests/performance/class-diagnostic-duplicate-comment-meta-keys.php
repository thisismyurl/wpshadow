<?php
/**
 * Duplicate Comment Meta Keys Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Duplicate_Comment_Meta_Keys extends Diagnostic_Base {
	protected static $slug = 'duplicate-comment-meta-keys';
	protected static $title = 'Duplicate Comment Meta Keys';
	protected static $description = 'Identifies duplicate comment meta entries';
	protected static $family = 'performance';

	public static function check() {
		global $wpdb;

		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT comment_id, meta_key, COUNT(*) as cnt
				FROM {$wpdb->commentmeta}
				GROUP BY comment_id, meta_key
				HAVING cnt > 1
			) as dups"
		);

		if ( $duplicates > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d duplicate comment meta entries - may slow queries', 'wpshadow' ),
					$duplicates
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/duplicate-comment-meta-keys',
			);
		}

		return null;
	}
}
