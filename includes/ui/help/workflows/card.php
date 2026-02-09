<?php
/**
 * Help card: Workflows
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'workflows',
	'order'       => 40,
	'section'     => 'resources',
	'width'       => 'half',
	'title'       => __( 'Workflows & Automation', 'wpshadow' ),
	'description' => __( 'Set up automated workflows to keep your site healthy without manual intervention.', 'wpshadow' ),
	'icon'        => 'dashicons-schedule',
	'url'         => UTM_Link_Manager::kb_link( 'workflows', 'help_page' ),
	'video'       => 'https://wpshadow.com/academy/workflows?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_workflows',
);
