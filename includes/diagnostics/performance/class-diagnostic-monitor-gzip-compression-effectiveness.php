<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Gzip_Compression_Effectiveness extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-gzip', 'title' => __('Gzip Compression Effectiveness', 'wpshadow'), 'description' => __('Verifies gzip enabled and compressing resources. 60-70% compression typical; low = misconfiguration.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/compression/', 'training_link' => 'https://wpshadow.com/training/compression-setup/', 'auto_fixable' => false, 'threat_level' => 5]; } 
}