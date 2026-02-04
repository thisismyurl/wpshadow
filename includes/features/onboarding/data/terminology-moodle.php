<?php
/**
 * Moodle Terminology
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'platform'       => 'moodle',
	'label'          => __( 'Moodle', 'wpshadow' ),
	'terms'          => array(
		'post'           => __( 'Forum Post', 'wpshadow' ),
		'page'           => __( 'Page', 'wpshadow' ),
		'plugin'         => __( 'Plugin', 'wpshadow' ),
		'theme'          => __( 'Theme', 'wpshadow' ),
		'widget'         => __( 'Block', 'wpshadow' ),
		'menu'           => __( 'Navigation', 'wpshadow' ),
		'category'       => __( 'Course Category', 'wpshadow' ),
		'tag'            => __( 'Tag', 'wpshadow' ),
		'media_library'  => __( 'File Repository', 'wpshadow' ),
		'customize'      => __( 'Settings', 'wpshadow' ),
		'dashboard'      => __( 'Dashboard', 'wpshadow' ),
		'settings'       => __( 'Site Administration', 'wpshadow' ),
		'editor'         => __( 'Text Editor', 'wpshadow' ),
		'publish'        => __( 'Make Available', 'wpshadow' ),
		'draft'          => __( 'Hidden', 'wpshadow' ),
		'trash'          => __( 'Delete', 'wpshadow' ),
		'permalink'      => __( 'URL', 'wpshadow' ),
		'excerpt'        => __( 'Summary', 'wpshadow' ),
		'featured_image' => __( 'Course Image', 'wpshadow' ),
		'author'         => __( 'Teacher', 'wpshadow' ),
		'comment'        => __( 'Forum Comment', 'wpshadow' ),
	),
	'kb_article'     => \WPShadow\Core\UTM_Link_Manager::kb_link( 'moodle-to-wordpress', 'onboarding' ),
	'training_video' => \WPShadow\Core\UTM_Link_Manager::academy_link( 'moodle-to-wordpress', 'onboarding' ),
);
