<?php

class Posts_Balancer_Personalize
{
    public $nonce = 'personalize-nonce';
    public $action = 'personalize-ajax-action';
    public $url;
    public function __construct()
    {
        $this->url = admin_url('admin-ajax.php');

        add_action('wp_enqueue_scripts', [$this, 'personalizer_ajax_script']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'personalizer_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'personalizer_ajax']);

        add_action('personalize_before',[$this,'is_log']);

        //add_action('template_redirect',[$this,'get_tags']);


    }


    public function is_log()
    {
        if(!is_user_logged_in()) {
            if(get_option('user_login_page')) {
                wp_redirect(get_permalink( get_option('user_login_page')).'?login=unauthorized');
                exit();
            } else {
                wp_redirect( home_url());
                exit();
            }
        }
    }

    public function personalizer_ajax_script($extra = '')
    {
        wp_enqueue_script('personalizer_ajax_script', plugin_dir_url(__FILE__) . 'js/personalizer-balancer-ajax.js', array('jquery'), '1.0', true);
        $this->personalizer_vars();


        if (has_filter('personalizer_ajax_ext')) {
            apply_filters('personalizer_ajax_ext', $extra);
        }
    }

    public function personalizer_localize_script($var_data, $data)
    {
        $fields = [
            'url'    => $this->url,
            '_ajax_nonce'  => wp_create_nonce($this->nonce),
            'action' => $this->action
        ];

        $fields = array_merge($fields, $data);

        wp_localize_script('balancer_ajax_script', $var_data, $fields);
    }

    public function personalizer_vars()
    {
        $personalizer = isset($_POST['personalizer']) ? $_POST['personalizer'] : '';
        $location = isset($_POST['location']) ? $_POST['location'] : '';
        $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
        $tax = isset($_POST['tax']) ? $_POST['tax'] : '';
        $authors = isset($_POST['authors']) ? $_POST['authors'] : '';
        $user = isset($_POST['user']) ? $_POST['user'] : '';

        $fields = [
            'personalizer' => $personalizer,
            'location' => $location,
            'tags' => $tags,
            'tax' => $tax,
            'authors' => $authors
        ];

        return $this->personalizer_localize_script('ajax_personalizer', $fields);
    }

    public function personalizer_ajax()
    {
        if (isset($_POST['personalizer'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }

            if(isset($_POST['location'])) {
                update_user_meta($_POST['user'], '_personalizer_location', $_POST['location']);
            }
            /**
             * Tags ID
             */
            if(isset($_POST['tags'])) {
                update_user_meta($_POST['user'], '_personalizer_topics', $_POST['tags']);
            }
            /**
             * taxonomy ID
             */
            if(isset($_POST['tax'])) {
                update_user_meta($_POST['user'], '_personalizer_taxonomy', $_POST['tax']);
            }
            /**
             * posts ID
             */
            if(isset($_POST['authors'])) {
                update_user_meta($_POST['user'], '_personalizer_authors', $_POST['authors']);
            }

            if(!isset($_POST['location']) && !isset($_POST['tags']) && !isset($_POST['tax']) && !isset($_POST['authors'])){
                echo wp_send_json_error(__('At least one of the data must be entered.','posts-balancer'));
                wp_die();
            }

            echo wp_send_json_success();
            wp_die();
        }
    }

    public function get_tags($number = 24)
    {

        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_tags'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms; //return array (key (term_id) => val (term name))
    }

    public function get_topics($number = 24)
    {

        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_topics'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms; //return array (key (term_id) => val (term name))
    }

    public function get_taxonomies($number = 12)
    {
        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_taxonomy'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms;
    }

    public function get_authors($number = 12)
    {
        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_autor'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms;
    }

    public function get_articles($number = 16)
    {
        $args = [
            'post_type' => get_option('balancer_editorial_post_type'),
            'numberposts' => $number,
            'orderby'   => 'rand',
            'post_status' => 'publish',
            'date_query' => [
                [
                    'column' => 'post_date_gmt',
                    'after'  => get_option('balancer_editorial_days').' days ago',
                ]
            ],
        ];

        return get_posts($args);
    }

}

function balancer_personalize()
{
    return new Posts_Balancer_Personalize();
}

balancer_personalize();