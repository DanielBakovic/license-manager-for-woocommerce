<?php defined('ABSPATH') || exit; ?>

<h1 class="lmfwc-header"><?php esc_html_e('Please select an option', 'lmfwc'); ?></h1>

<div class="lmfwc-add-licenses-wrapper">
    <div class="lmfwc-add-licenses-container">
        <div
            class="lmfwc-card"
            data-order="1"
            style="
                -webkit-transform: translate(0%, 0%);
                   -moz-transform: translate(0%, 0%);
                    -ms-transform: translate(0%, 0%);
                     -o-transform: translate(0%, 0%);
                        transform: translate(0%, 0%);
            ">
            <h2><?php echo esc_html_e('Add a single text license'); ?></h2>
            <span class="dashicons dashicons-editor-textcolor"></span>
            <p>Lorem ipsum dolor sit amet. Add some keys.</p>
            <div class="lmfwc-card-content">
                <?php include_once(LMFWC_TEMPLATES_DIR . 'licenses/forms/single-text.php'); ?>
            </div>
            <button class="button button-secondary"><?php esc_html_e('Continue', 'lmfwc'); ?></button>
        </div>
        <div
            class="lmfwc-card"
            data-order="2"
            style="
                -webkit-transform: translate(calc(100% + 1em), 0%);
                   -moz-transform: translate(calc(100% + 1em), 0%);
                    -ms-transform: translate(calc(100% + 1em), 0%);
                     -o-transform: translate(calc(100% + 1em), 0%);
                        transform: translate(calc(100% + 1em), 0%);
            ">
            <h2>Add text licenses in bulk</h2>
            <span class="dashicons dashicons-media-text"></span>
            <p>Upload a .txt or .csv file containing one license per row.</p>
            <div class="lmfwc-card-content">
                <?php include_once(LMFWC_TEMPLATES_DIR . 'licenses/forms/bulk-text.php'); ?>
            </div>
            <button class="button button-secondary"><?php esc_html_e('Continue', 'lmfwc'); ?></button>
        </div>
    </div>
</div>