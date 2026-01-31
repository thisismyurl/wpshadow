<?php
/**
 * Comment Threading Depth Limit Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_Threading_Depth_Limit extends Diagnostic_Base {
	protected static $slug = 'comment-threading-depth-limit';
	protected static $title = 'Comment Threading Depth Limit';
	protected static $description = 'Verifies comment threading depth not excessive';
	protected static $family = 'performance';

	public static function check() {
		$thread_depth = (int) get_option( 'thread_comments_depth', 5 );

		if ( $thread_depth > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Comment threading depth set to %d (recommended: 5-10) - may cause UI/performance issues', 'wpshadow' ),
					$thread_depth
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-threading-depth-limit',
			);
		}
		return null;
	}
}
