<?php

class Posts_Balancer_Posts
{
    public function __construct()
    {
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

        add_action('template_redirect', [$this, 'set_view']);

        add_action('rest_api_init', [$this, 'most_viewed_endpoint']); //wp-json/post-balancer/v1/most-view/ return a array, ex: [523,520,519,537]
    }

    public function get_user_ip()
    {
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }

    public function set_view()
    {
        global $post;

        if (is_single() && $post->post_type === 'ta_article') {

            $count = get_post_meta($post->ID, 'ta_article_count', true);
            $ip = get_post_meta($post->ID, 'ta_article_visit_ip', true);

            if ($this->get_user_ip() !== $ip || $ip == '' || !$ip) {
                if ($count == '') {
                    $count = 0;
                    delete_post_meta($post->ID, 'ta_article_count');
                    add_post_meta($post->ID, 'ta_article_count', '1');
                    add_post_meta($post->ID, 'ta_article_visit_ip', $this->get_user_ip());
                } else {
                    $count++;
                    update_post_meta($post->ID, 'ta_article_count', $count);
                }
            }
        }
    }

    public function get_most_view()
    {
        $args = [
            'post_type' => 'ta_article',
            'posts_per_page' => 10,
            'orderby' => ['ta_article_count' => 'DESC'],
            'meta_query' => [
                [
                    'key' => 'ta_article_count',
                    'compare' => 'LIKE',
                    'type'      => 'NUMERIC',
                    'compare'   => 'EXISTS'
                ]
            ],
            'date_query' => [
                [
                    'column' => 'post_date_gmt',
                    'after'  => get_option('balancer_editorial_days') . ' days ago',
                ]
            ],
            'fields' => 'ids'
        ];

        $posts = get_posts($args);


        return $posts;
    }

    public function most_viewed_endpoint() //wp-json/post-balancer/v1/most-view/ return a array, ex: [523,520,519,537]
    {
        register_rest_route('post-balancer/v1', '/most-view/', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_most_view'],
            'permission_callback' => ''
        ));
    }
    /**
     * Balancer
     */

    
}

function balancer_posts()
{
    return new Posts_Balancer_Posts();
}

balancer_posts();
