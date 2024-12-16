<?php
/**
 * Plugin Name: AAB
 * Plugin URI: https://github.com/aiiddqd/aab
 * Description: Async Admin Bar - for cached websites and load toolbar asynchronously
 * Version: 1.0.0
 * License: GPL-3.0+
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

add_action('htmxer/toolbar', function (WP_REST_Request $request) {

    $context = $request->get_header('context');
    if ($context) {
        $context = json_decode($context, true);
    }
    if (empty($context)) {
        $context = [];
    }


    $actions = apply_filters('aab-actions', [], $context, $request);
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



add_action('1rest_api_init', function () {

    //hack to fix authentication
    if (wp_is_serving_rest_request()) {
        global $wp;
        if ('wp-json/app/v1/toolbar' == $wp->request) {
            remove_filter('rest_authentication_errors', 'rest_cookie_check_errors', 100);
        }
    }

    // /wp-json/app/v1/toolbar
    register_rest_route('app/v1', '/toolbar', [
        'methods' => 'GET',
        'callback' => function () {
            if (!is_user_logged_in()) {
                return new \WP_REST_Response(null, 200);
            }
            if (!current_user_can("administrator")) {
                return new \WP_REST_Response(null, 200);
            }

            header('Content-Type: text/html');
            include __DIR__ . '/view.php';
            exit;

        },
        'permission_callback' => '__return_true',
    ]);
});

add_action('wp_enqueue_scripts', function () {
    $css_file = plugin_dir_path(__FILE__) . 'dist/main.min.css';
    wp_enqueue_style('aab', plugins_url('dist/main.min.css', __FILE__), [], filemtime($css_file));
    // wp_style_add_data('aab', 'rel', 'preload');
    // wp_style_add_data('aab', 'as', 'style');

});


add_filter('style_loader_tag', function ($html, $handle) {

    if ($handle == 'aab') {
        $fallback = '<noscript>' . $html . '</noscript>';
        $preload = str_replace("rel='stylesheet'", "rel='preload' as='style' onload='this.rel=\"stylesheet\"'", $html);
        $html = $preload . $fallback;
    }

    return $html;

}, 10, 2);

