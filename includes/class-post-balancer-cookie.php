<?php

class Post_Balancer_Cookie
{
    public function __construct()
    {
        add_action('wp', [$this, 'init_cookie']);
        add_action('template_redirect',[$this,'balancer_cookie'],99);
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
 
            if ($authors) {
                foreach ($authors as $a) {
                    $author[] = $a->term_id;
                }
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
                $new_data = [];
                $new_data['info']['posts'] = [$post_id];
                $new_data['info']['cats'] = $cat;
                $new_data['info']['authors'] = $author;

                if($user_data['info'] != null) {
                    if(array_diff($user_data['info']['posts'],$new_data['info']['posts']) > 0){ //ad new post id
                        $new_id = array_diff($user_data['info']['posts'],$new_data['info']['posts']);
                        $new_data['info']['posts'] = array_merge($new_id,$new_data['info']['posts']);    
                    }
                }
                
                if($user_data['info']['cats'] != null) {
                    if(array_diff($user_data['info']['cats'], $new_data['info']['cats']) > 0) {
                        $new_cat = array_diff($user_data['info']['cats'], $new_data['info']['cats']);
                        $new_data['info']['cats'] = array_merge($new_cat,$new_data['info']['cats']);
                    }
                }

                if($authors && $user_data['info']['authors'] != null) {
                    if(array_diff($user_data['info']['authors'], $new_data['info']['authors']) > 0) {
                        $new_author = array_diff($user_data['info']['authors'], $new_data['info']['authors']);
                        $new_data['info']['authors'] = array_merge($new_author,$new_data['info']['authors']);
                    } 
                } else if($user_data['info']['authors']) {
                    $new_data['info']['authors'] = $user_data['info']['authors'];
                }              
               posts_balancer_db()->update_data('balancer_session',['content' => maybe_serialize($new_data)],['id_session'=>$_COOKIE['balancer']],['%s'],['%s']);
            }
        }
    }

    public function cookie_data()
    {
        if(isset($_COOKIE['balancer'])) {
            $data = posts_balancer_db()->get_data_row($_COOKIE['balancer'],'id_session','balancer_session');
            $user_data = maybe_unserialize($data->{'content'});

            if($data == null) return;

            if($data->{'content'} == '') return;

            return $user_data['info'];
        }
    }

}

function balancer_cookie()
{
    return new Post_Balancer_Cookie();
}

balancer_cookie();
