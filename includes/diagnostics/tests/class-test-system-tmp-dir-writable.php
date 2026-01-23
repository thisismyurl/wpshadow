<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_Tmp_Dir_Writable extends Diagnostic_Base {
    public static function check(): ?array {
        $tmp = ini_get('upload_tmp_dir');
        if (empty($tmp)) {
            $tmp = sys_get_temp_dir();
        }
        
        if (!is_writable($tmp)) {
            return array(
                'id' => 'tmp-not-writable',
                'title' => 'Temp directory not writable',
                'threat_level' => 60
            );
        }
        return null;
    }

    public static function test_live_system_tmp_dir_writable(): array {
        $result = self::check();
        return array(
            'passed' => $result === null,
            'message' => $result === null ? 'Temp writable' : 'Not writable'
        );
    }
}
