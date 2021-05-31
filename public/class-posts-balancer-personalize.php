<?php

class Posts_Balancer_Personalize{
    static private $initialized = false;
    static public $nonce = 'personalize-nonce';
    static public $action = 'personalize-ajax-action';
    static public $url;

    // TODO: Esto hay que pasarlo a un modelo singleton, no se puede estar corriendo
    // el construct cada vez que se quiere usar un method the esta clase
    static public function initialize(){
        if(self::$initialized)
            return false;
        self::$initialized = true;

        self::$url = admin_url('admin-ajax.php');
        add_action('wp_enqueue_scripts', [self::class, 'personalizer_ajax_script']);
        add_action('wp_ajax_nopriv_' . self::$action, [self::class, 'personalizer_ajax']);
        add_action('wp_ajax_' . self::$action, [self::class, 'personalizer_ajax']);
        add_action('personalize_before',[self::class,'is_log']);
        //add_action('template_redirect',[self::class,'get_tags']);
    }


    static public function is_log()
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

    static public function personalizer_ajax_script($extra = '')
    {
        wp_enqueue_script('personalizer_ajax_script', plugin_dir_url(__FILE__) . 'js/personalizer-balancer-ajax.js', array('jquery'), '1.0', true);
        self::personalizer_vars();

        if (has_filter('personalizer_ajax_ext')) {
            apply_filters('personalizer_ajax_ext', $extra);
        }
    }

    static public function personalizer_localize_script($var_data, $data)
    {
        $fields = [
            'url'    => self::$url,
            '_ajax_nonce'  => wp_create_nonce(self::$nonce),
            'action' => self::$action
        ];

        $fields = array_merge($fields, $data);

        wp_localize_script('balancer_ajax_script', $var_data, $fields);
    }

    static public function personalizer_vars()
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

        return self::personalizer_localize_script('ajax_personalizer', $fields);
    }

    static public function personalizer_ajax()
    {
        if (isset($_POST['personalizer'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, self::$nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }

            if(!isset($_POST['location']) && !isset($_POST['tags']) && !isset($_POST['tax']) && !isset($_POST['authors'])){
                echo wp_send_json_error(__('At least one of the data must be entered.','posts-balancer'));
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
             // TODO: Cambiar tax por cats en todos los lugares donde se use para referirse a la taxonomia del option balancer_editorial_taxonomy
            if(isset($_POST['tax'])) {
                update_user_meta($_POST['user'], '_personalizer_taxonomy', $_POST['tax']);
            }
            /**
             * posts ID
             */
            if(isset($_POST['authors'])) {
                update_user_meta($_POST['user'], '_personalizer_authors', $_POST['authors']);
            }

            update_user_meta($_POST['user'], '_personalizer_active', true);

            echo wp_send_json_success();
            wp_die();
        }
    }

    /**
    *   Returns the stored personlizer data for an user
    *   @return mixed[]|null {
    *       @property int[] authors
    *       @property int[] location
    *       @property int[] cats
    *       @property int[] topics
    *   }
    */
    static public function get_user_personalizer_data($user_id){
        if(!self::user_is_personalized($user_id))
            return null;

        return array(
            'authors'           => get_user_meta($user_id, '_personalizer_authors', true),
            'location'          => get_user_meta($user_id, '_personalizer_location', true),
            'cats'              => get_user_meta($user_id, '_personalizer_taxonomy', true),
            'topics'            => get_user_meta($user_id, '_personalizer_topics', true),
        );
    }

    /**
    *   Indicates if an user is has personalized his expirience
    *   @return bool
    */
    static public function user_is_personalized($user_id){
        return get_user_meta($user_id, '_personalizer_active', true);
    }

    static public function get_tags($number = 24)
    {
        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_tags'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms; //return array (key (term_id) => val (term name))
    }

    static public function get_topics($number = 24)
    {

        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_topics'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms; //return array (key (term_id) => val (term name))
    }

    static public function get_taxonomies($number = 12)
    {
        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_taxonomy'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms;
    }

    static public function get_authors($number = 12)
    {
        $terms = get_terms([
            'taxonomy' => get_option('balancer_editorial_autor'),
            'hide_empty' => true,
            'fields' => 'id=>name',
            'number' => $number
        ]);
        return $terms;
    }

    static public function get_articles($number = 16)
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

Posts_Balancer_Personalize::initialize();
