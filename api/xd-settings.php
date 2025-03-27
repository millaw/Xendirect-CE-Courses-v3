<?php
declare(strict_types=1);

class XD_Settings {
    private array $options = [];

    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
    }

    public function add_plugin_page(): void {
        add_options_page(
            'XD CE Courses Settings',
            'XD CE Courses',
            'manage_options',
            'xd-ce-settings',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page(): void {
        $this->options = get_option('xd_ce_options', []); ?>
        <div class="wrap">
            <h1>XD CE Courses Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('xd_ce_option_group');
                do_settings_sections('xd-ce-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init(): void {
        register_setting(
            'xd_ce_option_group',
            'xd_ce_options',
            [$this, 'sanitize']
        );

        add_settings_section(
            'api_settings_section',
            'API Settings',
            [$this, 'print_section_info'],
            'xd-ce-settings'
        );

        $this->add_settings_field('api_key', 'API Key');
        $this->add_settings_field('organization_id', 'Organization ID');
        $this->add_settings_field('base_url', 'API Base URL');
    }

    private function add_settings_field(string $id, string $title): void {
        add_settings_field(
            $id,
            $title,
            [$this, "{$id}_callback"],
            'xd-ce-settings',
            'api_settings_section'
        );
    }

    public function sanitize(array $input): array {
        return [
            'api_key' => sanitize_text_field($input['api_key'] ?? ''),
            'organization_id' => sanitize_text_field($input['organization_id'] ?? ''),
            'base_url' => $this->sanitize_url($input['base_url'] ?? '')
        ];
    }

    private function sanitize_url(string $url): string {
        $url = esc_url_raw($url, ['https']);
        return $url ? rtrim($url, '/') . '/' : 'https://api.xendirect.com/v2/';
    }

    public function print_section_info(): void {
        echo '<p>Enter your XD API credentials below:</p>';
    }

    public function api_key_callback(): void {
        $this->render_input_field('api_key');
    }

    public function organization_id_callback(): void {
        $this->render_input_field('organization_id');
    }

    public function base_url_callback(): void {
        $this->render_input_field('base_url', 'url', 'https://api.xendirect.com/v2/');
        echo '<p class="description">Must include protocol and trailing slash</p>';
    }

    private function render_input_field(string $id, string $type = 'text', string $placeholder = ''): void {
        printf(
            '<input type="%s" id="%s" name="xd_ce_options[%s]" value="%s" class="regular-text" placeholder="%s" />',
            esc_attr($type),
            esc_attr($id),
            esc_attr($id),
            esc_attr($this->options[$id] ?? ''),
            esc_attr($placeholder)
        );
    }

    public static function uninstall(): void {
        delete_option('xd_ce_options');
        
        $posts = get_posts([
            'post_type' => 'post',
            'numberposts' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ]);
        
        foreach ($posts as $post_id) {
            delete_post_meta($post_id, '_xd_ce_subtitle');
        }
    }
}