<?php
/**
 * Help card: Diagnostics
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'diagnostics',
	'order'       => 20,
	'section'     => 'resources',
	'width'       => 'half',
	'title'       => __( 'Understanding Diagnostics', 'wpshadow' ),
	'description' => __( 'Discover what each diagnostic check does and what issues it helps identify.', 'wpshadow' ),
	'icon'        => 'dashicons-search',
	'url'         => UTM_Link_Manager::kb_link( 'diagnostics', 'help_page' ),
	'video'       => 'https://wpshadow.com/academy/diagnostics?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_diagnostics',
);
