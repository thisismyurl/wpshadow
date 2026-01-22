<?php
/**
 * Notion Terminology
 * 
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'platform'       => 'notion',
	'label'          => __( 'Notion', 'wpshadow' ),
	'terms'          => [
		'post'           => __( 'Page', 'wpshadow' ),
		'page'           => __( 'Page', 'wpshadow' ),
		'plugin'         => __( 'Integration', 'wpshadow' ),
		'theme'          => __( 'Template', 'wpshadow' ),
		'widget'         => __( 'Block', 'wpshadow' ),
		'menu'           => __( 'Sidebar', 'wpshadow' ),
		'category'       => __( 'Database', 'wpshadow' ),
		'tag'            => __( 'Tag', 'wpshadow' ),
		'media_library'  => __( 'Files & Media', 'wpshadow' ),
		'customize'      => __( 'Customize', 'wpshadow' ),
		'dashboard'      => __( 'Workspace', 'wpshadow' ),
		'settings'       => __( 'Settings & Members', 'wpshadow' ),
		'editor'         => __( 'Page', 'wpshadow' ),
		'publish'        => __( 'Share', 'wpshadow' ),
		'draft'          => __( 'Draft', 'wpshadow' ),
		'trash'          => __( 'Trash', 'wpshadow' ),
		'permalink'      => __( 'Share Link', 'wpshadow' ),
		'excerpt'        => __( 'Description', 'wpshadow' ),
		'featured_image' => __( 'Cover', 'wpshadow' ),
		'author'         => __( 'Creator', 'wpshadow' ),
		'comment'        => __( 'Comment', 'wpshadow' ),
	],
	'kb_article'     => 'https://wpshadow.com/kb/notion-to-wordpress/',
	'training_video' => 'https://wpshadow.com/training/notion-to-wordpress/',
];
