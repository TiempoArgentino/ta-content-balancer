<?php

class Post_Balancer_Cookie
{
    public function __construct()
    {
        add_action('wp', [$this, 'init_cookie']);
        add_action('template_redirect',[$this,'balancer_cookie'],999);
        add_action('template_redirect', [$this,'dump'],9999);
    }

    public function init_cookie()
    {
        if (!isset($_COOKIE['balancer'])) {
            setcookie('balancer', uniqid() . date('YmdHmi'), time() + 86400, '/');           
        }        
    }

    public function populate_cookie()
    {
        if (isset($_COOKIE['balancer'])) {
            $result = posts_balancer_db()->get_data_row($_COOKIE['balancer'],'id_session','balancer_session');
            if($result == null) {
                 $data = [
                    'id_session' => $_COOKIE['balancer'],
                    'content' => ''
                ];

                $insert = posts_balancer_db()->insert_data('balancer_session',$data,['%s','%s']);
                return $insert;
            }           
        }
    }

    public function balancer_cookie()
    {
       
        if (is_single()) {

            if(!$this->populate_cookie()){
                $this->populate_cookie();
            }

            if(is_user_logged_in()) {
                posts_balancer_db()->update_data('balancer_session',['user_id' => get_current_user_id()],['id_session'=>$_COOKIE['balancer']],['%d'],['%d']);
            }

            $post_id = get_queried_object_id();
            $categories = get_the_terms($post_id, get_option('balancer_editorial_taxonomy'));
            $authors = get_the_terms($post_id, get_option('balancer_editorial_autor'));
            $cat = [];
            if ($categories != null) {
                foreach ($categories as $c) {
                    $cat[] = $c->term_id;;
                }
            } else {
                $cat[] = '';
            }
            $author = [];
            if ($authors != null) {

                foreach ($authors as $a) {
                    $author[] = $a->term_id;
                }
            } else {
                $author[] = '';
            }

            $array = [];

            $array['info']['posts'] = [$post_id]; 
            $array['info']['cats'] = $cat;
            $array['info']['authors'] = $author;

            //**add info */

            $data = posts_balancer_db()->get_data_row($_COOKIE['balancer'],'id_session','balancer_session');
            $user_data = maybe_unserialize($data->{'content'});

            if($data->{'content'} === '') {
                posts_balancer_db()->update_data('balancer_session',['content' => maybe_serialize($array)],['id_session'=>$_COOKIE['balancer']],['%s'],['%s']);
            } else {
                $data = [];
                $data['info']['posts'] = [$post_id];
                $data['info']['cats'] = $cat;
                $data['info']['authors'] = $author;

                if(array_diff($user_data['info']['posts'],$data['info']['posts']) > 0){ //ad new post id
                    $new_id = array_diff($user_data['info']['posts'],$data['info']['posts']);
                    $data['info']['posts'] = array_merge($new_id,$data['info']['posts']);    
                }

                if(array_diff($user_data['info']['cats'], $data['info']['cats']) > 0) {
                    $new_cat = array_diff($user_data['info']['cats'], $data['info']['cats']);
                    $data['info']['cats'] = array_merge($new_cat,$data['info']['cats']);
                }

                if(array_diff($array['info']['authors'], $data['info']['authors']) > 0) {
                    $new_author = array_diff($array['info']['authors'], $data['info']['authors']);
                    $data['info']['authors'] = array_merge($new_author,$data['info']['authors']);
                }

                posts_balancer_db()->update_data('balancer_session',['content' => maybe_serialize($data)],['id_session'=>$_COOKIE['balancer']],['%s'],['%s']);
            }
        }
    }

    public function dump()
    {

        $data = posts_balancer_db()->get_data_row($_COOKIE['balancer'],'id_session','balancer_session');

        $user_data = maybe_unserialize($data->{'content'});
        echo '<pre>';
        var_dump($user_data);
        echo '</pre>';
       
    }
}

function balancer_cookie()
{
    return new Post_Balancer_Cookie();
}

balancer_cookie();
