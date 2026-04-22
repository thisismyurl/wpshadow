<?php
/**
 * Uninstaller for heic support
 * .
 * 
 * Updated: 1.251229
 * 
 */



if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wp_filesystem;
if ( empty( $wp_filesystem ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
}

$upload_dir = wp_upload_dir();
$backup_dir = $upload_dir['basedir'] . '/heic-backups/';

if ( $wp_filesystem->exists( $backup_dir ) ) {
    $wp_filesystem->delete( $backup_dir, true );
}

delete_metadata( 'post', 0, '_heic_original_path', '', true );
wp_cache_flush();