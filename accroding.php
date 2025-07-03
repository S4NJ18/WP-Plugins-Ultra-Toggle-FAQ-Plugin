<?php
/*
Plugin Name: Ultra Toggle / FAQ Plugin
Description: A stylish, collapsible FAQ plugin with shortcode support, customizable style options (background, font size, alignment, tag), and a "Buy Me a Coffee" button. Use it like: [ultra_according title="Your Title" heading_tag="h2"]Your content[/ultra_according]
Version: 1.1
Author: Sanjib
*/

// Shortcode Function
function ultra_toggle_faq_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'title' => 'Click to expand',
        'heading_tag' => null
    ), $atts);

    $options = get_option('ultra_according_plugin_options');
    $bg = esc_attr($options['background_color'] ?? 'linear-gradient(90deg, #061B78 0%, #C20003 100%)');
    $font_size = esc_attr($options['font_size'] ?? '16');
    $align = esc_attr($options['text_align'] ?? 'left');

    // Allow heading_tag override from shortcode
    $tag = $atts['heading_tag'] ?? ($options['title_tag'] ?? 'h3');
    $tag = strtolower(trim($tag));
    $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
    if (!in_array($tag, $allowed_tags)) {
        $tag = 'h3';
    }

    $id = uniqid('ultra_faq_');

    ob_start();
    ?>
    <div class="ultra-faq-item">
        <<?php echo $tag; ?> class="ultra-faq-title" data-target="#<?php echo $id; ?>">
            <span class="arrow">▼</span>
            <span class="title-text"><?php echo esc_html($atts['title']); ?></span>
        </<?php echo $tag; ?>>
        <div class="ultra-faq-content" id="<?php echo $id; ?>">
            <?php echo wpautop(do_shortcode($content)); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('ultra_according', 'ultra_toggle_faq_shortcode');

// Frontend Styles and Script
function ultra_faq_plugin_scripts() {
    $options = get_option('ultra_according_plugin_options');
    $bg = esc_attr($options['background_color'] ?? 'linear-gradient(90deg, #061B78 0%, #C20003 100%)');
    $font_size = esc_attr($options['font_size'] ?? '16');
    $align = esc_attr($options['text_align'] ?? 'left');
    ?>
    <style>
        .ultra-faq-item {
            margin-bottom: 16px;
			border: 0px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            font-family: sans-serif;
        }

        .ultra-faq-title {
            background-image: <?php echo $bg; ?>;
            color: white;
            padding: 14px 18px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: <?php echo $align; ?>;
            font-size: <?php echo $font_size; ?>px;
            gap: 10px;
        }

        .ultra-faq-title .arrow {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .ultra-faq-title.active .arrow {
            transform: rotate(180deg);
        }

        .ultra-faq-content {
            display: none;
            padding: 14px 18px;
            background: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }

        .ultra-faq-coffee {
            background: #f1f1f1;
            text-align: right;
            padding: 8px 14px;
            font-size: 14px;
        }

        .paypal-button {
            background-color: #ffc439;
            color: #111;
            border: none;
            padding: 6px 14px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }

        .paypal-button:hover {
            background-color: #ffb347;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            $('.ultra-faq-title').on('click', function () {
                var target = $(this).data('target');
                $('.ultra-faq-content').not(target).slideUp();
                $('.ultra-faq-title').not(this).removeClass('active');
                $(target).stop(true, true).slideToggle();
                $(this).toggleClass('active');
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'ultra_faq_plugin_scripts');

// Admin Settings Page
function ultra_faq_plugin_menu() {
    add_options_page(
        'FAQ Plugin Settings',
        'ultra FAQ Plugin Settings',
        'manage_options',
        'ultra-faq-plugin-settings',
        'ultra_faq_plugin_settings_page'
    );
}
add_action('admin_menu', 'ultra_faq_plugin_menu');

function ultra_faq_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>ultra FAQ Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ultra_faq_plugin_group');
            do_settings_sections('ultra-faq-plugin-settings');
            submit_button();
            ?>
        </form>
        <hr>
        <h2>Support the Developer</h2>
        <p>If you find this plugin useful, consider donating 😊</p>
        <form action="https://www.paypal.com/donate" method="post" target="_blank">
            <input type="hidden" name="business" value="sanjib90511@gmail.com" />
            <input type="hidden" name="no_recurring" value="0" />
            <input type="hidden" name="currency_code" value="USD" />
            <input type="submit" value="Donate via PayPal" class="paypal-button" />
        </form>
        <style>
            .paypal-button {
                background-color: #ffc439;
                color: #111;
                border: none;
                padding: 8px 16px;
                font-size: 14px;
                font-weight: bold;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 8px;
            }

            .paypal-button:hover {
                background-color: #ffb347;
            }
        </style>
    </div>
    <?php
}

function ultra_faq_plugin_settings_init() {
    register_setting('ultra_faq_plugin_group', 'ultra_according_plugin_options');

    add_settings_section('ultra_faq_plugin_main', '', null, 'ultra-faq-plugin-settings');

    add_settings_field('background_color', 'Background Gradient CSS', 'ultra_faq_bg_field', 'ultra-faq-plugin-settings', 'ultra_faq_plugin_main');
    add_settings_field('font_size', 'Title Font Size (px)', 'ultra_faq_font_field', 'ultra-faq-plugin-settings', 'ultra_faq_plugin_main');
    add_settings_field('text_align', 'Title Text Align', 'ultra_faq_align_field', 'ultra-faq-plugin-settings', 'ultra_faq_plugin_main');
    add_settings_field('title_tag', 'Default Title Tag (e.g. h2, h3)', 'ultra_faq_tag_field', 'ultra-faq-plugin-settings', 'ultra_faq_plugin_main');
}
add_action('admin_init', 'ultra_faq_plugin_settings_init');

// Settings Fields
function ultra_faq_bg_field() {
    $options = get_option('ultra_according_plugin_options');
    echo '<input type="text" name="ultra_according_plugin_options[background_color]" value="' . esc_attr($options['background_color'] ?? 'linear-gradient(90deg, #061B78 0%, #C20003 100%)') . '" size="60" />';
}

function ultra_faq_font_field() {
    $options = get_option('ultra_according_plugin_options');
    echo '<input type="number" name="ultra_according_plugin_options[font_size]" value="' . esc_attr($options['font_size'] ?? '16') . '" />';
}

function ultra_faq_align_field() {
    $options = get_option('ultra_according_plugin_options');
    ?>
    <select name="ultra_according_plugin_options[text_align]">
        <option value="left" <?php selected($options['text_align'] ?? '', 'left'); ?>>Left</option>
        <option value="center" <?php selected($options['text_align'] ?? '', 'center'); ?>>Center</option>
        <option value="right" <?php selected($options['text_align'] ?? '', 'right'); ?>>Right</option>
    </select>
    <?php
}

function ultra_faq_tag_field() {
    $options = get_option('ultra_according_plugin_options');
    ?>
    <select name="ultra_according_plugin_options[title_tag]">
        <option value="h2" <?php selected($options['title_tag'] ?? '', 'h2'); ?>>H2</option>
        <option value="h3" <?php selected($options['title_tag'] ?? '', 'h3'); ?>>H3</option>
        <option value="h4" <?php selected($options['title_tag'] ?? '', 'h4'); ?>>H4</option>
        <option value="span" <?php selected($options['title_tag'] ?? '', 'span'); ?>>Span</option>
        <option value="p" <?php selected($options['title_tag'] ?? '', 'p'); ?>>Paragraph (p)</option>
    </select>
    <?php
}
