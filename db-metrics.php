<?php 
/*
Plugin Name: DB Metrics
Description: Dashboard metrics for WordPress
Author: WPCraft
Version: 0.1
*/

namespace BA;


class DashboardMetrics {



    public static function init(){


        add_action( 'admin_enqueue_scripts', function(){
            wp_enqueue_script( 'google-chart', 'https://www.gstatic.com/charts/loader.js' );
            wp_enqueue_script( 'wp-api' );
        }, 99 );


        require_once __DIR__ . '/inc/AllPosts.php';

    }

}

DashboardMetrics::init();