<?php

$list = array(
    "連勝文", "陳汝斌", "柯文哲", "馮光遠", "陳永昌", "李宏信", "趙衍慶",
    "朱立倫", "游錫堃", "李進順",
    "吳志揚", "鄭文燦", "許睿智",
    "胡志強", "林佳龍",
    "黃秀霜", "賴清德",
    "楊秋興", "陳菊", "周可盛",
);

$files = glob("news-data");
$output = fopen('php://output', 'w');
sort($files);
foreach ($files as $file) {
    $fp = gzopen($file, 'r');
    while (!feof($fp)) {
        $meta = json_decode(fgets($fp));
        $title = json_decode(fgets($fp));
        $content = json_decode(fgets($fp));

        $hit = array();
        foreach ($list as $name) {
            if (false !== strpos($titlei . $content, $name)) {
                $hit[] = $name;
            }
        }

        if ($hit) {
            fputcsv($output, array(
                date('Ymd', $meta->created_at),
                $meta->source,
                $meta->url,
                $title,
                implode('|', $hit),
            ));

        }

    }
}
