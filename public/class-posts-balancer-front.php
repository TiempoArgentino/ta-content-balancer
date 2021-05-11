<?php
class Posts_Balancer_Front
{
    static public $offset = 0;
    public $session_posts = 'balancer_storage_posts';
    public $session_author = 'balancer_storage_author';
    public $session_tax = 'balancer_storage_tax';
    public $session_tag = 'balancer_storage_tag';

    public function __construct()
    {
        add_action('template_redirect', [$this, 'show_interest']);
        add_action('rest_api_init', [$this, 'show_tags']);
    }

    /**
     * balancer
     *  @param int $number                                                      The amount of posts to fetch from the balancer
     *  @param bool $use_offset                                                 Indicates if the global offset should be used to avoid fetching
     *                                                                          posts that had already been used.
     */
    public function balancer($number = 6, $use_offset = true)
    {
        $num = intval($number);

        $view = round(($num * intval(get_option('_balancer_percent_views'))) / 100);
        $user = round(($num * intval(get_option('_balancer_percent_user'))) / 100);
        $editorial = round(($num * intval(get_option('_balancer_percent_editorial'))) / 100);

       /**
        * The sum of the 3 has to be 100, validation in settings,
        * if the user is not logged in add what is missing for the other two
        */

        $user_posts = $this->post_user_count($user) !== null ? $this->post_user_count($user) : []; //return a empty array if $user is empty or in zero

        if($user_posts === null || sizeof($user_posts) == 0) { //missing for other two
            $view = round($view + ($user / 2));
            $editorial = round($editorial + ($user /2));

        }

        $editorial_posts = $this->post_count($editorial) !== null ? $this->post_count($editorial) : [];
        $view_posts = $this->post_most_view_count($view) !== null ? $this->post_most_view_count($view) : [];

        /**
         * if most view and user preference is not empty
         */
        $posts_id = array_merge($view_posts,$user_posts); //merge ID's
        $query = array_merge($posts_id,$editorial_posts);

        //return $query;

        $args = [
            'post_type' => get_option('balancer_editorial_post_type'),
            'status' => 'publish',
            'include' => $query,
            'numberposts' => $num,
            'fields' => 'ids'
        ];

        if($use_offset){
            if(self::$offset)
                $args['offset'] = self::$offset;

            self::$offset += $num;
        }

        return get_posts( $args ); //return post ID's

    }

    public function post_count($number) //editorial
    {
        if($number > 0) {
            $args = [
                'post_type' => get_option('balancer_editorial_post_type'),
                'status' => 'publish',
                'numberposts' => $number,
                'date_query' => [
                    [
                        'column' => 'post_date_gmt',
                        'after'  =>  get_option('balancer_editorial_days') . ' days ago',
                    ]
                ],
                'fields' => 'ids',
            ];

            $query = get_posts($args);
            return $query;
        }

    }

    public function post_user_count($number) //user preference, this function maybe return 0 post by exclution
    {
        if (is_user_logged_in()) {

            if($number > 0) {
                $user_id = wp_get_current_user()->ID;
                $posts_ids = get_user_meta($user_id, '_personalizer_taxonomy', true);

                $ids = [];

                foreach($posts_ids as $c) {
                    $ids[] = $c;
                }
                $args = [
                    'post_type' => get_option('balancer_editorial_post_type'),
                    'status' => 'publish',
                    'numberposts' => $number,
                    'exclude' => $this->post_count($number),
                    'tax_query' => [
                        [
                            'taxonomy' => get_option('balancer_editorial_taxonomy'),
                            'field' => 'term_id',
                            'terms' => $ids
                        ]
                    ],
                    'date_query' => [
                        [
                            'column' => 'post_date_gmt',
                            'after'  => get_option('balancer_editorial_days') . ' days ago',
                        ]
                    ],
                    'fields' => 'ids',
                ];

                $query = get_posts($args);
                return $query;
            }


        }
    }
    /**
     * most view
     */
    public function post_most_view_count($number) //most view, this function maybe return 0 post by exclution
    {
        if($number > 0) {
            $args = [
                'post_type' => get_option('balancer_editorial_post_type'),
                'orderby' => ['ta_article_count' => 'DESC'],
                'status' => 'publish',
                'numberposts' => $number,
                'exclude' => $this->post_count($number),
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

            $query = get_posts($args);
            return $query;
        }

    }


    /**
     * balancer
     */
    public function show_interest($query)
    {
        if(is_single()) {
            if(function_exists('show_interest_front')){
                return show_interest_front($query);
            } else {
                return $query;
            }
        }

    }

    public function show_front_tags()
    {
        $terms = get_terms( array(
            'taxonomy' => get_option('balancer_editorial_tags'),
            'hide_empty' => true,
        ) );

        return $terms;
    }

    public function show_tags() //wp-json/post-balancer/v1/tag-cloud/
    {
        register_rest_route('post-balancer/v1', '/tag-cloud/', array(
            'methods' => 'GET',
            'callback' => [$this, 'show_front_tags'],
            'permission_callback' => ''
        ));
    }

    public function show_interest_post($user_id,$post_id,$icon1=null,$icon2=null,$icon3=null)
    {

        if(!$user_id || !$post_id){
            return;
        }

        $location = get_user_meta($user_id,'_personalizer_location',true);
        $authors = get_user_meta($user_id,'_personalizer_authors',true);
        $topics = get_user_meta($user_id,'_personalizer_topics',true);

        $post_location = get_the_terms($post_id,'ta_article_place');
        $post_authors = get_the_terms($post_id,'ta_article_author');
        $post_topics = get_the_terms($post_id,'ta_article_tema');

        $pa = [];
        foreach($post_authors as $a){
            $pa[] = $a->term_id;
        }
        $authors_compare = $authors !== '' ? array_intersect($pa,$authors) : '';

        $pt = [];
        foreach($post_topics as $t) {
            $pt[] = $t->term_id;
        }
        $topics_compare = $topics !== '' ? array_intersect($pt,$topics) : '';

        $icon1 = $icon1 !== null ? $icon1 : plugin_dir_url( __FILE__ ).'img/icon-img-1.svg';
        $icon2 = $icon2 !== null ? $icon2 : plugin_dir_url( __FILE__ ).'img/icon-img-2.svg';
        $icon3 = $icon3 !== null ? $icon3 : plugin_dir_url( __FILE__ ).'img/icon-img-3.svg';

        if(is_user_logged_in()):
            $icons = '<div class="icons-container">
                <div class="article-icons d-flex flex-column mb-2">';
             if($post_location[0]->{'name'} === $location):
                  $icons .= '<img src="'.$icon1.'" alt="" />';
             endif;
             if($topics_compare !== '' && sizeof($topics_compare) > 0):
                 $icons .= '<img src="'.$icon2.'" alt="" />';
             endif;
             if($authors_compare !== '' && sizeof($authors_compare) > 0) :
                 $icons .= '<img src="'.$icon3.'" alt="" />';
             endif;
                $icons .= '</div>
            </div>';
            echo $icons;
        endif;

    }

}

function balancer_front()
{
    return new Posts_Balancer_Front();
}
balancer_front();
