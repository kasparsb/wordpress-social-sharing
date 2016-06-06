<?php
/*
Plugin Name: Webit Social sharing
Plugin URI: http://webit.lv/socia-sharing
Description: Social sharing buttons
Version: 1.0
Author: Kaspars Bulins
Author URI: http://webit.lv
License: Commercial
*/

include_once(plugin_dir_path(__FILE__).'base.php');
include_once(plugin_dir_path(__FILE__).'facade.php');
include_once(plugin_dir_path(__FILE__).'plugin.php');
include_once(plugin_dir_path(__FILE__).'icons.php');


// Define static facade to theme object
class SocialSharing extends SocialSharing\Facade {}
class SocialIcons extends SocialSharing\Facade {}

// Init facade and create plugin object
SocialSharing::init('SocialSharing\Plugin');
SocialIcons::init('SocialSharing\Icons');