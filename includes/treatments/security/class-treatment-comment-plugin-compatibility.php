<?php
/**
 * Comment Plugin Compatibility Treatment
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Comment_Plugin_Compatibility extends Treatment_Base {
	protected static $slug = 'comment-plugin-compatibility';
	protected static $title = 'Comment Plugin Compatibility';
	protected static $description = 'Checks if comment plugins conflict with core';
	protected static $family = 'security';

	public static function check() {
		$comment_plugins = array(
			'Disqus'              => class_exists( 'Disqus' ),
			'Jetpack Comments'    => class_exists( 'Jetpack_Comments' ),
			'wpDiscuz'            => class_exists( 'WpdiscuzCore' ),
			'GraphComment'        => function_exists( 'graphcomment_init' ),
			'CommentLuv'          => defined( 'COMMENTLUV_VERSION' ),
		);

		$active = array_filter( $comment_plugins );
		if ( count( $active ) > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Multiple comment plugins active may cause conflicts: %s', 'wpshadow' ),
					implode( ', ', array_keys( $active ) )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-plugin-compatibility',
			);
		}
		return null;
	}
}
