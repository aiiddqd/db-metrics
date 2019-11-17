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

        add_action('wp_dashboard_setup', [__CLASS__, 'add_widget']);

        add_action( 'admin_enqueue_scripts', function(){
            wp_enqueue_script( 'google-chart', 'https://www.gstatic.com/charts/loader.js' );
            wp_enqueue_script( 'wp-api' );


        }, 99 );

        add_action('admin_footer', [__CLASS__, 'add_js']);

        add_action('rest_api_init', function () {
            register_rest_route('ba/v1', '/metrics/posts', array(
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'metrics_posts_data']
            ));
        });


    }

    public static function get_data(){
        $args = [
            'post_type' => ['post', 'product'],
            'date_query' => array(
                array(
                    'after'   => '-4 month',
                ),
            ),
        ];
        $data = get_posts($args);

        $data = [
            ['Месяц и год', 'Количество'],
            ['08 2019', 33],
            ['10 2019', 54],
            ['11 2019', 76],
        ];

        return $data;
    }

    public static function metrics_posts_data($request)
    {
        $data = self::get_data();
        $response = new \WP_REST_Response($data);
        return $response;
    }

    public static function add_js(){
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function(event) {
                var data_api;

                // 1. Создаём новый объект XMLHttpRequest
                var xhr = new XMLHttpRequest();
                var apiUrl = wpApiSettings.root + 'ba/v1/metrics/posts';

                // 2. Конфигурируем его: GET-запрос на URL 'phones.json'
                xhr.open('GET', apiUrl, false);

                // 3. Отсылаем запрос
                xhr.send();

                // 4. Если код ответа сервера не 200, то это ошибка
                if (xhr.status != 200) {
                // обработать ошибку
                    // alert( xhr.status + ': ' + xhr.statusText ); // пример вывода: 404: Not Found
                } else {
                // вывести результат
                    // alert( xhr.responseText ); // responseText -- текст ответа.
                    data_api = JSON.parse(xhr.responseText);

                }

                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {

                    var data = google.visualization.arrayToDataTable(data_api);

                    var options = {
                        title: "Посты по месяцам",
                        bar: {groupWidth: "95%"},
                        legend: { position: "none" },
                    };            
                    
                    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                    chart.draw(data, options);

                }
            });

        </script>

        <?php 
        
    }

  
    public static function add_widget() {
        
        global $wp_meta_boxes;
        wp_add_dashboard_widget(
            'custom_help_widget', 
            'Metrics', 
            [__CLASS__, 'render_widget']
        );
        
    }
 
    public static function render_widget() {
        ?>

        <div id="chart_div"></div>
        <?php 
    }
}

DashboardMetrics::init();