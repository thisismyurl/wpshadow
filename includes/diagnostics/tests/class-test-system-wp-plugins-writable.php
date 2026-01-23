<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_WP_Plugins_Writable extends Diagnostic_Base {
    public static function check(): ?array {
        $path = WP_PLUGIN_DIR;
        if (!is_writable($path)) {
            return array(
                'id' => 'plugins-not-writable',
                'title' => 'Plugins directory not writable',
                'threat_level' => 60
            );
        }
        return null;
    }

    public static function test_live_system_wp_plugins_writable(): array {
        $result = self::check();
        return array(
            'passed' => $result === null,
            'message' => $result === null ? 'Plugins writable' : 'Not writable'
        );
    }
}
