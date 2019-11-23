<?php 
/*
Plugin Name: DB Metrics
Description: Dashboard metrics for WordPress
Author: WPCraft
Version: 0.1
*/
namespace BA;

class DashboardMetrics 
{
    public static function init()
    {
        require_once __DIR__ . '/inc/AllPosts.php';
        require_once __DIR__ . '/inc/PostsByAuthors.php';

        add_action( 'admin_enqueue_scripts', function($hook)
        {
            if( 'index.php' != $hook){
                return;
            }

            wp_enqueue_script( 'google-chart', 'https://www.gstatic.com/charts/loader.js' );
            wp_enqueue_script( 'wp-api' );
        }, 99 );
    }

}

DashboardMetrics::init();