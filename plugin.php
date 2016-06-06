<?php

namespace SocialSharing;

class Plugin extends Base {

    public $allowed_post_types = ['post'];

    public function __construct() {
        parent::__construct();

        add_shortcode('socialsharing', [$this, 'shortcode_sharing']);
        add_shortcode('socialfollow', [$this, 'shortcode_follow']);

        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_post']);

        add_action('admin_menu', [$this, 'admin_menu']);
        
        //add_action('admin_enqueue_scripts', [$this, 'scripts_styles']);
        add_action('wp_enqueue_scripts', [$this, 'scripts_styles']);
    }

    public function admin_menu() {
        $hook = add_submenu_page(
            'options-general.php',
            'Social sharing',
            'Social sharing',
            'publish_posts',
            'socialsharing',
            [$this, 'page_settings']
        );

        add_action('load-'.$hook, [$this, 'do_page_settings']);
    }

    public function scripts_styles() {
        wp_register_style('socialsharing', plugins_url( 'build/app-'.$this->package('version').'.css' , __FILE__ ));
        wp_register_script('socialsharing', plugins_url( 'build/app-'.$this->package('version').'.js' , __FILE__ ), ['jquery']);
        
        wp_enqueue_style('socialsharing');
        wp_enqueue_script('socialsharing');

        //wp_register_script('postscollections-main', plugins_url( 'assets/main.js', __FILE__ ), ['jquery', 'jquery-ui-sortable'], '1.8');
        //wp_localize_script('postscollections-main', 'postscollections', ['ajax_url' => admin_url( 'admin-ajax.php' )]);
    }

    /**
     * Link sharing
     */
    public function shortcode_sharing($atts) {
        $atts = shortcode_atts([
            'title' => 'Iesaki šo rakstu citiem',
            'size' => 'normal',
            'sharing_count' => null,
            'show_labels' => true,
            'show_icons' => true,
            'show_sharing_count' => true,
        ], $atts, 'socialsharing');

        foreach (['show_labels', 'show_icons', 'show_sharing_count'] as $bool_param) {
            $atts[$bool_param] = filter_var($atts[$bool_param], FILTER_VALIDATE_BOOLEAN);
        }

        // Share link
        $atts['method'] = 'share';


        global $post;

        // Ja nav padots sharing_count caur shortcode params, tad nolasām no posta
        if (!$atts['sharing_count']) {
            if ($post && $post->ID) {
                $atts['sharing_count'] = $this->get_sharing_count($post->ID);
            }
        }

        return $this->shortcode_html($atts);
    }

    /**
     * Social profile follow
     */
    public function shortcode_follow($atts) {
        $atts = shortcode_atts([
            'title' => '',
            'size' => 'normal',
            'show_labels' => true,
            'show_icons' => true
        ], $atts, 'socialfollow');

        foreach (['show_labels', 'show_icons'] as $bool_param) {
            $atts[$bool_param] = filter_var($atts[$bool_param], FILTER_VALIDATE_BOOLEAN);
        }

        $atts['sharing_count'] = null;
        $atts['show_sharing_count'] = false;

        // Show social profiles follow links
        $atts['method'] = 'follow';


        // Sociālo profilu linki
        $atts['links'] = [
            'facebook' => [
                'href' => get_option('wb_socialfollow_link_facebook', 'http://facebook.com'),
                'label' => get_option('wb_socialfollow_label_facebook', 'Facebook'),
                'target' => get_option('wb_socialfollow_target_facebook', '_blank')
            ],
            'draugiem' => [
                'href' => get_option('wb_socialfollow_link_draugiem', 'http://draugiem.lv'),
                'label' => get_option('wb_socialfollow_label_draugiem', 'Draugiem'),
                'target' => get_option('wb_socialfollow_target_draugiem', '_blank')
            ],
            'twitter' => [
                'href' => get_option('wb_socialfollow_link_twitter', 'http://twitter.com'),
                'label' => get_option('wb_socialfollow_label_twitter', 'Twitter'),
                'target' => get_option('wb_socialfollow_target_twitter', '_blank')
            ]
        ];

        // Social profiles labels
        $atts['labels'] = [
            'facebook' => get_option('wb_socialfollow_label_facebook', 'Facebook'),
            'draugiem' => get_option('wb_socialfollow_label_draugiem', 'Draugiem'),
            'twitter' => get_option('wb_socialfollow_label_twitter', 'Twitter')
        ];

        return $this->shortcode_html($atts);
    }

    private function shortcode_html($atts) {
        extract($atts);

        $h = '';
        ob_start();

        include($this->path.'shortcode-html.php');

        $h = ob_get_contents();
        ob_end_clean();
        return $h;
    }

    public function save_post( $post_id ) {
        // Check post type
        if ($this->is_allowed_post_type()) {
            
            if (!$this->verify_nonce_metbox('socialsharing')) {
                return $post_id;
            }
            
            // If this is an autosave, our form has not been submitted, so we don't want to do anything.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            $count = filter_input(INPUT_POST, 'socialsharing_count', FILTER_SANITIZE_NUMBER_INT);
            
            update_post_meta($post_id, '_socialsharing_count', $count);
        }
    }

    public function add_metabox() {
        foreach ($this->allowed_post_types as $post_type) {
            add_meta_box(
                'socialsharing',
                __( 'Sharing', 'socialsharing' ),
                [$this, 'metabox'],
                $post_type,
                'side'
            );
        }
    }

    public function metabox($post) {
        $this->nonce_field_metabox('socialsharing');
        ?>
        <div>
            <input type="text" value="<?php echo $this->get_sharing_count($post->ID) ?>" name="socialsharing_count" />
        </div>
        <?php
    }

    public function get_sharing_count($post_id) {
        $c = intval(get_post_meta($post_id, '_socialsharing_count', true));
        return $c === 0 ? '' : $c;
    }

    public function page_settings() {
        $form_action = 'options-general.php?page=socialsharing&amp;action=save';

        $settings = [
            'facebook' => [
                'caption' => 'Facebook',
                'link' => get_option('wb_socialfollow_link_facebook', 'http://facebook.com'),
                'label' => get_option('wb_socialfollow_label_facebook', 'Facebook'),
                'target' => get_option('wb_socialfollow_target_facebook', '_blank')
            ],
            'twitter' => [
                'caption' => 'Twitter',
                'link' => get_option('wb_socialfollow_link_twitter', 'http://twitter.com'),
                'label' => get_option('wb_socialfollow_label_twitter', 'Twitter'),
                'target' => get_option('wb_socialfollow_target_twitter', '_blank')
            ],
            'draugiem' => [
                'caption' => 'Draugiem',
                'link' => get_option('wb_socialfollow_link_draugiem', 'http://draugiem.lv'),
                'label' => get_option('wb_socialfollow_label_draugiem', 'Draugiem'),
                'target' => get_option('wb_socialfollow_target_draugiem', '_blank')
            ],
        ];

        include($this->path.'page-settings.php');
    }

    public function do_page_settings() {
        if (filter_input(INPUT_GET, 'action') != 'save') {
            return;
        }

        $links = filter_input(INPUT_POST, 'follow_link', FILTER_SANITIZE_STRING, ['flags' => FILTER_REQUIRE_ARRAY]);
        $labels = filter_input(INPUT_POST, 'follow_label', FILTER_SANITIZE_STRING, ['flags' => FILTER_REQUIRE_ARRAY]);
        $targets = filter_input(INPUT_POST, 'follow_target', FILTER_SANITIZE_STRING, ['flags' => FILTER_REQUIRE_ARRAY]);

        foreach (['facebook', 'twitter', 'draugiem'] as $id) {
            if (array_key_exists($id, $links)) {
                update_option('wb_socialfollow_link_'.$id, $links[$id]);
            }
            if (array_key_exists($id, $labels)) {
                update_option('wb_socialfollow_label_'.$id, $labels[$id]);
            }
            if (array_key_exists($id, $targets)) {
                update_option('wb_socialfollow_target_'.$id, $targets[$id]);
            }
        }

        wp_redirect('options-general.php?page=socialsharing');
        exit;
    }
}