<?php
// xd-ce-courses/uninstall.php

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('xd_ce_options');

// Delete post meta for all posts
$posts = get_posts(array(
    'post_type' => 'post',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($posts as $post) {
    delete_post_meta($post->ID, '_xd_ce_subtitle');
}