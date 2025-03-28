<?php
/**
 * XD CE Courses
 *
 * @package       XDCECOURSE
 * @author        Milla Wynn
 * @license       gplv3
 * @version       3.1.0
 *
 * @wordpress-plugin
 * Plugin Name:   XD CE Courses v.3
 * Plugin URI:    https://github.com/millaw/xd-ce-courses-v3
 * Description:   This plugin integrates with the Xenegrade/Xendirect API to display continuing education course offerings, specifically tailored for CUNY colleges and other educational institutions utilizing their platform. Use a Subtitle as an KEY to display the course. Use shortcode [ce_courses] to display courses.
 * Version:       3.1.0
 * Author:        Milla Wynn
 * Author URI:    https://github.com/millaw
 * Text Domain:   xd-ce-courses
 * Domain Path:   /languages
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with XD CE Courses. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

/**
 * XD CE Courses - PHP 8+ Optimized
 */
declare(strict_types=1);

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('XDCECOURSE_NAME',        'XD CE Courses v.3');
define('XDCECOURSE_VERSION',     '3.0.0');
define('XDCECOURSE_PLUGIN_FILE', __FILE__);
define('XDCECOURSE_PLUGIN_BASE', plugin_basename(XDCECOURSE_PLUGIN_FILE));
define('XDCECOURSE_PLUGIN_DIR',  plugin_dir_path(XDCECOURSE_PLUGIN_FILE));
define('XDCECOURSE_PLUGIN_URL',  plugin_dir_url(XDCECOURSE_PLUGIN_FILE));

// Shortcode with return type declaration
function xd_ce_courses_shortcode(array $atts = []): string {
    ob_start();
    include XDCECOURSE_PLUGIN_DIR . 'pages/ce-courses.php';
    return (string) ob_get_clean();
}
add_shortcode('ce_courses', 'xd_ce_courses_shortcode');

// Meta box functions with type hints
function xd_ce_subtitle_meta_box(): void {
    add_meta_box(
        'xd_ce_subtitle',
        'Post Subtitle (Program Name)',
        'xd_ce_subtitle_callback',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'xd_ce_subtitle_meta_box');

function xd_ce_move_meta_box() {
    global $wp_meta_boxes;
    
    // Get our meta box
    $subtitle_box = $wp_meta_boxes['post']['side']['default']['xd_ce_subtitle'];
    
    // Remove it temporarily
    unset($wp_meta_boxes['post']['side']['default']['xd_ce_subtitle']);
    
    // Re-add it after categories (priority 40)
    add_meta_box(
        'xd_ce_subtitle',
        $subtitle_box['title'],
        $subtitle_box['callback'],
        'post',
        'side',
        'default',
        $subtitle_box['args']
    );
}
add_action('add_meta_boxes', 'xd_ce_move_meta_box', 99);

// Meta Box Callback
function xd_ce_subtitle_callback(WP_Post $post): void {
    $subtitle = get_post_meta($post->ID, '_xd_ce_subtitle', true);
    wp_nonce_field('xd_ce_save_subtitle', 'xd_ce_subtitle_nonce');
    
    echo '<label for="xd_ce_subtitle">Subtitle (Program Name):</label>';
    echo '<input type="text" id="xd_ce_subtitle" name="xd_ce_subtitle" 
           value="' . esc_attr($subtitle) . '" style="width:100%">';
    echo '<p><small>Used for API requests when populated if Program Name is different from this Post title.</small></p>';
}

function xd_ce_save_subtitle(int $post_id): void {
    if (!isset($_POST['xd_ce_subtitle_nonce']) || 
        !wp_verify_nonce($_POST['xd_ce_subtitle_nonce'], 'xd_ce_save_subtitle')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['xd_ce_subtitle'])) {
        update_post_meta(
            $post_id,
            '_xd_ce_subtitle',
            sanitize_text_field($_POST['xd_ce_subtitle'])
        );
    }
}
add_action('save_post', 'xd_ce_save_subtitle');

// Initialize plugin
require_once XDCECOURSE_PLUGIN_DIR . 'api/xd-connect.php';
require_once XDCECOURSE_PLUGIN_DIR . 'api/xd-settings.php';

if (is_admin()) {
    new XD_Settings();
}

new XDConnectAPI();
