<?php
/*
Plugin Name: Progress Bar Plugin
Description: Plugin personnalisé pour gérer une barre de progression en fonction du sous-total dans WooCommerce.
Author: Troteseil Lucas
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit; // Empêche l'accès direct au fichier.
}

// Enqueue des styles CSS
function progress_bar_enqueue_styles() {
    wp_enqueue_style(
        'progress-bar-style',
        plugin_dir_url(__FILE__) . 'assets/css/progress-bar-style.css',
        array(),
        '1.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'progress_bar_enqueue_styles');

// Ajout d'une option pour le CSS personnalisé dans le Back Office
function progress_bar_add_custom_css_option() {
    add_options_page(
        'Progress Bar Settings',
        'Progress Bar',
        'manage_options',
        'progress-bar-plugin',
        'progress_bar_settings_page'
    );
}
add_action('admin_menu', 'progress_bar_add_custom_css_option');

function progress_bar_register_settings() {
    register_setting('progress-bar-settings', 'progress_bar_custom_css');
}
add_action('admin_init', 'progress_bar_register_settings');

// Formulaire pour insérer du CSS personnalisé dans le Back Office
function progress_bar_settings_page() {
    ?>
    <div class="wrap">
        <h1>Paramètres de la Progress Bar</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('progress-bar-settings');
            do_settings_sections('progress-bar-settings');
            ?>
            <textarea name="progress_bar_custom_css" rows="10" style="width:100%;"><?php echo esc_textarea(get_option('progress_bar_custom_css', '')); ?></textarea>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Ajout du CSS personnalisé dans le `<head>` du site
function progress_bar_custom_css() {
    $custom_css = get_option('progress_bar_custom_css', '');
    if (!empty($custom_css)) {
        echo '<style>' . esc_html($custom_css) . '</style>';
    }
}
add_action('wp_head', 'progress_bar_custom_css');

// Fonction de rendu de la barre de progression
function render_progress_bar($attributes, $content) {
    ob_start();
    ?>
    <script type="text/javascript">
        function convertCurrencyStringToFloat(currencyString) {
            let cleanedString = currencyString.replace('€', '').trim();
            cleanedString = cleanedString.replace(',', '.');
            return parseFloat(cleanedString);
        }

        document.addEventListener('DOMContentLoaded', function() {
            function updateProgressBar(subTotalText) {
                if (!subTotalText) return;
                let text = document.getElementById('text-indicator');
                let goal = 60;
                let bar_max = goal + 30;
                let subTotal = convertCurrencyStringToFloat(subTotalText);
                let progress = Math.min(100, (subTotal / bar_max) * 100);
                let progressBarFill = document.querySelector('.progress-bar-fill');

                if (subTotal < goal) {
                    let calcul = (goal - subTotal).toFixed(2);
                    text.innerHTML = `Encore <strong>${calcul} €</strong> avant la livraison gratuite !`;
                } else {
                    text.innerHTML = `La livraison est offerte !`;
                }

                if (progressBarFill && text) {
                    progressBarFill.style.width = progress + '%';
                }
            }

            // Exemple d'observateur (mini-panier WooCommerce)
            updateProgressBar('50'); // Remplacer par des valeurs dynamiques.
        });
    </script>

    <div class="pb-cont">
        <p id="text-indicator">Plus que <span class="progress-bar-text">0.00</span> avant une réduction.</p>
        <div class="pb">
            <div class="progress-bar-wrapper">
                <div class="progress-bar">
                    <div class="progress-bar-fill"></div>
                </div>
            </div>
            <div class="progress-bar-marker" style="left: 66.67%;"></div>
            <div class="progress-bar-marker-txt">Livraison gratuite</div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('progress_bar', 'render_progress_bar');
