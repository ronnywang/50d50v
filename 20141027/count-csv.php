<?php

// 將 https://github.com/ronnywang/db.cec.gov.tw/tree/master/elections 的東西放到 elections/
$list = array(
    '20101101C1B1',
    '20091201C1C1',
    '20061201C1B1',
    '20051201C1C1',
    '20021201C1B1',
    '20011201C1C1',
    '19981201C1B1',
    '19971101C1C1',
    '19941201C1B1',
);

$parties[0]= array(
    '中國國民黨',
    '新黨',
    '親民黨',
);
$parties[1] = array(
    '民主進步黨',
    '台灣團結聯盟',
);

$fp = fopen(__DIR__ . '/town.csv', 'r');
$columns = fgetcsv($fp);
$town_ids = array();
while ($rows = fgetcsv($fp)) {
    $county = mb_substr($rows[1], 0, -1, 'UTF-8');
    $town = mb_substr($rows[2], 0, -1, 'UTF-8');

    if ($town_ids[$county . $town]) {
        throw new Exception("{$county}{$town} 重覆");
    }
    $town_ids[$county . $town] = $rows[0];
}

foreach ($list as $id) {
    $file = __DIR__ . "/elections/{$id}.csv";
    if (!file_exists($file)) {
        error_log($file);
        continue;
    }

    $fp = fopen($file, 'r');
    $columns = fgetcsv($fp);
    $county_rows = array();
    while ($rows = fgetcsv($fp)) {
        if (!$county_rows[$rows[0]]) {
            $county_rows[$rows[0]] = array();
        }
        $county_rows[$rows[0]][] = $rows;
    }
    fclose($fp);

    $color_names = array();

    foreach ($county_rows as $county => $records) {
        $records = array_filter($records, function($a){
            // 只取得票超過 1% 的
            return trim($a[7], '%') > 1;
        });
        usort($records, function($a, $b) {
            if ($a[6] == $b[6]) return 0;
            return $a[6] < $b[6] ? 1 : -1;
        });
        $color_records = array();
        $color_records[0] = array_filter($records, function($a) use ($parties) { return in_array($a[5], $parties[0]); });
        $color_records[1] = array_filter($records, function($a) use ($parties) { return in_array($a[5], $parties[1]); });

        if (count($color_records[0]) == 0 or count($color_records[1]) == 0) {
            error_log("{$id} 的 {$county} 並非是藍綠一比一:" . implode(', ', array_map(function($a){
                return "{$a[1]}({$a[7]}){$a[5]}";
            }, $records)));
            continue;
        }

        if (array_sum(array_map(function($a){ return trim($a[7], '%'); }, $color_records[0])) > 80) {
            error_log("{$id} 的 {$county} 太懸殊 :" . implode(', ', array_map(function($a){
                return "{$a[1]}({$a[7]}){$a[5]}";
            }, $records)));
        }

        $color_names[$county] = array();
        $color_names[$county][0] = array_map(function($a) { return $a[1]; }, $color_records[0]);
        $color_names[$county][1] = array_map(function($a) { return $a[1]; }, $color_records[1]);
    }

    $file = __DIR__ . "/elections/{$id}-1.csv";
    if (!file_exists($file)) {
        error_log($file);
        continue;
    }

    $fp = fopen($file, 'r');
    $columns = fgetcsv($fp);
    $town_records = array();
    while ($rows = fgetcsv($fp)) {
        if (!$town_records[$rows[0]]) {
            $town_records[$rows[0]] = array();
        }
        $town_records[$rows[0]][] = $rows;
    }

    $output = fopen(substr($id, 0, 4) . '.csv', 'w');
    fputcsv($output, array('id', 'county', 'town', 'blue', 'green', 'blue_rate'));
    foreach ($town_records as $town => $records) {
        $counts = array();
        $county = mb_substr($town, 0, 3, 'UTF-8');
        $town = mb_substr($town, 3, null, 'UTF-8');
        $town = str_replace('褔', '福', $town);
        $town = str_replace('台', '臺', $town);
        if (!$color_names[$county]) {
            continue;
        }
        foreach ($records as $rows) {
            for ($i = 0; $i < 2; $i ++) {
                if (in_array($rows[1], $color_names[$county][$i])) {
                    $counts[$i] += $rows[3];
                }
            }
        }
        $short_county = mb_substr(str_replace('臺北縣', '新北市', $county), 0, -1, 'UTF-8');
        $short_town = mb_substr($town, 0, -1, 'UTF-8');
        if (!$town_id = $town_ids[$short_county . $short_town]) {
            continue;
        }
        fputcsv($output, array(
            $town_id,
            $county,
            $town,
            $counts[0],
            $counts[1],
            100 * $counts[0] / ($counts[0] + $counts[1]),
            implode(' ', $color_names[$county][0]),
            implode(' ', $color_names[$county][1]),
        ));
    }
    fclose($output);
}
