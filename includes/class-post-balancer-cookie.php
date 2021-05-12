<?php

class Post_Balancer_Cookie
{
    public function __construct()
    {
        add_action('wp', [$this, 'init_cookie']);
        add_action('template_redirect',[$this,'balancer_cookie'],9999);
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

            posts_balancer_db()->update_data('balancer_session',['content' => json_encode($array)],['id_session'=>$_COOKIE['balancer']],['%s'],['%s']);
        }
    }
}

function balancer_cookie()
{
    return new Post_Balancer_Cookie();
}

balancer_cookie();
