<?php
require_once __DIR__ . '/../tamin-exporter/includes/exporter.php';

function unpad_value($value, array $config) {
    $lead = isset($config['lead']) ? (int) $config['lead'] : 0;
    $trail = isset($config['trail']) ? (int) $config['trail'] : 0;
    $force_space = !empty($config['force_space_if_empty']);

    for ($i = 0; $i < $lead; $i++) {
        if ($value !== '' && $value[0] === ' ') {
            $value = substr($value, 1);
        }
    }

    for ($i = 0; $i < $trail; $i++) {
        $len = strlen($value);
        if ($len > 0 && $value[$len - 1] === ' ') {
            $value = substr($value, 0, $len - 1);
        }
    }

    if ($force_space && $value === ' ') {
        return '';
    }

    return $value;
}

function parse_columns($line, array $padding) {
    $columns = explode(',', $line);
    $values = [];
    foreach ($columns as $index => $value) {
        $col_index = $index + 1;
        $values[$col_index] = unpad_value($value, $padding[$col_index]);
    }
    return $values;
}

$samples_dir = __DIR__ . '/../samples';
$sample_dskkar = file_get_contents($samples_dir . '/DSKKAR00.dbf');
$sample_dskwor = file_get_contents($samples_dir . '/DSKWOR00.dbf');

if (function_exists('iconv')) {
    $dskkar_text = iconv('CP1256', 'UTF-8', $sample_dskkar);
    $dskwor_text = iconv('CP1256', 'UTF-8', $sample_dskwor);
} else {
    $dskkar_text = mb_convert_encoding($sample_dskkar, 'UTF-8', 'CP1256');
    $dskwor_text = mb_convert_encoding($sample_dskwor, 'UTF-8', 'CP1256');
}

$dskkar_line = rtrim($dskkar_text, "\r\n");
$dskkar_values = parse_columns($dskkar_line, Tamin_Exporter::get_dskkar_padding());

$dskwor_lines = array_filter(explode("\r\n", $dskwor_text), 'strlen');
$worker_rows = [];
foreach ($dskwor_lines as $line) {
    $worker_rows[] = parse_columns($line, Tamin_Exporter::get_dskwor_padding());
}

$outputs = Tamin_Exporter::build_outputs($dskkar_values, $worker_rows);

$checks = [
    'DSKKAR00.dbf' => $sample_dskkar,
    'DSKWOR00.dbf' => $sample_dskwor,
];

$failed = false;
foreach ($checks as $filename => $expected) {
    $actual = $outputs[$filename];
    $expected_md5 = md5($expected);
    $actual_md5 = md5($actual);
    if ($expected_md5 !== $actual_md5) {
        echo $filename . " mismatch\n";
        echo "Expected: " . $expected_md5 . "\n";
        echo "Actual:   " . $actual_md5 . "\n";
        $failed = true;
    } else {
        echo $filename . " OK\n";
    }
}

exit($failed ? 1 : 0);
