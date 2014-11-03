<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
$fp = gzopen('result.csv.gz', 'r');

$results = array();
$sources = array();
while ($rows = fgetcsv($fp)) {
    list($date, $source, $url, $title, $name) = $rows;
    if ($_GET['name'] and $name != $_GET['name']) {
        continue;
    }

    if ($_GET['time'] and 0 !== strpos($date, $_GET['time'])) {
        continue;
    }

    $sources[0] ++;
    $sources[$source] ++;

    if ($_GET['source'] and $source != $_GET['source']) {
        continue;
    }

    $result = new StdClass;
    foreach (explode('|', $name) as $n) {
        $title = str_replace($n, '', $title);
    }
    $title = str_replace('大選', '', $title);
    $title = str_replace('新北', '', $title);
    $title = str_replace('北市', '', $title);
    $title = str_replace('台北', '', $title);
    $title = str_replace('台南市', '', $title);
    $title = str_replace('台南', '', $title);
    $title = str_replace('南市', '', $title);
    $title = str_replace('南部', '', $title);
    $title = str_replace('台中市', '', $title);
    $title = str_replace('台中', '', $title);
    $title = str_replace('中市', '', $title);
    $title = str_replace('桃園市', '', $title);
    $title = str_replace('桃園', '', $title);
    $title = str_replace('桃縣', '', $title);
    $title = str_replace('高雄市', '', $title);
    $title = str_replace('高雄', '', $title);
    $title = str_replace('高市', '', $title);

    $result->title = $title;
    $result->url = $url;
    $results[] = $result;
}
header('Content-Type: text/json');

echo json_encode(array(
    'results' => $results,
    'sources' => $sources,
));
