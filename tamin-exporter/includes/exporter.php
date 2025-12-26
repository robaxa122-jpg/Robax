<?php

class Tamin_Exporter {
    public static function get_dskkar_padding() {
        return [
            1 => ['lead' => 0, 'trail' => 0],
            2 => ['lead' => 0, 'trail' => 1],
            3 => ['lead' => 0, 'trail' => 0],
            4 => ['lead' => 0, 'trail' => 0],
            5 => ['lead' => 1, 'trail' => 1],
            6 => ['lead' => 0, 'trail' => 0],
            7 => ['lead' => 0, 'trail' => 1],
            8 => ['lead' => 0, 'trail' => 1],
            9 => ['lead' => 1, 'trail' => 2],
            10 => ['lead' => 1, 'trail' => 1],
            11 => ['lead' => 1, 'trail' => 1],
            12 => ['lead' => 1, 'trail' => 1],
            13 => ['lead' => 1, 'trail' => 1],
            14 => ['lead' => 1, 'trail' => 1],
            15 => ['lead' => 1, 'trail' => 1],
            16 => ['lead' => 1, 'trail' => 1],
            17 => ['lead' => 1, 'trail' => 1],
            18 => ['lead' => 1, 'trail' => 2],
            19 => ['lead' => 1, 'trail' => 1],
            20 => ['lead' => 1, 'trail' => 1],
            21 => ['lead' => 1, 'trail' => 1],
            22 => ['lead' => 1, 'trail' => 1],
            23 => ['lead' => 1, 'trail' => 1],
            24 => ['lead' => 1, 'trail' => 1],
        ];
    }

    public static function get_dskwor_padding() {
        return [
            1 => ['lead' => 0, 'trail' => 0],
            2 => ['lead' => 0, 'trail' => 0],
            3 => ['lead' => 0, 'trail' => 0],
            4 => ['lead' => 0, 'trail' => 0],
            5 => ['lead' => 0, 'trail' => 1],
            6 => ['lead' => 0, 'trail' => 0],
            7 => ['lead' => 0, 'trail' => 0],
            8 => ['lead' => 0, 'trail' => 0],
            9 => ['lead' => 0, 'trail' => 1],
            10 => ['lead' => 0, 'trail' => 0],
            11 => ['lead' => 1, 'trail' => 1],
            12 => ['lead' => 0, 'trail' => 1],
            13 => ['lead' => 0, 'trail' => 1],
            14 => ['lead' => 0, 'trail' => 1],
            15 => ['lead' => 0, 'trail' => 1],
            16 => ['lead' => 0, 'trail' => 1],
            17 => ['lead' => 1, 'trail' => 2],
            18 => ['lead' => 1, 'trail' => 2],
            19 => ['lead' => 1, 'trail' => 2],
            20 => ['lead' => 1, 'trail' => 2],
            21 => ['lead' => 1, 'trail' => 2],
            22 => ['lead' => 1, 'trail' => 2],
            23 => ['lead' => 1, 'trail' => 2],
            24 => ['lead' => 1, 'trail' => 1],
            25 => ['lead' => 0, 'trail' => 0],
            26 => ['lead' => 0, 'trail' => 1],
            27 => ['lead' => 0, 'trail' => 1],
            28 => ['lead' => 1, 'trail' => 2],
            29 => ['lead' => 1, 'trail' => 1],
        ];
    }

    public static function build_dskkar_line(array $cols) {
        $padding = self::get_dskkar_padding();
        $values = [];
        for ($i = 1; $i <= 24; $i++) {
            $value = array_key_exists($i, $cols) ? $cols[$i] : '';
            $values[] = self::apply_padding($value, $padding[$i]);
        }
        return implode(',', $values) . "\r\n";
    }

    public static function build_dskwor_lines(array $rows) {
        $padding = self::get_dskwor_padding();
        $lines = [];
        foreach ($rows as $row) {
            $has_value = false;
            $values = [];
            for ($i = 1; $i <= 29; $i++) {
                $value = array_key_exists($i, $row) ? $row[$i] : '';
                if ($value !== '') {
                    $has_value = true;
                }
                $values[] = self::apply_padding($value, $padding[$i]);
            }
            if ($has_value) {
                $lines[] = implode(',', $values);
            }
        }
        if (!$lines) {
            return '';
        }
        return implode("\r\n", $lines) . "\r\n";
    }

    private static function apply_padding($value, array $config) {
        $lead = isset($config['lead']) ? (int) $config['lead'] : 0;
        $trail = isset($config['trail']) ? (int) $config['trail'] : 0;
        $force_space = !empty($config['force_space_if_empty']);
        if ($force_space && $value === '') {
            $value = ' ';
        }
        return str_repeat(' ', $lead) . $value . str_repeat(' ', $trail);
    }

    public static function encode_cp1256($content) {
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'CP1256//TRANSLIT', $content);
            if ($converted !== false) {
                return $converted;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($content, 'Windows-1256', 'UTF-8');
        }
        return $content;
    }

    public static function build_outputs(array $dsk_cols, array $worker_rows) {
        $dskkar_line = self::build_dskkar_line($dsk_cols);
        $dskwor_lines = self::build_dskwor_lines($worker_rows);
        return [
            'DSKKAR00.dbf' => self::encode_cp1256($dskkar_line),
            'DSKWOR00.dbf' => self::encode_cp1256($dskwor_lines),
        ];
    }
}
