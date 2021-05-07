<?php

class Posts_Balancer_Session
{
    public $messages = [];

    public function __construct()
    {
        add_action('init',[$this,'sessions'],1);
        
        add_action('wp_loaded',[$this,'set_session'],10,2);

        if(isset($_SESSION['flash_messages'])) {
            self::$messages = $_SESSION['flash_messages'];

            $_SESSION['flash_messages'] = [];
        }
    }
     /**
     * https://www.php.net/manual/es/function.setcookie.php
     */
    public function set_cookie($name, $value, $expire = 0, $secure = false, $httponly = false)
    {
       if(!headers_sent()){
           setcookie($name, $value, $expire, $secure, $httponly);
       } else {
           headers_sent($file,$line);
           trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
       }  
    }
     /**
     * Initialize the sessions
     */
    public function sessions() 
    {
        if(!headers_sent()) {
            if(!session_id()) {
                session_start();
            }
        }
    }
    /**
     * Session set
     */
    public function set_session($session_name,$data = [])
    {
        if(!isset($_SESSION[$session_name])) {
            $_SESSION[$session_name] = $data;
        }
    }
     /**
     * Session get
     */
    public function get_session($session_name)
    {
        if(isset($_SESSION[$session_name])) {
            return $_SESSION[$session_name];
        } else {
            return;
        }
    }
    /**
     * Session update
     */
    public function update_session($session_name,$val)
    {
        if(isset($_SESSION[$session_name])) {
            return $_SESSION[$session_name] = $val;
        } else {
            return;
        }
    }
    /**
     * Session destroy
     */
    public function destroy_session($session_name)
    {
        unset($_SESSION[$session_name]);
    }
    /***
     * Set Flash messages
     */
    public function set_flash_session($class, $msg)
    {
        /**
         * Init sessions if not
         */
        if (!session_id()) {
            session_start();
        }
        /**
         * Create session if not exist
         */
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = ["hola"];
        }

        $_SESSION['flash_messages'] = [
            'name' => $class,
            'msg' => $msg
        ];

        return $_SESSION['flash_messages'];
    }
     /**
     * Show Flash Messages
     */
    public function show_flash_session()
    {
        if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {

            echo '<div class="notice notice-' . $_SESSION['flash_messages']['name'] . ' is-dismissible">
                    <p>' . $_SESSION['flash_messages']['msg'] . '</p>
                </div>';
        }
        unset($_SESSION['flash_messages']);
    }
}

function posts_balancer_session()
{
    return new Posts_Balancer_Session();
}

posts_balancer_session();