<?php

class Posts_Balancer_Local_Storage
{

    public $nonce = 'storage-nonce';
    public $action = 'storage-ajax-action';
    public $url;

    public function __construct()
    {
        $this->url = admin_url('admin-ajax.php');

        add_action('wp_enqueue_scripts', [$this, 'storage_ajax_script']); 

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'storage_ajax_response']);
        add_action('wp_ajax_' . $this->action, [$this, 'storage_ajax_response']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'storage_get_data']);
        add_action('wp_ajax_' . $this->action, [$this, 'storage_get_data']);

        // add_action('wp_ajax_nopriv_' . $this->action, [$this, 'storage_get_front']);
        // add_action('wp_ajax_' . $this->action, [$this, 'storage_get_front']);

    }


    public function storage_ajax_script($extra = '')
    {
        if (is_single()) {
            wp_enqueue_script('storage_ajax_script', plugin_dir_url(__FILE__) . 'js/balancer-storage.js', array('jquery'), '1.0', true);
            $this->storage_vars();
            $this->storage_get_vars();
            $this->storage_get_front_vars();

            if (has_filter('storage_ajax_ext')) {
                apply_filters('storage_ajax_ext', $extra);
            }
        }
    }

    public function storage_localize_script($var_data, $data)
    {
        $fields = [
            'url'    => $this->url,
            '_ajax_nonce'  => wp_create_nonce($this->nonce),
            'action' => $this->action
        ];

        $fields = array_merge($fields, $data);

        wp_localize_script('storage_ajax_script', $var_data, $fields);
    }

   


    public function storage_get_post_data($post_id)
    {
         $post = $post_id; //post id

        if (get_post_type($post) === get_option('balancer_editorial_post_type')) {

            $anonInfo = [];
            /**
             * Authors
             */
            if (get_option('balancer_editorial_autor') !== null || get_option('balancer_editorial_autor') !== '') {
                $terms_id = get_the_terms($post, get_option('balancer_editorial_autor'));
                $anonInfo['authors'] = [];

                if(!empty($terms_id)) {
                    foreach ($terms_id as $t) {
                        $anonInfo['authors'][] = strval($t->{'term_id'});
                    }
                } else {
                    $anonInfo['authors'][] = '';
                }
            } else {
                $anonInfo['authors'] = get_post_field('post_author', $post);
            }
            /**
             * Categories
             */
            if (get_option('balancer_editorial_taxonomy') !== null || get_option('balancer_editorial_taxonomy') !== '') {
                $terms_id = get_the_terms($post, get_option('balancer_editorial_taxonomy'));
                $anonInfo['cats'] = [];

                if(!empty($terms_id)) {
                    foreach ($terms_id as $t) {
                        $anonInfo['cats'][] = strval($t->{'term_id'});
                    }
                } else {
                    $anonInfo['cats'][] = null;
                }
               
            }
            /**
             * tags
             */
            if (get_option('balancer_editorial_tags') !== null  || get_option('balancer_editorial_tags') !== '') {
                $terms_id = get_the_terms($post, get_option('balancer_editorial_tags'));
                $anonInfo['tags'] = [];

                if(!empty($terms_id)) {
                    foreach ($terms_id as $t) {
                        $anonInfo['tags'][] = strval($t->{'term_id'});
                    }
                } else {
                    $anonInfo['tags'][] = null;
                }
               
            }

            /**
             * Posts
             */
            if (get_option('balancer_editorial_post_type') !== null || get_option('balancer_editorial_post_type') !== '') {
                $anonInfo['posts'] = [$post];
            }
            $info = ['anonInfo' => $anonInfo];
            return $info;
        }
    }

    public function storage_vars()
    {
        $anon = isset($_POST['anon']) ? $_POST['anon'] : '';
        $post_id = get_queried_object_id();
      

        $fields = [
            'anon' => $anon,
            'post_id' => $post_id,
            
        ];

        return $this->storage_localize_script('balancer_anon_ajax', $fields);
    }

    public function storage_ajax_response()
    {
        if (isset($_POST['anon'])) {
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }

            echo json_encode($this->storage_get_post_data($_POST['post_id']));
           
            wp_die();
        }
    }

    public function storage_get_vars()
    {
        $data = isset($_POST['data']) ? $_POST['data'] : '';
        $storage = isset($_POST['storage']) ? $_POST['storage'] : '';
        $post_id = get_queried_object_id();

        $fields = [
            'data' => $data,
            'storage' => $storage,
            'post_id' => $post_id
        ];

        return $this->storage_localize_script('balancer_anon_ajax_get', $fields);
    }

    public function storage_get_data()
    {
        if(isset($_POST['data'])){
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }
            
            if(isset($_POST['storage']) !== null) {
                $data = $_POST['storage'];
                //var_dump($data);
                if($data !== null) {
                    $authors = $data['anonInfo']['authors'] !== null || $data !== '' ? $data['anonInfo']['authors'] : '';
                    $cats = $data['anonInfo']['cats'] !== null || $data !== '' ? $data['anonInfo']['cats'] : '';
                    $tags = isset($data['anonInfo']['tags']) !== null || $data !== '' ? $data['anonInfo']['tags'] : '';
                    $posts = $data['anonInfo']['posts'] !== null || $data !== '' ? $data['anonInfo']['posts'] : '';
                    echo json_encode($this->update_storage($_POST['post_id'],$authors,$cats,$tags,$posts));
                }   
            } 
            wp_die();
        }
    }

    public function update_storage($post_id,$authors,$cats,$tags,$posts)
    {
        $post = $post_id; //post id

        if (get_post_type($post) === get_option('balancer_editorial_post_type')) {

            $anonInfo = [];

            /**
             * Authors
             */
            if (get_option('balancer_editorial_autor') !== null || get_option('balancer_editorial_autor') !== '') {
                $terms_id = get_the_terms($post, get_option('balancer_editorial_autor'));
                

                if($authors[0] == '') {
                    $anonInfo['authors'] = [];
                    foreach ($terms_id as $t) {
                        $anonInfo['authors'][] = strval($t->{'term_id'});
                    }
                } else {
                    $new_authors = [];
                    foreach ($terms_id as $t) {
                        $new_authors[] = strval($t->{'term_id'});
                    }  
                    if($authors != null) {

                        if(array_diff($new_authors,$authors)){
                            $data = array_diff($new_authors,$authors);
                            $anonInfo['authors'] = array_merge($data,$authors);
                        } else {
                            $anonInfo['authors'] = $authors;
                        }

                    }
                   
                } 
            } else {
                $author = strval(get_post_field('post_author', $post));

                if($authors[0] === ''){
                    $anonInfo['authors'] = [$author];
                } else {
                    if(!in_array($author,$authors)){
                        $data = array_push($authors,$author);
                        $anonInfo['authors'] = $data;
                    } else {
                        $anonInfo['authors'] = $authors;
                    }
                }
            }
            // /**
            //  * Categories
            //  */
            if (get_option('balancer_editorial_taxonomy') !== null || get_option('balancer_editorial_taxonomy') !== '') {
                $terms_id = get_the_terms($post, get_option('balancer_editorial_taxonomy'));
                if($cats[0] === '') {
                    $anonInfo['cats'] = [];
                    foreach ($terms_id as $t) {
                        $anonInfo['cats'][] = strval($t->{'term_id'});
                    }
                } else {
                    $new_cats = [];
                    foreach ($terms_id as $t) {
                        $new_cats[] = strval($t->{'term_id'});
                    }
                    if($cats !== null) {
                        if(array_diff($new_cats,$cats) > 0){
                            $data = array_diff($new_cats,$cats);
                            $anonInfo['cats'] = array_merge($data,$cats);
                        } else {
                            $anonInfo['cats'][] = $cats;
                        }
                    }
                 
                }
            }
            // /**
            //  * tags
            //  */
            if (get_option('balancer_editorial_tags') !== null  || get_option('balancer_editorial_tags') !== '') {
                $terms_id = get_the_terms($post, get_option('balancer_editorial_tags'));
                if($tags !== null && $tags) {
                    if($tags[0] === ''){
                        $anonInfo['tags'] = [];
                        foreach ($terms_id as $t) {
                            $anonInfo['tags'][] = strval($t->{'term_id'});
                        }
                    } else {
                        $new_tags = [];
                        foreach ($terms_id as $t){
                            $new_tags[] = strval($t->{'term_id'});
                        }
                        
                            if(array_diff($new_tags,$tags) > 0){
                                $data = array_diff($new_tags,$tags);
                                $anonInfo['tags'] = array_merge($data,$tags);
                            } else {
                                $anonInfo['tags'] = $tags;
                            }
                        }
                }
               
            }
            // /**
            //  * Posts
            //  */
            if (get_option('balancer_editorial_post_type') !== null || get_option('balancer_editorial_post_type') !== '') {
                if($posts[0] === ''){
                    $anonInfo['posts'] = [$post];
                } else {
                    $new_post = strval($post);
                
                    if(!in_array($new_post,$posts)){
                        $post = [$new_post];    
                        $anonInfo['posts'] = array_merge($post,$posts);
                    } else {
                        $anonInfo['posts'] = $posts;
                    }
                }
                
            }

            $info = ['anonInfo' => $anonInfo];
            return $info;
        }
    }


    public function storage_get_front_vars()
    {
        $balancer_get = isset($_POST['balancer_get']) ? $_POST['balancer_get'] : '';
        $balancer_data = isset($_POST['balancer_data']) ? $_POST['balancer_data'] : '';

        $fields = [
            'balancer_get' => $balancer_get,
            'balancer_data' => $balancer_data
        ];

        return $this->storage_localize_script('balancer_front_data_ajax', $fields);
    }

    public function storage_get_front()
    {
        if(isset($_POST['balancer_get'])){
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                echo wp_send_json_error(__('The nonce is broken or this is illegal.', 'posts-balancer'));
                wp_die();
            }

            if(isset($_POST['balancer_data'])){       
                
              $cats = is_user_logged_in() && get_user_meta( wp_get_current_user()->ID, '_personalizer_categories',true ) !== '' ? get_user_meta( wp_get_current_user()->ID, '_personalizer_categories',true ) : array_values($_POST['balancer_data']['anonInfo']['cats']);
            
              $tags = is_user_logged_in() && get_user_meta( wp_get_current_user()->ID, '_personalizer_posts',true ) !== '' ? get_user_meta( wp_get_current_user()->ID, '_personalizer_posts',true ) : array_values($_POST['balancer_data']['anonInfo']['tags']);
            

                
                $args = [
                    'post_type' => get_option('balancer_editorial_post_type'),
                    'numberposts' => 4,
                    'post_status' => 'publish',
                    'exclude' => array_values($_POST['balancer_data']['anonInfo']['posts']),
                    'date_query' => [
                        [
                            'column' => 'post_date_gmt',
                            'after'  => get_option('balancer_editorial_days') . ' days ago',
                        ]
                    ]
                ];

                $args['tax_query'] = ['relation' => 'OR'];

                $args['tax_query'][] =  [
                    'taxonomy' => get_option('balancer_editorial_taxonomy'),
                    'field' => 'term_id',
                    'terms' => $cats
                ];

                if(key_exists('authors',$_POST['balancer_data']['anonInfo'])){
                    $args['tax_query'][] =  [
                        'taxonomy' => get_option('balancer_editorial_autor'),
                        'field' => 'term_id',
                        'terms' => array_values($_POST['balancer_data']['anonInfo']['authors'])
    
                    ];
                }

                $args['tax_query'][] =  [
                    'taxonomy' => get_option('balancer_editorial_tags'),
                    'field' => 'term_id',
                    'terms' => $tags
                ];

                if(is_user_logged_in() && get_option('balancer_editorial_place') !== null){
                    $place = is_user_logged_in() && get_user_meta( wp_get_current_user()->ID, '_personalizer_posts',true ) !== '' ? get_user_meta( wp_get_current_user()->ID, '_personalizer_location',true ) : false;
                    if($place && get_user_meta( wp_get_current_user()->ID, '_personalizer_location',true )) {
                        $args['tax_query'][] =  [
                            'taxonomy' => get_option('balancer_editorial_place'),
                            'field' => 'name',
                            'terms' => get_user_meta( wp_get_current_user()->ID, '_personalizer_location',true )
                        ];
                    }
                   
                }
                
                $query = get_posts($args);
              
                if(function_exists('show_interest_front')){
                    return show_interest_front($query);
                } else {
                    return $query;
                }
                wp_die();

            } else {
                return wp_send_json_error();
                wp_die();
            }
            wp_die();
        }
    }


}

function balancer_local_storage()
{
    return new Posts_Balancer_Local_Storage();
}

balancer_local_storage();
