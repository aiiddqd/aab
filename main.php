<?php
/**
 * Plugin Name: AAB
 * Plugin URI: https://github.com/aiiddqd/aab
 * Description: Async Admin Bar - for cached websites and load toolbar asynchronously
 * Version: 1.0.0
 * License: GPL-3.0+
 */


add_action('wp_footer', function () {
    ?>
    <div hx-get="/wp-json/app/v1/toolbar" hx-trigger="load delay:0s" hx-swap="outerHTML"></div>
    <?php
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
            if (!is_user_logged_in()) {
                return;
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


add_filter('style_loader_tag', function($html, $handle){

    if($handle == 'aab') {
        $fallback = '<noscript>' . $html . '</noscript>';
        $preload = str_replace("rel='stylesheet'", "rel='preload' as='style' onload='this.rel=\"stylesheet\"'", $html);
        $html = $preload . $fallback;
    }

    return $html;

}, 10, 2);

