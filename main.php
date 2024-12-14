<?php
/**
 * Plugin Name: AAB
 * Plugin URI: https://github.com/aiiddqd/aab
 * Description: Async Admin Bar - for cached websites and load toolbar asynchronously
 * Version: 1.0.0
 * License: GPL-3.0+
 */


add_action('wp_footer', function () {
    ob_start(); ?>
    <div hx-get="/wp-json/app/v1/toolbar" hx-trigger="load delay:0s" hx-swap="outerHTML"></div>
    <?php return ob_get_clean();
});


add_action('rest_api_init', function () {

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
            header('Content-Type: text/html');
            include __DIR__ . '/view.php';
            exit;
        },
        'permission_callback' => '__return_true',
    ]);
});
