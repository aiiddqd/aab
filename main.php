<?php
/**
 * Plugin Name: AAB
 * Plugin URI: https://github.com/aiiddqd/aab
 * Description: Async Admin Bar - for cached websites and load toolbar asynchronously
 * License: GPL-3.0+
 * Version: 1.0.0
 */

namespace AAB;

use WP_REST_Request;

add_filter('aab-actions', __NAMESPACE__ . '\\add_edit_post', 10, 2);
add_filter('aab-actions', __NAMESPACE__ . '\\add_sitekit_url', 10, 2);
add_filter('aab-actions', __NAMESPACE__ . '\\add_admin_urls');

add_action('wp_footer', function () {
    ?>
    <div hx-get="/wp-json/htmxer/toolbar" hx-trigger="load delay:0s" hx-swap="outerHTML"></div>
    <?php
});

// * url https://example.site/wp-json/htmxer/toolbar

add_action('htmxer/toolbar', function ($context) {

    if( ! is_user_logged_in()) {
        return '';
    }

    $actions = apply_filters('aab-actions', [], $context);
    if (empty($actions)) {
        return;
    }

    ?>
    <div class="aab-wrapper">
        <div>
            hi <?= wp_get_current_user()->display_name ?>
        </div>

        <?php foreach ($actions as $action): ?>
            <span class="aab-action">
                <?= $action ?>
            </span>
        <?php endforeach; ?>
    </div>
    <?php

});

add_filter('htmxer/context', function ($context) {
    global $wp;
    $post_id = get_post()->ID ?? null;
    $context['post_id'] = $post_id;
    $context['url'] = site_url($wp->request);
    return $context;
});


function add_sitekit_url($actions, $context)
{
    $url = $context['url'] ?? null;

    if ($url) {
        $url = admin_url('admin.php?page=googlesitekit-dashboard&permaLink=' . $url);
        $actions[] = sprintf('<a href="%s">SiteKit</a>', $url);
    }

    return $actions;
}

function add_edit_post($actions, $context)
{

    $post_id = $context['post_id'] ?? null;
    if ($post_id) {
        $url = get_edit_post_link($post_id);
    }

    if (empty($url)) {
        return $actions;
    }

    $actions[] = sprintf('<a href="%s">Edit</a>', $url);

    return $actions;
}

function add_admin_urls($actions)
{
    $actions[] = sprintf('<a href="%s">Admin</a>', get_admin_url());
    $actions[] = sprintf('<a href="%s">Add</a>', admin_url('post-new.php?post_type=product'));

    return $actions;
}






add_filter('style_loader_tag', function ($html, $handle) {

    if ($handle == 'aab') {
        $fallback = '<noscript>' . $html . '</noscript>';
        $preload = str_replace("rel='stylesheet'", "rel='preload' as='style' onload='this.rel=\"stylesheet\"'", $html);
        $html = $preload . $fallback;
    }

    return $html;

}, 10, 2);

