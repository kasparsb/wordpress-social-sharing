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

        // Send link to email
        add_action('wp_ajax_socialsharing_sendtoemail', [$this, 'do_send_to_email']);
        add_action('wp_ajax_nopriv_socialsharing_sendtoemail', [$this, 'do_send_to_email']);

        // Track button click
        add_action('wp_ajax_socialsharing_hit', [$this, 'do_hit']);
        add_action('wp_ajax_nopriv_socialsharing_hit', [$this, 'do_hit']);

        // Count hits
        add_action('wp_ajax_socialsharing_count_hits', [$this, 'do_count_hits']);
        add_action('wp_ajax_nopriv_socialsharing_count_hits', [$this, 'do_count_hits']);        
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

        global $post;
        wp_localize_script('socialsharing', 'socialsharing', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'postId' => $post ? $post->ID : ''
        ]);
    }

    /**
     * Link sharing
     */
    public function shortcode_sharing($atts) {
        $atts = shortcode_atts([
            'sharing_count' => null,

            'title' => 'Iesaki šo rakstu citiem',
            'width' => 'auto', // 100%
            
            'show_sharing_count' => true,
            'show_labels' => true,
            'show_icons' => true,

            'label_position' => 'inline', // bottom
            'icon_size' => 'normal'
            
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
                // Ja ir custom shares count, tad ņemam to
                if ($custom_sharing_count = $this->get_sharing_count($post->ID)) {
                    $atts['sharing_count'] = $this->get_sharing_count($post->ID);
                }
                else {
                    $atts['sharing_count'] = $this->get_organic_count($post->ID);
                }
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
            'width' => 'auto', // 100%
            
            'show_labels' => true,
            'show_icons' => true,
            
            'label_position' => 'inline', // bottom
            'icon_size' => 'normal'

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
            Organic: <?php echo $this->get_organic_count($post->ID) ?>
        </div>
        <div>
            <input type="text" value="<?php echo $this->get_sharing_count($post->ID) ?>" name="socialsharing_count" />
        </div>
        <?php
    }

    public function get_sharing_count($post_id) {
        $c = intval(get_post_meta($post_id, '_socialsharing_count', true));
        return $c === 0 ? '' : $c;
    }

    public function get_organic_count($post_id) {
        $c = intval(get_post_meta($post_id, '_socialsharing_hits', true));
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

    public function do_send_to_email() {
        $reciever_email = trim(filter_input(INPUT_POST, 'recieveremail', FILTER_SANITIZE_EMAIL));
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

        $errors = [];

        // Validējam
        if (!filter_var($reciever_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Norādiet saņēmēja e-pastu!';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Norādiet savu e-pastu!';
        }

        if (count($errors) > 0) {
            echo json_encode([
                'success' => false,
                'message' => implode('<br />', $errors)
            ]);
            exit;
        }


        global $post;

        $post = get_post($post_id);
        
        setup_postdata($post);
        $title = get_the_title();
        $link = get_permalink($post_id);
        $excerpt = get_the_excerpt();

        $message = $this->get_link_share_email_template(compact(
            'reciever_email',
            'comment',
            'email',
            'name',
            'title',
            'excerpt',
            'link'
        ));

        $this->send_mail($reciever_email, 'Draugs sūta interesantu rakstu', $message);

        echo json_encode([
            'success' => true
        ]);
        exit;
    }

    public function do_hit() {
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
        $share = filter_input(INPUT_POST, 'share', FILTER_SANITIZE_STRING);

        $this->record_hit($post_id, $share, $this->get_ip(), $this->get_user_agent());

        exit;
    }

    public function do_count_hits() {
        $this->count_hits();
        exit;
    }

    public function get_ip() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }

    public function get_user_agent() {
        return empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
    }

    public function get_link_share_email_template($data) {
        $f = dirname(__FILE__).'/template/link-share-email.php';

        ob_start();

        extract($data);
        
        include($f);

        $h = ob_get_contents();
        ob_end_clean();
        return $h;
    }

    public function send_mail($mail, $title, $message) {
        \LA_Auth::send_email([
            'email' => $mail,
            'subject' => $title,
            'body' => $message,
        ]);
    }

    public function record_hit($post_id, $share, $ip, $user_agent) {
        $time = date('Y-m-d H:i:s');
        /**
         * Individual hits data
         * data, ip, user_agent separated by |
         */
        add_post_meta($post_id, '_socialsharing_hit_'.$share, implode('|', [
            $time,
            $ip,
            $user_agent
        ]));

        /**
         * Piefiksējam, ka ir bijis hit, bet to vēl neatrādam pie posta
         * metode count_hits skatīsies šo ierakstus un tie, kas ir 
         * vecāki par 2 min tiks pieskaitīti pie kopējā shares count
         */
        add_post_meta($post_id, '_socialsharing_tracked_hit', $time);
    }

    private function add_post_share_hit($post_id, $hits=1) {
        // Total hits
        $total_hits = intval(get_post_meta($post_id, '_socialsharing_hits', true));
        update_post_meta($post_id, '_socialsharing_hits', $total_hits + $hits);
    }

    private function count_hits() {
        global $wpdb;

        $q = "select * from $wpdb->postmeta where meta_key='_socialsharing_tracked_hit'";
        $rows = $wpdb->get_results($q);

        $current_time = time();
        echo "start count hits ".date('Y-m-d H:i:s', $current_time)."\n";
        foreach ($rows as $row) {
            $time = strtotime($row->meta_value);
            echo $row->post_id.' - '.date('Y-m-d H:i:s', $time)."\n";
            if ($current_time - $time > 120) { // 2 min
                $this->add_post_share_hit($row->post_id);
                $wpdb->delete($wpdb->postmeta, ['meta_id' => $row->meta_id]);
            }
        }
    }
}