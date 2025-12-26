<?php
/**
 * Plugin Name: Tamin Exporter
 * Description: Export DSKKAR00.dbf and DSKWOR00.dbf files for Tamin social security.
 * Version: 1.0.0
 * Author: OpenAI
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/exporter.php';

function tamin_exporter_render_shortcode() {
    if (!empty($_POST['tamin_export_submit']) && !empty($_POST['tamin_export_nonce'])) {
        $nonce = wp_unslash($_POST['tamin_export_nonce']);
        if (wp_verify_nonce($nonce, 'tamin_export')) {
            tamin_exporter_handle_download();
        }
    }

    wp_enqueue_script(
        'tamin-exporter-form',
        plugins_url('assets/form.js', __FILE__),
        [],
        '1.0.0',
        true
    );

    $dsk_cols = [];
    for ($i = 1; $i <= 24; $i++) {
        $key = 'dsk_col' . $i;
        $dsk_cols[$i] = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
    }

    $workers = [];
    $worker_count = 0;
    if (!empty($_POST['dsw_col1']) && is_array($_POST['dsw_col1'])) {
        $worker_count = count($_POST['dsw_col1']);
        for ($row = 0; $row < $worker_count; $row++) {
            $workers[$row] = [];
            for ($col = 1; $col <= 29; $col++) {
                $key = 'dsw_col' . $col;
                $workers[$row][$col] = isset($_POST[$key][$row]) ? wp_unslash($_POST[$key][$row]) : '';
            }
        }
    }

    if ($worker_count === 0) {
        $workers[] = array_fill(1, 29, '');
    }

    ob_start();
    ?>
    <form method="post" class="tamin-exporter-form">
        <h3>اطلاعات کارگاه</h3>
        <div class="tamin-exporter-section">
            <?php for ($i = 1; $i <= 24; $i++) : ?>
                <div class="tamin-exporter-field">
                    <label for="dsk_col<?php echo esc_attr($i); ?>">dsk_col<?php echo esc_html($i); ?></label>
                    <input type="text" id="dsk_col<?php echo esc_attr($i); ?>" name="dsk_col<?php echo esc_attr($i); ?>" value="<?php echo esc_attr($dsk_cols[$i]); ?>" />
                </div>
            <?php endfor; ?>
        </div>

        <h3>اطلاعات کارگران</h3>
        <table class="tamin-exporter-workers" data-template>
            <thead>
                <tr>
                    <?php for ($i = 1; $i <= 29; $i++) : ?>
                        <th>dsw_col<?php echo esc_html($i); ?></th>
                    <?php endfor; ?>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workers as $row_index => $row) : ?>
                    <tr class="tamin-exporter-row">
                        <?php for ($i = 1; $i <= 29; $i++) : ?>
                            <td>
                                <input type="text" name="dsw_col<?php echo esc_attr($i); ?>[]" value="<?php echo esc_attr($row[$i]); ?>" />
                            </td>
                        <?php endfor; ?>
                        <td>
                            <button type="button" class="tamin-exporter-remove">حذف</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" class="tamin-exporter-add">افزودن کارگر</button>

        <?php wp_nonce_field('tamin_export', 'tamin_export_nonce'); ?>
        <button type="submit" name="tamin_export_submit" value="1">دانلود خروجی تامین اجتماعی</button>
    </form>
    <?php
    return ob_get_clean();
}

function tamin_exporter_handle_download() {
    $dsk_cols = [];
    for ($i = 1; $i <= 24; $i++) {
        $key = 'dsk_col' . $i;
        $dsk_cols[$i] = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
    }

    $workers = [];
    if (!empty($_POST['dsw_col1']) && is_array($_POST['dsw_col1'])) {
        $worker_count = count($_POST['dsw_col1']);
        for ($row = 0; $row < $worker_count; $row++) {
            $workers[$row] = [];
            for ($col = 1; $col <= 29; $col++) {
                $key = 'dsw_col' . $col;
                $workers[$row][$col] = isset($_POST[$key][$row]) ? wp_unslash($_POST[$key][$row]) : '';
            }
        }
    }

    $outputs = Tamin_Exporter::build_outputs($dsk_cols, $workers);
    $zip = new ZipArchive();
    $zip_name = 'tamin-output-' . gmdate('Ymd-His') . '.zip';
    $tmp = tempnam(sys_get_temp_dir(), 'tamin');
    if ($tmp === false) {
        return;
    }

    if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        unlink($tmp);
        return;
    }

    foreach ($outputs as $filename => $content) {
        $zip->addFromString($filename, $content);
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename=' . $zip_name);
    header('Content-Length: ' . filesize($tmp));
    readfile($tmp);
    unlink($tmp);
    exit;
}

add_shortcode('tamin_export', 'tamin_exporter_render_shortcode');
