<?php

class Posts_Balancer_Template
{

    public function __construct()
    {
        add_filter('template_include', [$this, 'personalize'], 99);
    }

    public function balancer_load_template($filename = '')
    {
        if (!empty($filename)) {
            if (locate_template('balancer/' . $filename)) {
                /**
                 * Folder in theme for subscriptions templates, this folder must be created into your theme.
                 */
                $template = locate_template('balancer/' . $filename);
            } else {
                /**
                 * Default folder of templates
                 */
                $template = dirname(__FILE__) . '/partials/' . $filename;
            }
        }
        return $template;
    }

    public function personalize($template)
    {
        if (is_page(get_option('personalize')))
            $template = $this->balancer_load_template('pages/personalize.php');
        return $template;
    }
}

function balancer_template()
{
    return new Posts_Balancer_Template();
}

balancer_template();