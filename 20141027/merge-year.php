<?php

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

$years = array_map(function($a) { return substr($a, 0, 4); }, $list);
$years = array_reverse($years);
$records = array();
$columns = array();
$columns[] = 'id';
for ($i = 1; $i < count($years) - 1; $i ++) {
    $year_a = $years[$i - 1];
    $year_b = $years[$i];

    $year_str = "{$years[$i]}-{$years[$i + 1]}";
    $columns[] = "{$year_str}";
    $columns[] = "{$year_str}藍代表";
    $columns[] = "{$year_str}綠代表";
    $columns[] = "{$year_str}藍得票";
    $columns[] = "{$year_str}綠得票";

    $fin = fopen("{$year_a}.csv", "r");
    fgetcsv($fin); // columns
    while ($rows = fgetcsv($fin)) {
        if (!$records[$rows[0]]) {
            $records[$rows[0]] = array_fill(0, 5 * count($years) - 10, -1);
        }
        $records[$rows[0]][5 * $i - 5] = $rows[5];
        $records[$rows[0]][5 * $i - 4] = $rows[6];
        $records[$rows[0]][5 * $i - 3] = $rows[7];
        $records[$rows[0]][5 * $i - 2] = $rows[3];
        $records[$rows[0]][5 * $i - 1] = $rows[4];
    }
    fclose($fin);
    if ($year_a == 2006) {
        // 需要把 2005 年的 臺北縣、高雄縣、臺南縣、臺中縣、臺中市拉進來
        $fin = fopen('2005.csv', 'r');
        fgetcsv($fin); // columns
        while ($rows = fgetcsv($fin)) {
            if (in_array($rows[1], array('臺北縣', '高雄縣', '臺南縣', '臺中縣', '臺中市'))) {
                if (!$records[$rows[0]]) {
                    $records[$rows[0]] = array_fill(0, 5 * count($years) - 10, -1);
                }
                $records[$rows[0]][5 * $i - 5] = $rows[5];
                $records[$rows[0]][5 * $i - 4] = $rows[6];
                $records[$rows[0]][5 * $i - 3] = $rows[7];
                $records[$rows[0]][5 * $i - 2] = $rows[3];
                $records[$rows[0]][5 * $i - 1] = $rows[4];
            }
        }
        fclose($fin);
    }

    $fin = fopen("{$year_b}.csv", "r");
    fgetcsv($fin); // columns
    while ($rows = fgetcsv($fin)) {
        if (!$records[$rows[0]]) {
            $records[$rows[0]] = array_fill(0, 5 * count($years) - 10, -1);
        }
        $records[$rows[0]][5 * $i - 5] = $rows[5];
        $records[$rows[0]][5 * $i - 4] = $rows[6];
        $records[$rows[0]][5 * $i - 3] = $rows[7];
        $records[$rows[0]][5 * $i - 2] = $rows[3];
        $records[$rows[0]][5 * $i - 1] = $rows[4];
    }
    fclose($fin);
}

$county_year_log = array();
foreach ($years as $year) {
    $fp = fopen("{$year}.csv", "r");
    fgetcsv($fp);
    while ($rows = fgetcsv($fp)) {
        if (!$county_year_log[$rows[0]]) {
            $county_year_log[$rows[0]] = array(
                'county' => $rows[1],
                'town' => $rows[2],
            );
        }
    }
}
$output = fopen('php://output', 'w');

$columns[] = '縣市';
$columns[] = '鄉鎮';

fputcsv($output, $columns);
foreach ($records as $id => $rows) {
    $rows[] = $county_year_log[$id]['county'];
    $rows[] = $county_year_log[$id]['town'];
    fputcsv($output, array_merge(array($id), $rows));
}
