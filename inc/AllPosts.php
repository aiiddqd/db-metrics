<?php 

namespace DBMetrics;

class AllPosts {

    static public $data = [];
    static public $widget_title = 'Количество постов по месяцам';
    static public $div_id = 'chart_all_posts';
    static public $wname = 'chartAllPosts';

    public static function init(){
        add_action('wp_dashboard_setup', [__CLASS__, 'add_widget']);
        add_action('rest_api_init', function () {
            register_rest_route('ba/v1', '/metrics/posts', array(
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'metrics_posts_data']
            ));
        });
    }


    public static function get_data(){

        if(self::$data = get_transient('ba_posts_by_months')){
            return self::$data;
        }

        $args = [
            'post_type' => ['post', 'product'],
            'order' => 'ASC',
            'numberposts' => -1,
            'date_query' => array(
                array(
                    'after'   => '-5 month',
                ),
            ),
        ];

        $posts = get_posts($args);

        foreach($posts as $post){
            $timestamp = strtotime($post->post_date);
            $ym = date('Y m', $timestamp);
            self::count_ym($ym);
        }

        set_transient('ba_posts_by_months', self::$data, HOUR_IN_SECONDS);

        return self::$data;
    }

    public static function count_ym($ym)
    {
        foreach(self::$data as $row_key => $row){
            if($row[0] == $ym){
                self::$data[$row_key][1]++;
                return;
            }
        }

        self::$data[] = [$ym, 1];

    }

    public static function metrics_posts_data($request)
    {
        $data = [];

        $data = self::get_data();
        array_unshift($data, ["Год и месяц", "Количество"]);

        $response = new \WP_REST_Response($data);
        return $response;
    }

    public static function add_widget()
    {
        wp_add_dashboard_widget(
            self::$wname, 
            $title = self::$widget_title, 
            [__CLASS__, 'render_widget']
        );
    }
 
    public static function render_widget()
    {?>
        <div id="<?= self::$div_id ?>"></div>
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
                    
                    var chart = new google.visualization.ColumnChart(document.getElementById('<?= self::$div_id ?>'));
                    chart.draw(data, options);

                }
            });

        </script>
        <?php 
    }
}

AllPosts::init();