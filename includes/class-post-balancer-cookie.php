<?php

class Post_Balancer_Cookie
{
    public function __construct()
    {
        add_action('wp', [$this, 'init_cookie'],1);
        add_action('wp_head', [$this,'balancer_cookie']);
    }

    public function init_cookie()
    {
        if (!isset($_COOKIE['balancer'])) {
            setcookie('balancer', uniqid() . date('YmdHmi'), time() + 86400, '/');
        }
    }

    /**
    *   Actualiza o inicializa la cookie del balanceo para un usuario
    */
    public function populate_cookie()
    {
        if (isset($_COOKIE['balancer'])) {

            if(is_user_logged_in()){
                $user_id = get_current_user_id();
                $result = posts_balancer_db()->get_row('balancer_session','id_session=%s',$_COOKIE['balancer']);
                if($result == null && $result->{'user_id'} == null) {

                    $data = [
                       'id_session' => $_COOKIE['balancer'],
                       'user_id' => $user_id,
                       'content' => ''
                   ];
                   return posts_balancer_db()->insert_data('balancer_session',$data,['%s','%s']);
               } else if($result != null && $result->{'user_id'} == null) {
                    return posts_balancer_db()->update_data('balancer_session',['user_id' => $user_id],['id_session'=>$_COOKIE['balancer']],['%d'],['%d']);
               } else {
                   return false;
               }
           } else { // WARNING: Esta guardando para usuarios no logueados???????????

                $result = posts_balancer_db()->get_row('balancer_session','id_session=%s',$_COOKIE['balancer']);
                if($result == null) {

                     $data = [
                        'id_session' => $_COOKIE['balancer'],
                        'content' => ''
                    ];

                    return posts_balancer_db()->insert_data('balancer_session',$data,['%s','%s']);
                } else {
                    return false;
                }
            }
        }


    }

    /**
    *   Actualiza los gustos del usuario
    */
    public function balancer_cookie()
    {
        global $wpdb;
        if (is_single()) { // TODO: Deberia chequear si es del post type que se quiere balancear

            // WARNING: Doble llamada al mismo method ?
            if(!$this->populate_cookie()){
                $this->populate_cookie();
            }

            if(is_user_logged_in()) {
                // WARNING: Por que actualiza el user_id ????????? casi seguro que el parametro 2 y 3 estan intercambiados
               posts_balancer_db()->update_data('balancer_session',['user_id' => get_current_user_id()],['id_session'=>$_COOKIE['balancer']],['%d'],['%d']);
            }

            $post_id = get_queried_object_id();
            $categories = get_the_terms($post_id, get_option('balancer_editorial_taxonomy'));
            $authors = get_the_terms($post_id, get_option('balancer_editorial_autor'));
            $tags = get_the_terms($post_id, get_option('balancer_editorial_tags'));
            // WARNING: $categories, $author y $tags pueden devolver false, o WP_Error, que no evaluan como null
            // y el ultimo tampoco como falso.
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

            $tag = [];

            if($tags) {
                foreach($tags as $t) {
                    $tag[] = $t->term_id;
                }
            }

            $array = [];

            $array['info']['posts'] = [$post_id];
            $array['info']['cats'] = $cat;
            $array['info']['tags'] = $tag;
            $array['info']['authors'] = $author;


            //**add info */

            $data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}balancer_session WHERE id_session = %s", $_COOKIE['balancer'] ));
            $user_data = maybe_unserialize($data->{'content'});

            // TODO Aca podemos calcular el peso de los terms, teniendo en cuenta los datos ya guardados en la DB ($user_data)
            // y los nuevos ($array)
            // TODO: Cambiar nombre $array a algo mas expresivo

            if($data->{'content'} === '') {
                posts_balancer_db()->update_data('balancer_session',['content' => maybe_serialize($array)],['id_session'=>$_COOKIE['balancer']],['%s'],['%s']);
            } else {
                $new_data = [];
                $new_data['info']['posts'] = [$post_id];
                $new_data['info']['cats'] = $cat;
                $new_data['info']['tags'] = $tag;
                $new_data['info']['authors'] = $author;

                // WARNING: En estos chequeos se repite el mismo array_diff 2 veces. Creo que con hacer los merge de
                // una se consigue el mismo resultado, porque el merge pisa los valores que se repiten.
                // WARNING: array_diff devuelve un array, pero se lo esta comparando con > 0. Esto devuelve true incluso para arrays vacios

                if($user_data['info'] != null) {
                    if($user_data['info']['posts'] != null) {
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

                    if($user_data['info']['tags'] != null) {
                        if(array_diff($user_data['info']['tags'], $new_data['info']['tags']) > 0) {
                            $new_tag = array_diff($user_data['info']['tags'], $new_data['info']['tags']);
                            $new_data['info']['tags'] = array_merge($new_tag,$new_data['info']['tags']);
                        }
                    }

                    if($user_data['info']['authors'] != null) {
                        if(array_diff($user_data['info']['authors'], $new_data['info']['authors']) > 0) {
                            $new_author = array_diff($user_data['info']['authors'], $new_data['info']['authors']);
                            $new_data['info']['authors'] = array_merge($new_author,$new_data['info']['authors']);
                        }
                        // WARNING: Cual es el proposito de este else?? solo entraria en el caso de que evalue false pero no sea null
                    } else if($user_data['info']['authors']) {
                        $new_data['info']['authors'] = $user_data['info']['authors'];
                    }
                    posts_balancer_db()->update_data('balancer_session',['content' => maybe_serialize($new_data)],['id_session'=>$_COOKIE['balancer']],['%s'],['%s']);
                }
            }
        }
    }

    public function cookie_data()
    {
        global $wpdb;
        if(isset($_COOKIE['balancer'])) {

            if(is_user_logged_in()) {
                $user_id = get_current_user_id();
                $data = posts_balancer_db()->get_row('balancer_session','user_id=%d',$user_id);
            } else { // WARNING: Por que busca datos de usuarios no logueados ????
                $data = posts_balancer_db()->get_row('balancer_session','id_session=%s',$_COOKIE['balancer']);
            }

            $user_data = maybe_unserialize($data->{'content'});

            if($data == null) return;

            if($data->{'content'} == '') return;

            return $user_data['info'];
        }
    }

}

// WARNING: Esta funcion se llama cada vez que se quiere acceder a un metodo de la clase
// Post_Balancer_Cookie, corriendo el __construct, que lleva a cabo tareas que solo deberian
// ocurrir una vez.
function balancer_cookie()
{
    return new Post_Balancer_Cookie();
}

balancer_cookie();
