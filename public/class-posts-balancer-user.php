<?php

class Posts_Balancer_User
{
    public $nonce = 'balancer-nonce';
    public $action = 'balancer-ajax-action';
    public $url;

    public $favorite_table = 'favorites';

    public function __construct()
    {
        $this->url = admin_url('admin-ajax.php');

        add_action('favorite_button_action', [$this, 'favorite_button']);

        add_action('wp_enqueue_scripts', [$this, 'balancer_ajax_script']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'favorite_add_post']);
        add_action('wp_ajax_' . $this->action, [$this, 'favorite_add_post']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'favorite_delete_action']);
        add_action('wp_ajax_' . $this->action, [$this, 'favorite_delete_action']);
    }

    public function balancer_ajax_script($extra = '')
    {
        wp_enqueue_script('balancer_ajax_script', plugin_dir_url(__FILE__) . 'js/posts-balancer-ajax.js', array('jquery'), '1.0', true);
        $this->favorite_vars();
        $this->favorite_delete_vars();

        if (has_filter('balancer_ajax_ext')) {
            apply_filters('balancer_ajax_ext', $extra);
        }
    }

    public function balancer_localize_script($var_data, $data)
    {
        $fields = [
            'url'    => $this->url,
            '_ajax_nonce'  => wp_create_nonce($this->nonce),
            'action' => $this->action
        ];

        $fields = array_merge($fields, $data);

        wp_localize_script('balancer_ajax_script', $var_data, $fields);
    }

    public function favorite_button()
    {
        global $post;
        echo '<a href="#" class="save_favorite" data-post="' . $post->ID . '" data-user="' . wp_get_current_user()->ID . '">
                <img src="' . plugin_dir_url(__FILE__) . 'img/guardar.svg" />
            </a>';
    }

    public function favorite_vars()
    {
        $add_favorite = isset($_POST['add_favorite']) ? $_POST['add_favorite'] : '';
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';

        $fields = [
            'add_favorite' => $add_favorite,
            'user_id' => $user_id,
            'post_id' => $post_id
        ];

        return $this->balancer_localize_script('ajax_add_favorite', $fields);
    }

    public function favorite_add_post()
    {
        if (isset($_POST['add_favorite'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }

            if (!isset($_POST['user_id']) || $_POST['user_id'] === '0') {
                echo wp_send_json_error(__('You must be logged for use this function.', 'posts-balancer'));
                wp_die();
            }

            if (!isset($_POST['post_id'])) {
                echo wp_send_json_error(__('This is not a Article (?).', 'posts-balancer'));
                wp_die();
            }
            /** check */
            $get_favorite = posts_balancer_db()->get_data($_POST['user_id'], 'user_id', $this->favorite_table);
            /** delete */
            if (count($get_favorite) !== 0) {
                if ($get_favorite[0]->id_post === $_POST['post_id']) {
                    $delete = posts_balancer_db()->delete_data($this->favorite_table, ['ID' => $get_favorite[0]->ID], null);
                    echo wp_send_json_success(__('The favorite is delete.', 'posts-balancer'));
                    wp_die();
                }
            }
            /** add */
            $data = [
                'user_id' => $_POST['user_id'],
                'id_post' => $_POST['post_id']
            ];

            $favorite = posts_balancer_db()->insert_data($this->favorite_table, $data, ['%d', '%d']);

            if (!$favorite) {
                echo wp_send_json_error(__('A error ocurred in insert the data.', 'posts-balancer'));
                wp_die();
            } else {
                echo wp_send_json_success(__('The favorite was save.', 'posts-balancer'));
                wp_die();
            }
        }
    }
    /**
     * favorites count
     */
    public function favorites_count()
    {
        $id = wp_get_current_user()->ID;
        if ($id) {
            $get_favorite = posts_balancer_db()->get_data($id, 'user_id', $this->favorite_table);
            return count($get_favorite);
        }
        return '0';
    }
    /**
     * favorites show profile
     */
    public function favorites_show_profile()
    {
        $id = wp_get_current_user()->ID;
        $posts = array();
        if ($id) {
            $show_favorites = posts_balancer_db()->get_data($id, 'user_id', $this->favorite_table);
            foreach ($show_favorites as $key => $val) {
                $posts[] = $val->{'id_post'};
            }
            if (count($posts) > 0) {
                $args = [
                    'include' => $posts,
                    'post_status' => 'publish',
                    'post_type' => 'ta_article'
                ];
                $posts_data = get_posts($args);
                return $posts_data;
            }
            return false;
        }
        return false;
    }
    /**
     * favorites delete
     */
    public function favorite_delete_vars()
    {
        $fav_delete = isset($_POST['fav_delete']) ? $_POST['fav_delete'] : '';
        $id_post = isset($_POST['id_post']) ? $_POST['id_post'] : '';
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

        $fields = [
            'fav_delete' => $fav_delete,
            'id_post' => $id_post,
            'user_id' => $user_id
        ];

        return $this->balancer_localize_script('ajax_delete_favorite', $fields);
    }

    public function favorite_delete_action()
    {
        if(isset($_POST['fav_delete'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }

            if (!isset($_POST['user_id']) || $_POST['user_id'] === '0') {
                echo wp_send_json_error(__('You must be logged for use this function.', 'posts-balancer'));
                wp_die();
            }

            if (!isset($_POST['id_post'])) {
                echo wp_send_json_error(__('This is not a Article (?).', 'posts-balancer'));
                wp_die();
            }
            /** check */
            $get_favorite = posts_balancer_db()->get_data($_POST['user_id'], 'user_id', $this->favorite_table);

            /** delete */
            if (count($get_favorite) !== 0) {
                if ($get_favorite[0]->id_post == $_POST['id_post']) {
                    $delete = posts_balancer_db()->delete_data($this->favorite_table, ['ID' => $get_favorite[0]->ID], null);
                    echo wp_send_json_success(__('The favorite is delete.', 'posts-balancer'));
                    wp_die();
                }
            }
        }
    }
}

function balancer_user()
{
    return new Posts_Balancer_User();
}

balancer_user();
