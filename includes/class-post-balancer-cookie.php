<?php

class Post_Balancer_Cookie
{
    public function __construct()
    {
        add_action('wp',[$this,'init_cookie']);
    }

    public function init_cookie()
    {              
            $post_id = get_queried_object_id();
            $categories = get_the_terms($post_id,get_option('balancer_editorial_taxonomy'));
            $authors = get_the_terms($post_id,get_option('balancer_editorial_autor'));
            $cat = [];

 

            if($categories != null) {
                foreach($categories as $c) {
                    $cat[] = $c->term_id;;
                }
            } else {
                $cat[] = '';
            }
            $author = [];
           if($authors != null) {
                
                foreach($authors as $a) {
                    $author[] = $a->term_id;
                }
            } else {
                $author[] = '';
            }

            $terms_string = join(', ', wp_list_pluck($categories, 'name'));
           

            $array = [];

            $array['posts'] = [$post_id]; 
            $array['cats'] = $cat;
            $array['authors'] = $author;
            setcookie('ta_cookie', $terms_string, time()+86400,'/');
            // setcookie('user[posts]', json_encode($array['posts']), time()+86400,'/');
            // setcookie('user[authors]', json_encode($array['authors']), time()+86400,'/');
            
           if(isset($_COOKIE['user'])) {
            echo $_COOKIE['user']['cats'];
           }
           
    }
}

function balancer_cookie()
{
    return new Post_Balancer_Cookie();
}

balancer_cookie();