<?php

class Posts_Balancer_DB
{
    public function __construct()
    {
        
    }

    public function insert_data($table, $data = [], $replace = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $wpdb->insert($table_name, $data, $replace);
        return $this->last_id();
    }

    public function last_id()
    {
        global $wpdb;
        return $wpdb->insert_id;
    }

    public function update_data($table, $data, $condition, $data_format, $where_format)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $result = $wpdb->update($table_name, $data, $condition, $data_format, $where_format);
        return $result;
    }

    public function delete_data($table, $condition, $where_format)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $result = $wpdb->delete($table_name, $condition, $where_format);
        return $result;
    }

    public function get_all_data($table, $order_by, $limit, $offset)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_results("SELECT * FROM " . $table_name . " " . $order_by . " " . $limit . " OFFSET " . $offset, OBJECT);
        return $results;
    }

    public function get_data_by_field($table, $fields, $order_by)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_results("SELECT " . $fields . " FROM " . $table_name . " " . $order_by, OBJECT);
        return $results;
    }

    public function count_data($table, $by)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_var("SELECT count(" . $by . ") FROM " . $table_name);
        return $results;
    }

    public function get_data($id, $where, $table)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE " . $where . "=%s", $id)
        );
        return $results;
    }
    public function get_data_row($id, $where, $table)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $results = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE " . $where . "=%d", $id)
        );
        return $results;
    }
}

function posts_balancer_db()
{
    return new Posts_Balancer_DB();
}

posts_balancer_db();