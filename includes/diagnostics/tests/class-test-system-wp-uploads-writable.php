<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_WP_Uploads_Writable extends Diagnostic_Base {
    public static function check(): ?array {
        $upload_dir = wp_upload_dir();
        $path = $upload_dir['basedir'];
        
        if (!is_writable($path)) {
            return array(
                'id' => 'uploads-not-writable',
                'title' => 'Uploads directory not writable',
                'threat_level' => 70
            );
        }
        return null;
    }

    public static function test_live_system_wp_uploads_writable(): array {
        $result = self::check();
        return array(
            'passed' => $result === null,
            'message' => $result === null ? 'Uploads writable' : 'Not writable'
        );
    }
}
