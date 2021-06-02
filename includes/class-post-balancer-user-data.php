<?php

// TODO: Revisar donde se utiliza la funcion balancer_cookie y actualizar acordemente
class Post_Balancer_User_Data{
    /**
    *   @property bool $initialized
    *   Whether the module has been initialized or not
    */
    static private $initialized = false;

    /**
    *   @property mixed[] $current_user_data
    *   The balancer data for the current user. This user can be logged or not.
    *   Is the one that gets sent to the client.
    */
    static private $current_user_data = null;

    /**
    *   @property bool $current_user_personalized
    *   Indicates if the current user has personalized tastes.
    */
    static private $subscriber_has_db_row = false;

    /**
    *   @property bool $current_user_personalized
    *   Indicates if the current user has personalized tastes.
    */
    static private $current_user_personalized = false;

    /**
    *   @property bool $current_user_is_subscriber
    *   Indicates if the current user is logged in and a subscriber
    */
    static private $current_user_is_subscriber = false;

    /**
    *   @property int $max_preferences_items
    *   The maximum amounts of balancer items to stored for each preference
    *   Set to -1 for no limit.
    */
    static private $max_preferences_items = 30;

    /**
    *   @method initialize
    *   Initializes the module, stablishing variables, running neccesary scripts
    *   and hooking to wordpress actions/filters
    */
    static public function initialize(){
        if(self::$initialized == true)
            return false;
        self::$initialized = true;

        add_action('wp_enqueue_scripts', [self::class,'enqueue_front_scripts']);
        // TODO: Add an easy way for a developer to manipulate the user balancer data
        // maybe with hooks, passing data like is_logged, has_personalized, post_data, etc.
        add_action('wp_head', [self::class,'set_current_user_data']);
        add_action('wp_head', [self::class,'update_current_user_data']);
        // TODO: This step should be optional. Maybe its not wanted in the front
        // For this, some of the logic perfomed on the front should be done in the backend too
        add_action('wp_head', [self::class,'send_balancer_data_to_client']);

        return true;
    }

    /**
    *   Front scripts enqueue
    *   Sends a variable to the client that contains the data to use to balance the
    *   articles fetch. If the user is logged in, it contains data from the DB.
    *   If he isn't, the data will be from the current post. If not in a post, data will be empty
    */
    static public function enqueue_front_scripts(){
        wp_enqueue_script('storage_ajax_script', plugin_dir_url(__FILE__) . 'js/balancer-storage.js', array('jquery'), '1.0', true);
    }

    // TODO: Este method deberia estar en class-posts-balancer-db.php
    /**
    *
    *   Returns an user row in the balancer table
    *   @param int $user_id                                                     ID of the user for whom the retrieve the data
    *   @return mixed[]
    */
    static public function get_subscriber_balancer_row($user_id){
        return posts_balancer_db()->get_row('balancer_session','user_id=%d',$user_id);
    }

    /**
    *   Returns the data stored from the user navigation or personalizer
    *   @param int $user_id                                                     ID of the user for whom the retrieve the data
    *   @return mixed[]|false                                                   Returns false if the user has no balancer record in DB
    */
    static public function get_subscriber_balancer_data($user_id){
        $data = null;
        $personalized = Posts_Balancer_Personalize::get_user_personalizer_data($user_id);
        if($personalized){ // personalized
            // WARNING:
            // TODO: Esto se hacer porque el personalizador guarda el nombre del lugar. Este deberia guardar la id y este mapeo se deberia evitar
            if(isset($personalized['location']) && $personalized['location']){
                $term = get_term_by('name', $personalized['location'], get_option('balancer_editorial_place'));
                $personalized['locations'] = $term ? [$term->term_id] : [];
            }
            $data = array( 'info' => $personalized );
        }
        else {
            $row = self::get_subscriber_balancer_row($user_id);
            $data = maybe_unserialize($row->content);
        }

        return $data === null ? false : $data;
    }

    /**
    *   Returns the data related to a post that is used in the balancer
    *   @param int $post_id
    *   @return mixed[]
    */
    // WARNING: Este metodo no corresponde a esta clase. Incluso podria ser una function suelta
    static public function get_post_balanceable_data($post_id){
        // TODO: Check de errores y tipos de datos. Existe el post? etc.
        $categories = get_the_terms($post_id, get_option('balancer_editorial_taxonomy'));
        $tags = get_the_terms($post_id, get_option('balancer_editorial_tags'));
        $authors = get_the_terms($post_id, get_option('balancer_editorial_autor'));
        $topics = get_the_terms($post_id, get_option('balancer_editorial_topics'));
        $locations = get_the_terms($post_id, get_option('balancer_editorial_place'));

        $balanceable_data = array(
            'info'  => array(
                'posts'     => [$post_id],
                'cats'      => is_array($categories) ? wp_list_pluck($categories, 'term_id') : [],
                'tags'      => is_array($tags) ? wp_list_pluck($tags, 'term_id') : [],
                'authors'   => is_array($authors) ? wp_list_pluck($authors, 'term_id') : [],
                'topics'    => is_array($topics) ? wp_list_pluck($topics, 'term_id') : [],
                'locations' => is_array($locations) ? wp_list_pluck($locations, 'term_id') : [],
            ),
        );

        return $balanceable_data;
    }

    /**
    *   Merges an array of balancer data with another.
    *   @param mixed[] $dataA                                                   Array with main data
    *   @param mixed[] $dataB                                                   Array with data to append
    *   @param int $max                                                         Max amount of items the result can have.
    *                                                                           Pass -1 to indicate no limit
    */
    static public function merge_data($dataA, $dataB, $max = -1){
        $new_data = $dataA ?? [ 'info' => [] ];
        $data_slugs = ['posts', 'cats', 'tags', 'authors', 'locations', 'topics'];
        if($dataB) {
            foreach ($data_slugs as $data_slug){ // array_unique to remove duplicated. array_values to reset indexes
                $data_db = $dataA['info'][$data_slug] ?? [];
                $data_post = $dataB['info'][$data_slug] ?? [];
                $merged_data = array_values( array_unique( array_merge($data_db, $data_post), SORT_REGULAR) );
                $merged_amount = count($merged_data); // 7
                if($max == -1)
                    $new_data['info'][$data_slug] = $merged_data;
                else {
                    $dif_max = $merged_amount - $max;
                    $new_data['info'][$data_slug] = $dif_max > 0 ? array_slice($merged_data, $dif_max) : $merged_data;
                }
            }
        }
        return $new_data;
    }

    /**
    *   Inserts a balancer row for a user in the DB
    *   @param int $user_id
    *   @param string $content                                                  The balancer data to store for this user
    *   @return mixed[]                                                         Returns the content from the user balancer
    */
    static public function insert_subscriber_balancer_row($user_id, $content = ''){
        posts_balancer_db()->insert_data('balancer_session', array(
            'user_id' => $user_id,
            'content' => $content ?? '',
        ),['%d','%s']);
    }

    /**
    *   Stablishes the current user balancer data.
    */
    static public function set_current_user_data(){
        self::$current_user_is_subscriber = is_user_logged_in();
        self::$current_user_personalized = self::$current_user_is_subscriber && Posts_Balancer_Personalize::user_is_personalized(get_current_user_id());
        if( self::$current_user_is_subscriber ){ // DB + Post data
            $balancer_data = self::get_subscriber_balancer_data(get_current_user_id());
            self::$current_user_data = $balancer_data ? $balancer_data : null;
            self::$subscriber_has_db_row = $balancer_data === false ? false : true;
        }
    }

    /**
    *   Runs a series of functions that updates the value of $current_user_data
    *   accordingly. It also updates the DB when requiered
    */
    static public function update_current_user_data(){
        if(self::$current_user_personalized)
            return;

        self::update_current_user_based_on_current_single();
        self::update_current_user_db();
    }

    /**
    *   Updates the current user balancer data based with the current single post balancer data
    *   It stores de data in $current_user_data. If no user, it only stores the current post data.
    */
    static public function update_current_user_based_on_current_single(){
        if ( is_single() )// TODO: Deberia chequear si es del post type que se quiere balancear
            self::update_current_user_based_on_post(get_queried_object_id());
    }

    /**
    *   Updates the current user balancer data based on a post balancer data
    *   @param int $post_id
    */
    static public function update_current_user_based_on_post($post_id){
        self::$current_user_data = self::merge_data(self::$current_user_data, self::get_post_balanceable_data($post_id), self::$max_preferences_items);
    }

    /**
    *   If the user is logged in and isn't personalized, it saves to the DB the final state of self::$current_user_data
    */
    static public function update_current_user_db(){
        if(self::$current_user_is_subscriber && !self::$current_user_personalized){
            // TODO: Esta serializacion deberia ser abstraida a algun method en esta u otra clase.
            $serialized_content = maybe_serialize(self::$current_user_data);
            if(!self::$subscriber_has_db_row)
                self::insert_subscriber_balancer_row(get_current_user_id(), $serialized_content);
            else
                posts_balancer_db()->update_data('balancer_session',['content' => $serialized_content],['user_id'=>get_current_user_id()],['%s'],['%d']);
        }
    }

    /**
    *   Sends a variable to the client that contains the data to use to balance the
    *   articles fetch. If the user is logged in, it contains data from the DB.
    *   If he isn't, the data will be from the current post. If not in a post, data will be empty
    */
    static public function send_balancer_data_to_client(){
        $balancer_data = array(
            'userPreferences'   => self::$current_user_data,
            'percentages'       => array(
                'views'     => intval(get_option('_balancer_percent_views')),
                'user'      => intval(get_option('_balancer_percent_user')),
                'editorial' => intval(get_option('_balancer_percent_editorial')),
            ),
            // WARNING: No podemos depender de una variable global para saber si el usuario esta
            // logeado o no. Esto se deja asi para pruebas, pero lo ideal seria realizar un fetch
            // desde el cliente para determinar si esta o no logeado.
            'isLogged'      => self::$current_user_is_subscriber,
            'maxPreferenceItems'    => self::$max_preferences_items,
        );
        wp_localize_script( 'storage_ajax_script', 'postsBalancerData', $balancer_data );
    }
}

Post_Balancer_User_Data::initialize();


// WARNING: Esta funcion se llama cada vez que se quiere acceder a un metodo de la clase
// Post_Balancer_Cookie, corriendo el __construct, que lleva a cabo tareas que solo deberian
// ocurrir una vez.
// function balancer_cookie()
// {
//     return new Post_Balancer_Cookie();
// }
