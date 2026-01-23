<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Meta extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-meta_tag_keyword_alignment', 'title' => __('Meta Tag Keyword Alignment', 'wpshadow'), 'description' => __('Ensures meta description contains target keywords. Misalignment = lower CTR.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}