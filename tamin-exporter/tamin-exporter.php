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

function tamin_exporter_get_dsk_labels() {
    return [
        1 => 'کد کارگاه',
        2 => 'نام کارگاه',
        3 => 'کد شعبه',
        4 => 'شماره لیست',
        5 => 'نام کارفرما',
        6 => 'نام خانوادگی کارفرما',
        7 => 'نام پدر',
        8 => 'کد پستی',
        9 => 'نشانی کارگاه',
        10 => 'سال',
        11 => 'ماه',
        12 => 'تعداد کارگران',
        13 => 'تعداد روزکرد',
        14 => 'مزد/حقوق',
        15 => 'مزایا',
        16 => 'جمع مزد و مزایا',
        17 => 'حق بیمه سهم کارگر',
        18 => 'حق بیمه سهم کارفرما',
        19 => 'بیمه بیکاری',
        20 => 'جمع حق بیمه',
        21 => 'مبلغ پرداختی',
        22 => 'مابه‌التفاوت',
        23 => 'کارکرد',
        24 => 'سایر توضیحات',
    ];
}

function tamin_exporter_get_dsw_labels() {
    return [
        1 => 'کد کارگاه',
        2 => 'سال',
        3 => 'ماه',
        4 => 'شماره لیست',
        5 => 'شماره بیمه',
        6 => 'نام',
        7 => 'نام خانوادگی',
        8 => 'نام پدر',
        9 => 'کد ملی',
        10 => 'کد شغل',
        11 => 'نوع رابطه',
        12 => 'جنسیت',
        13 => 'تابعیت',
        14 => 'وضعیت',
        15 => 'تاریخ شروع',
        16 => 'تاریخ ترک کار',
        17 => 'روزکرد',
        18 => 'حقوق',
        19 => 'مزایا',
        20 => 'جمع حقوق و مزایا',
        21 => 'حق بیمه سهم کارگر',
        22 => 'حق بیمه سهم کارفرما',
        23 => 'بیمه بیکاری',
        24 => 'جمع حق بیمه',
        25 => 'کد شعبه',
        26 => 'تاریخ تولد',
        27 => 'شماره شناسنامه',
        28 => 'مزد ماهانه',
        29 => 'سایر توضیحات',
    ];
}

function tamin_exporter_render_shortcode() {
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
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tamin-exporter-form">
        <input type="hidden" name="action" value="tamin_export_download" />
        <h3>اطلاعات کارگاه</h3>
        <div class="tamin-exporter-section">
            <?php $dsk_labels = tamin_exporter_get_dsk_labels(); ?>
            <?php for ($i = 1; $i <= 24; $i++) : ?>
                <div class="tamin-exporter-field">
                    <label for="dsk_col<?php echo esc_attr($i); ?>">
                        <?php echo esc_html($dsk_labels[$i]); ?>
                        <span class="tamin-exporter-col-code" aria-hidden="true">(dsk_col<?php echo esc_html($i); ?>)</span>
                    </label>
                    <input type="text" id="dsk_col<?php echo esc_attr($i); ?>" name="dsk_col<?php echo esc_attr($i); ?>" value="<?php echo esc_attr($dsk_cols[$i]); ?>" />
                </div>
            <?php endfor; ?>
        </div>

        <h3>اطلاعات کارگران</h3>
        <?php $dsw_labels = tamin_exporter_get_dsw_labels(); ?>
        <table class="tamin-exporter-workers" data-template>
            <thead>
                <tr>
                    <?php for ($i = 1; $i <= 29; $i++) : ?>
                        <th>
                            <?php echo esc_html($dsw_labels[$i]); ?>
                            <span class="tamin-exporter-col-code" aria-hidden="true">(dsw_col<?php echo esc_html($i); ?>)</span>
                        </th>
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
        <div class="tamin-exporter-advanced">
            <label>
                <input type="checkbox" class="tamin-exporter-toggle" />
                نمایش حالت پیشرفته (نمایش کد ستون‌ها)
            </label>
        </div>

        <?php wp_nonce_field('tamin_export', 'tamin_export_nonce'); ?>
        <button type="submit" name="tamin_export_submit" value="1">دانلود خروجی تامین اجتماعی</button>
    </form>
    <?php
    return ob_get_clean();
}

function tamin_exporter_handle_download() {
    if (empty($_POST['tamin_export_nonce'])) {
        return;
    }

    $nonce = wp_unslash($_POST['tamin_export_nonce']);
    if (!wp_verify_nonce($nonce, 'tamin_export')) {
        return;
    }

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

    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_name . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($tmp);
    unlink($tmp);
    exit;
}

add_shortcode('tamin_export', 'tamin_exporter_render_shortcode');
add_action('admin_post_tamin_export_download', 'tamin_exporter_handle_download');
add_action('admin_post_nopriv_tamin_export_download', 'tamin_exporter_handle_download');
