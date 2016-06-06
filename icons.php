<?php

namespace SocialSharing;

class Icons {

    private $images_url = '';

    public function __construct() {
        $this->images_url = plugin_dir_url(__FILE__).'assets/images/';
    }
    
    public function facebook($attr=[]) {
        $attr['src'] = $this->images_url.'facebook.png';
        echo sprintf('<img %s />', $this->attributes_html($attr));
    }

    public function twitter($attr=[]) {
        $attr['src'] = $this->images_url.'twitter.png';
        echo sprintf('<img %s />', $this->attributes_html($attr));
    }

    public function draugiem($attr=[]) {
        $attr['src'] = $this->images_url.'draugiem.png';
        echo sprintf('<img %s />', $this->attributes_html($attr));
    }

    public function email($attr=[]) {
        $attr['src'] = $this->images_url.'email.png';
        echo sprintf('<img %s />', $this->attributes_html($attr));
    }

    private function attributes_html($attr) {
        $h = [];
        foreach ($attr as $key => $val) {
            $h[] = sprintf('%s="%s"', $key, $val);
        }
        return implode(' ', $h);
    }
}