<?php
if (!defined('ABSPATH')) exit;

class StatPal_API {

    private static function get_statpal_key(){
        $key = get_option('statpal_api_key');
        // Fallback to old constant if needed
        return $key ? $key : STATPAL_API_KEY;
    }

    public static function fetch($endpoint) {
        $endpoint = ltrim($endpoint, '/');
        
        $cache_key = 'statpal_' . md5($endpoint);
        $cached = get_transient($cache_key);
        if($cached) return $cached;

        $url = "https://statpal.io/" . $endpoint;
        $url = add_query_arg('access_key', self::get_statpal_key(), $url);

        $response = wp_remote_get($url, ['timeout' => 20]);
        if(is_wp_error($response)) return false;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        set_transient('statpal_debug_last_raw', $body, 60);
        set_transient('statpal_debug_last_url', $url, 60);

        if ($data) {
            set_transient($cache_key, $data, 120);
        }

        return $data;
    }
}
