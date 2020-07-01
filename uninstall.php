<?php
/**
 * Trigger for Plugin uninstall
 * @package ArafatPlugin
 */
if(!defined('WP_UNISTALL_PLUGIN')){ //this is for security
    die;
}


global $wpdb; //to trigger sql command
$wpdb-> query("DELETE FROM wp_posts WHERE post_type= 'book' ");
$wpdb-> query("DELETE FROM wp_postmeta WHERE post_id NOT IN(SELECT id FROM wp_posts)");
$wpdb-> query("DELETE FROM wp_term_relationships WHERE object_id NOT IN(SELECT id FROM wp_posts)");
