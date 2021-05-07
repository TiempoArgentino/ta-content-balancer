<?php

class Posts_Balancer_Test
{
    public function __construct()
    {
        add_action('template_redirect', [$this,'hook_template']);
    }

    public function message($message)
    {
        return posts_balancer_session()->set_session('session_test',[$message]);
    }

    public function hook_template()
    {
        echo $this->message('soy la sessión ja');
    }
}

function posts_balancer_test()
{
    return new Posts_Balancer_Test();
}

posts_balancer_test();
