<?php

class Converter
{
    public function getLines()
    {
        $fp = fopen(__DIR__ . '/20100601S1E1.csv', 'r');
        $columns = fgetcsv($fp);
        $lines = array();
        while ($values = fgetcsv($fp)) {
            $lines[] = $values;
        }
        fclose($fp);

        $fp = fopen(__DIR__ . '/20101101V1B3.csv', 'r');
        $columns = fgetcsv($fp);
        while ($values = fgetcsv($fp)) {
            $lines[] = $values;
        }
        fclose($fp);

        return $lines;

    }

    public function main($type)
    {
        $output = fopen('php://output', 'w');
        $lines = $this->getLines();

        $parties = array(
            '無' => 0,
            '無黨籍及未經政黨推薦' => 0,
            '中國國民黨' => 1,
            '民主進步黨' => 2,
            '台灣團結聯盟' => 3,
            '中華統一促進黨' => 4,
            '親民黨' => 5,
            '綠黨' => 6,
        );

        switch ($type) {
        case 'age':
            fputcsv($output, array(
                '村里', '姓名', '年齡', '性別', '政黨', '政黨ID', '得票率', 'COUNTY_ID', 'TOWN_ID', 'VILLAGE_ID', 'OBJECT_ID',
            ));
            foreach ($lines as $values) {
                if ($values[8] != '*') {
                    continue;
                }
                $id = $this->findVillageID($values[0]);
                if (!$id) {
                    error_log($values[0]);
                }
                fputcsv($output, array(
                    $values[0], // 村里
                    $values[1], // 姓名
                    2014 - $values[4], // 年齡
                    ('男' == $values[3]) ? 1: 0, // 性別(1 男, 0 女)
                    $values[5], // 政黨
                    $parties[$values[5]], // 政黨ID
                    floatval(rtrim($values[7], '%')), // 得票率
                    $id[0], // COUNTY_ID
                    $id[1], // TOWN_ID
                    $id[2], // VILLAGE_ID
                    $id[3], // OBJECT_ID
                ));

            }
            break;

        default:
            throw new Exception("type must be age");
        }
    }

    protected $_village_map = null;

    public function normalize_name($name)
    {
        $name = str_replace('台東', '臺東', $name);
        $name = str_replace('台西', '臺西', $name);
        $name = str_replace('舘', '館', $name);
        $name = str_replace('豐', '豊', $name);
        $name = str_replace('廓', '廍', $name);
        $name = str_replace('双', '雙', $name);
        $name = str_replace('脚', '腳', $name);
        $name = str_replace('楊梅鎮', '楊梅市', $name);
        $name = str_replace('磘', '窯', $name);
        $name = str_replace('糠', '槺', $name);
        $name = str_replace('雞', '鷄', $name);
        $name = str_replace('州', '洲', $name);
        $name = str_replace('濓', '濂', $name);
        $name = str_replace('銅境', '銅鏡', $name);
        $name = str_replace('南詋里', '南瑤里', $name);
        $name = str_replace('陜', '陝', $name);
        $name = str_replace('溝垻里', '溝埧里', $name);
        $name = str_replace('西岐里', '西歧里', $name);
        $name = str_replace('峯', '峰', $name);
        return $name;
    }

    public function findVillageID($name)
    {
        if (is_null($this->_village_map)) {
            $fp = fopen(__DIR__ . '/village-list.csv', 'r');
            $columns = fgetcsv($fp);

            $this->_village_map = array();

            while ($values = fgetcsv($fp)) {
                $this->_village_map[$this->normalize_name($values[1] . $values[3] . $values[5])] = array(
                    $values[2], $values[4], $values[6], $values[0],
                );
            }
            fclose($fp);
        }

        return $this->_village_map[$this->normalize_name($name)];
    }
}

$c = new Converter;

$c->main($_SERVER['argv'][1]);
