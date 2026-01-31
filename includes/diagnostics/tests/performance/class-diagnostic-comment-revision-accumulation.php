<?php
/**
 * Comment Revision Accumulation Diagnostic
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

class Diagnostic_Comment_Revision_Accumulation extends Diagnostic_Base {
	protected static $slug = 'comment-revision-accumulation';
	protected static $title = 'Comment Revision Accumulation';
	protected static $description = 'Detects excessive comment revisions in database';
	protected static $family = 'performance';

	public static function check() {
		// WordPress doesn't have built-in comment revisions, but plugins might.
		global $wpdb;

		// Check for comment history/revision plugins.
		$has_revision_plugin = class_exists( 'Simple_Comment_Editing' ) ||
		                       class_exists( 'Comment_Edit_Core' );

		if ( ! $has_revision_plugin ) {
			return null;
		}

		// Check for revision-like meta keys.
		$revision_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->commentmeta}
			WHERE meta_key LIKE '%revision%'
			OR meta_key LIKE '%history%'
			OR meta_key LIKE '%backup%'"
		);

		if ( $revision_meta > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d comment revision entries - consider cleanup to improve performance', 'wpshadow' ),
					$revision_meta
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-revision-accumulation',
			);
		}

		return null;
	}
}
