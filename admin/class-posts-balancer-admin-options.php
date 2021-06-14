<?php

class Posts_Balancer_Options
{
    public function __construct()
    {
        add_action('admin_menu', [$this,'balancer_options']);


        add_action('balancer_admin_actions',[$this,'percent']);

        //$this->percent();
        $this->save();
    }

    public function balancer_options()
    {
        add_submenu_page(
            'options-general.php',
        __( 'Balancer', 'posts-balancer' ),
        __( 'Balancer', 'posts-balancer' ),
        'manage_options',
        'balancer-options',
        [$this,'balancer_callback']
        );
    }

    public function balancer_callback()
    {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/posts-balancer-admin-display.php';
    }

    /**
    *   Returns the percentages set through the admin panel
    *   @return int[] {
    *       @property int views
    *       @property int user
    *       @property int editorial
    *   }
    */
    static public function get_percentages(){
        return array(
            'views'     => intval(get_option('_balancer_percent_views')),
            'user'      => intval(get_option('_balancer_percent_user')),
            'editorial' => intval(get_option('_balancer_percent_editorial')),
        );
    }

    public function percent()
    {

        if(isset($_POST['percent']) < 10 && isset($_POST['views']) && isset($_POST['user'])){
            $porcentaje = $_POST['percent'] + $_POST['views'] + $_POST['user'];
            if($porcentaje < 100 || $porcentaje > 100){
                echo 'no';
                return;
            }
        }

        if(isset($_POST['percent'])){
            update_option('_balancer_percent_editorial',$_POST['percent'],true);
        }

        if(isset($_POST['views'])){
            update_option('_balancer_percent_views',$_POST['views'],true);
        }

        if(isset($_POST['user'])){
            update_option('_balancer_percent_user',$_POST['user'],true);
       }
    }

    public function save()
    {
        if(isset($_POST['balancer_editorial_taxonomy'])){
            update_option('balancer_editorial_taxonomy',$_POST['balancer_editorial_taxonomy'],true);
        }

        if(isset($_POST['balancer_editorial_autor'])){
            update_option('balancer_editorial_autor',$_POST['balancer_editorial_autor'],true);
        }

        if(isset($_POST['balancer_editorial_tags'])){
            update_option('balancer_editorial_tags',$_POST['balancer_editorial_tags'],true);
        }

        if(isset($_POST['balancer_editorial_topics'])){
            update_option('balancer_editorial_topics',$_POST['balancer_editorial_topics'],true);
        }

        if(isset($_POST['balancer_editorial_place'])){
            update_option('balancer_editorial_place',$_POST['balancer_editorial_place'],true);
        }

        if(isset($_POST['balancer_editorial_post_type'])){
            update_option('balancer_editorial_post_type',$_POST['balancer_editorial_post_type'],true);
        }

        if(isset($_POST['balancer_editorial_days'])){
            update_option('balancer_editorial_days',$_POST['balancer_editorial_days'],true);
        }
    }

}

function balancer_options()
{
    return new Posts_Balancer_Options();
}
balancer_options();
