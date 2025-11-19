<?php
/*
 * Plugin Name: Inbio Hire Me Popup
 * Description: Adds a draggable “Hire Me” popup like the one seen on the Inbio demo.  The popup automatically appears after a short delay on the front‑end of your site and can be dragged anywhere on the screen.  A close button is provided to dismiss it.  You can replace the avatar image in <code>assets/img/avatar.png</code> with your own photo.
 * Version: 1.0.0
 * Author: ChatGPT
 * License: GPL2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Inbio_Hire_Me_Popup {

    /**
     * Constructor hooks into WordPress.
     */
    public function __construct() {
        // Enqueue styles and scripts on the front end only.
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Render the popup just before the closing body tag.
        add_action( 'wp_footer', [ $this, 'render_popup' ] );

        /*
         * Back‑end integrations: add our settings page, register settings and
         * enqueue the media uploader on the admin side.  The WordPress
         * Settings API requires calls to register_setting(),
         * add_settings_section() and add_settings_field()【689875329939214†L88-L116】.
         */
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Enqueue CSS and JavaScript files.
     */
    public function enqueue_assets() {
        // Only enqueue on the front end and not in the admin area.
        if ( is_admin() ) {
            return;
        }

        $plugin_url = plugin_dir_url( __FILE__ );

        // Register and enqueue the stylesheet.
        wp_register_style( 'inbio-hire-popup-style', $plugin_url . 'assets/css/popup-style.css', [], '1.0.0' );
        wp_enqueue_style( 'inbio-hire-popup-style' );

        // Register and enqueue the script.  Depend on jQuery for convenience but could be plain JS.
        wp_register_script( 'inbio-hire-popup-script', $plugin_url . 'assets/js/popup-script.js', [ 'jquery' ], '1.0.0', true );
        wp_enqueue_script( 'inbio-hire-popup-script' );
    }

    /**
     * Output the markup for the popup.  This uses minimal markup and classes for styling.
     */
    public function render_popup() {
        // Allow theme authors to disable the popup via a filter.
        if ( ! apply_filters( 'inbio_hire_popup_display', true ) ) {
            return;
        }
        // Fetch saved options (if any).
        $options = get_option( 'inbio_hire_popup_options', [] );
        $pic_id     = isset( $options['profile_pic'] ) ? absint( $options['profile_pic'] ) : 0;
        $title      = isset( $options['title_text'] ) ? $options['title_text'] : '';
        $subtitle   = isset( $options['subtitle_text'] ) ? $options['subtitle_text'] : '';
        $button_txt = isset( $options['button_text'] ) ? $options['button_text'] : '';
        $button_url = isset( $options['button_link'] ) ? $options['button_link'] : '';

        // Determine avatar URL: fallback to default image in assets if no ID saved.
        if ( $pic_id ) {
            $avatar_url = wp_get_attachment_url( $pic_id );
        }
        if ( empty( $avatar_url ) ) {
            $avatar_url = plugins_url( 'assets/img/avatar.png', __FILE__ );
        }

        // Provide default text if options not set.
        if ( ! $title ) {
            $title = __( "I'm Inbio is available for hire", 'inbio-hire-popup' );
        }
        if ( ! $subtitle ) {
            $subtitle = __( 'Availability: Maximum: 2 Hours', 'inbio-hire-popup' );
        }
        if ( ! $button_txt ) {
            $button_txt = __( 'Hire me', 'inbio-hire-popup' );
        }
        if ( ! $button_url ) {
            $button_url = '#';
        }
        // Build gradient style if colours are set.
        $bg_start = isset( $options['bg_color_start'] ) ? $options['bg_color_start'] : '';
        $bg_end   = isset( $options['bg_color_end'] ) ? $options['bg_color_end'] : '';
        $gradient_style = '';
        if ( $bg_start || $bg_end ) {
            // If one is empty, duplicate the other so gradient still works.
            $start = $bg_start ? $bg_start : $bg_end;
            $end   = $bg_end   ? $bg_end   : $bg_start;
            $gradient_style = 'background: linear-gradient(135deg, ' . $start . ', ' . $end . ');';
        }
        ?>
        <div id="inbio-hire-popup" class="inbio-hire-popup" role="dialog" aria-live="polite" aria-label="Hire me popup" style="display:none;<?php echo esc_attr( $gradient_style ); ?>">
            <button type="button" class="inbio-popup-close" aria-label="Close popup">&times;</button>
            <div class="inbio-popup-inner">
                <div class="inbio-popup-avatar">
                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="Avatar" width="60" height="60">
                </div>
                <div class="inbio-popup-text">
                    <h3 class="inbio-popup-title"><?php echo esc_html( $title ); ?></h3>
                    <p class="inbio-popup-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                </div>
                <div class="inbio-popup-action">
                    <a href="<?php echo esc_url( $button_url ); ?>" class="inbio-popup-button"><?php echo esc_html( $button_txt ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Register our settings and fields.
     *
     * Uses the Settings API functions register_setting(), add_settings_section() and
     * add_settings_field() to build a settings form【689875329939214†L88-L116】.
     */
    public function register_settings() {
        // Register our setting group and option name.
        register_setting( 'inbio_hire_popup', 'inbio_hire_popup_options', [ $this, 'sanitize_settings' ] );

        // Add a section to the settings page.
        add_settings_section(
            'inbio_hire_popup_section',
            __( 'Hire Popup Content', 'inbio-hire-popup' ),
            [ $this, 'section_info' ],
            'inbio-hire-popup'
        );

        // Profile picture field.
        add_settings_field(
            'profile_pic',
            __( 'Profile Picture', 'inbio-hire-popup' ),
            [ $this, 'field_profile_pic' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );

        // Title field.
        add_settings_field(
            'title_text',
            __( 'Title', 'inbio-hire-popup' ),
            [ $this, 'field_title' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );

        // Subtitle field.
        add_settings_field(
            'subtitle_text',
            __( 'Subtitle', 'inbio-hire-popup' ),
            [ $this, 'field_subtitle' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );

        // Button text field.
        add_settings_field(
            'button_text',
            __( 'Button Text', 'inbio-hire-popup' ),
            [ $this, 'field_button_text' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );

        // Button link field.
        add_settings_field(
            'button_link',
            __( 'Button Link', 'inbio-hire-popup' ),
            [ $this, 'field_button_link' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );

        // Gradient start color field.
        add_settings_field(
            'bg_color_start',
            __( 'Gradient Start Color', 'inbio-hire-popup' ),
            [ $this, 'field_bg_color_start' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );

        // Gradient end color field.
        add_settings_field(
            'bg_color_end',
            __( 'Gradient End Color', 'inbio-hire-popup' ),
            [ $this, 'field_bg_color_end' ],
            'inbio-hire-popup',
            'inbio_hire_popup_section'
        );
    }

    /**
     * Adds the settings page to the WordPress admin menu.
     */
    public function add_settings_page() {
        add_menu_page(
            __( 'Hire Popup Settings', 'inbio-hire-popup' ),
            __( 'Popup Settings', 'inbio-hire-popup' ),
            'manage_options',
            'inbio-hire-popup',
            [ $this, 'settings_page_html' ],
            'dashicons-admin-generic',
            99
        );
    }

    /**
     * Enqueue scripts for the admin settings page. This includes the WordPress
     * media uploader (wp_enqueue_media) which loads scripts and data needed to
     * open the media frame【958700085981395†L70-L147】.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only enqueue on our plugin page.
        if ( 'toplevel_page_inbio-hire-popup' !== $hook ) {
            return;
        }
        // Load the WordPress media uploader scripts.
        wp_enqueue_media();
        // Register our admin script to handle image selection.
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_script(
            'inbio-hire-popup-admin',
            $plugin_url . 'assets/js/admin-settings.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );
    }

    /**
     * Sanitize incoming option values before saving.
     *
     * @param array $input Raw input from the form.
     * @return array Sanitized input.
     */
    public function sanitize_settings( $input ) {
        $new_input = [];
        if ( isset( $input['profile_pic'] ) ) {
            $new_input['profile_pic'] = absint( $input['profile_pic'] );
        }
        if ( isset( $input['title_text'] ) ) {
            $new_input['title_text'] = sanitize_text_field( $input['title_text'] );
        }
        if ( isset( $input['subtitle_text'] ) ) {
            $new_input['subtitle_text'] = sanitize_textarea_field( $input['subtitle_text'] );
        }
        if ( isset( $input['button_text'] ) ) {
            $new_input['button_text'] = sanitize_text_field( $input['button_text'] );
        }
        if ( isset( $input['button_link'] ) ) {
            $new_input['button_link'] = esc_url_raw( $input['button_link'] );
        }

        if ( isset( $input['bg_color_start'] ) ) {
            // Accept only valid hex colors; fall back to empty string if invalid.
            $color = sanitize_hex_color( $input['bg_color_start'] );
            $new_input['bg_color_start'] = $color ? $color : '';
        }
        if ( isset( $input['bg_color_end'] ) ) {
            $color = sanitize_hex_color( $input['bg_color_end'] );
            $new_input['bg_color_end'] = $color ? $color : '';
        }
        return $new_input;
    }

    /**
     * Print information about the section.
     */
    public function section_info() {
        echo '<p>' . esc_html__( 'Configure the content and appearance of your hire popup.', 'inbio-hire-popup' ) . '</p>';
    }

    /**
     * Field callback for profile picture upload.
     */
    public function field_profile_pic() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $attachment_id = isset( $options['profile_pic'] ) ? absint( $options['profile_pic'] ) : 0;
        $image_url    = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
        ?>
        <div class="inbio-admin-image-preview" style="margin-bottom:10px;">
            <?php if ( $image_url ) : ?>
                <img id="inbio_profile_preview" src="<?php echo esc_url( $image_url ); ?>" style="max-width:80px;" />
            <?php else : ?>
                <img id="inbio_profile_preview" src="" style="max-width:80px; display:none;" />
            <?php endif; ?>
        </div>
        <input type="hidden" id="inbio_profile_pic" name="inbio_hire_popup_options[profile_pic]" value="<?php echo esc_attr( $attachment_id ); ?>" />
        <button type="button" class="button" id="inbio_profile_upload_btn"><?php esc_html_e( 'Upload Image', 'inbio-hire-popup' ); ?></button>
        <button type="button" class="button" id="inbio_profile_remove_btn" style="<?php echo $attachment_id ? '' : 'display:none;'; ?> margin-left:5px;"><?php esc_html_e( 'Remove', 'inbio-hire-popup' ); ?></button>
        <?php
    }

    /**
     * Field callback for title text.
     */
    public function field_title() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $val = isset( $options['title_text'] ) ? $options['title_text'] : '';
        printf(
            '<input type="text" name="inbio_hire_popup_options[title_text]" value="%s" class="regular-text" />',
            esc_attr( $val )
        );
    }

    /**
     * Field callback for subtitle.
     */
    public function field_subtitle() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $val = isset( $options['subtitle_text'] ) ? $options['subtitle_text'] : '';
        printf(
            '<textarea name="inbio_hire_popup_options[subtitle_text]" rows="3" cols="50" class="large-text">%s</textarea>',
            esc_textarea( $val )
        );
    }

    /**
     * Field callback for button text.
     */
    public function field_button_text() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $val = isset( $options['button_text'] ) ? $options['button_text'] : '';
        printf(
            '<input type="text" name="inbio_hire_popup_options[button_text]" value="%s" class="regular-text" />',
            esc_attr( $val )
        );
    }

    /**
     * Field callback for button link.
     */
    public function field_button_link() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $val = isset( $options['button_link'] ) ? $options['button_link'] : '';
        printf(
            '<input type="url" name="inbio_hire_popup_options[button_link]" value="%s" class="regular-text" />',
            esc_attr( $val )
        );
    }

    /**
     * Field callback for gradient start color. Uses a color input.
     */
    public function field_bg_color_start() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $val = isset( $options['bg_color_start'] ) ? $options['bg_color_start'] : '';
        if ( ! $val ) {
            // Default to the original gradient's first colour.
            $val = '#4b0082';
        }
        printf(
            '<input type="text" name="inbio_hire_popup_options[bg_color_start]" value="%s" class="regular-text" placeholder="#4b0082" />',
            esc_attr( $val )
        );
    }

    /**
     * Field callback for gradient end color.
     */
    public function field_bg_color_end() {
        $options = get_option( 'inbio_hire_popup_options', [] );
        $val = isset( $options['bg_color_end'] ) ? $options['bg_color_end'] : '';
        if ( ! $val ) {
            // Default to the last colour of the original gradient.
            $val = '#e11d74';
        }
        printf(
            '<input type="text" name="inbio_hire_popup_options[bg_color_end]" value="%s" class="regular-text" placeholder="#e11d74" />',
            esc_attr( $val )
        );
    }

    /**
     * Output the settings page HTML.
     */
    public function settings_page_html() {
        // Check capability.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Hire Popup Settings', 'inbio-hire-popup' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'inbio_hire_popup' );
                do_settings_sections( 'inbio-hire-popup' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

// Initialise the plugin.
new Inbio_Hire_Me_Popup();