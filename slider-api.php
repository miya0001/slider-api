<?php
/*
Plugin Name: Slider API
Author: Takayuki Miyauchi
Plugin URI: http://firegoby.jp/
Description: Add json api for image slider
Version: 0.1.0
Author URI: http://firegoby.jp/
Domain Path: /languages
Text Domain: slider
*/

register_activation_hook(__FILE__, 'slider_api_activation');
register_deactivation_hook(__FILE__, 'slider_api_deactivation');

define('SLIDER_API_ENDPOINT', 'slider');

function slider_api_activation() {
    add_rewrite_endpoint(SLIDER_API_ENDPOINT, EP_ROOT);
    flush_rewrite_rules();
}

function slider_api_deactivation() {
    flush_rewrite_rules();
}

new SliderAPI();

class SliderAPI {

const version = '0.2.0';
const nonce_key = 'slider-api';
const option_sliders = 'slider-api';

function __construct()
{
    add_action('plugins_loaded', array(&$this, 'plugins_loaded'));
}

public function plugins_loaded()
{
    add_action('init', array(&$this, 'init'));
    add_filter('query_vars', array(&$this, 'query_vars'));
    // piority 11 for fired after nginx cache controller
    add_action('template_redirect', array(&$this, 'template_redirect'), 11);
    if (is_admin()) {
        add_action("admin_menu", array(&$this, "admin_menu"));
        add_action("admin_init", array(&$this, "admin_init"));
        add_action('pre_get_posts', array(&$this, 'pre_get_posts'));
        add_action('save_post', array(&$this, 'save_post'));
    }
}

public function pre_get_posts($wp_query) {
    $sliders = get_option(self::option_sliders);
    if (isset($_GET['post_type']) && in_array($_GET['post_type'], array_keys($sliders))) {
        if (!isset($wp_query->query_vars['orderby'])) {
            $wp_query->query_vars['orderby'] = 'menu_order';
        }
        if (!isset($wp_query->query_vars['order'])) {
            $wp_query->query_vars['order'] = 'ASC';
        }
    }
}

public function admin_init()
{
    if (!current_user_can('update_core')) {
        return;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'slider-api-settings') {
        if (isset($_POST['delete-slider']) && wp_verify_nonce($_POST['delete-slider'], self::nonce_key)) {
            $posts = get_posts(array(
                'post_type' => $_POST['id'],
                'post_status' => 'any',
            ));
            foreach ($posts as $p) {
                wp_delete_post($p->ID, true);
            }
            $sliders = get_option(self::option_sliders);
            unset($sliders[$_POST['id']]);
            update_option(self::option_sliders, $sliders);
            $url = admin_url('admin.php?page=slider-api-settings');
            set_transient('slider-api-updated', 1, 5);
            wp_redirect($url);
            exit;
        }
    }

    if (isset($_GET['page']) && $_GET['page'] === 'slider-api') {
        if (isset($_POST['add-slider']) && wp_verify_nonce($_POST['add-slider'], self::nonce_key)) {
            if (isset($_POST['slider-name']) && strlen($_POST['slider-name'])) {
                $sliders = get_option(self::option_sliders);
                $sliders[self::get_slider_id()] = $_POST['slider-name'];
                update_option(self::option_sliders, $sliders);
                set_transient('slider-api-updated', 1, 5);
            }
            $url = admin_url('admin.php?page=slider-api&saved=true');
            wp_redirect($url);
            exit;
        }
    }
}

public function admin_menu()
{
    global $menu;
    $max = intval(max(array_keys($menu))) + 1;
    $menu[$max] = array(
        '',
        'read',
        "separator-slider_api",
        '',
        'wp-menu-separator'
    );
    $max = $max + 1;

    $hook = add_menu_page(
        "Slider API",
        "Slider API",
        "update_core",
        "slider-api",
        false,
        plugins_url('img/icon.png', __FILE__),
        $max
    );

    $hook = add_submenu_page(
        "slider-api",
        "Manage API",
        "Manage API",
        "update_core",
        "slider-api",
        array(&$this, "admin_panel")
    );
    add_action('admin_print_styles-'.$hook, array(&$this, 'admin_styles'));
    add_action('admin_print_scripts-'.$hook, array(&$this, 'admin_scripts'));

    $hook = add_submenu_page(
        "slider-api",
        "Settings",
        "Settings",
        "update_core",
        "slider-api-settings",
        array(&$this, "admin_panel")
    );
    add_action('admin_print_styles-'.$hook, array(&$this, 'admin_styles'));
    add_action('admin_print_scripts-'.$hook, array(&$this, 'admin_scripts'));

    $max = $max + 1;
    $temp_menu = $menu; // これやらないと無限ループ
    foreach ($temp_menu as $key => $item) {
        if (isset($item[6]) && preg_match('/slider-api\/img\/icon2.png/', $item[6])) {
            $menu[$max++] = $item;
            unset($menu[$key]);
        }
    }
    ksort($menu); // これをやらないとセパレーターが出ない
}


public function admin_scripts()
{
    wp_enqueue_script(
        'slider-api-bootstrap-tab',
        plugins_url("js/bootstrap-tab.js", __FILE__),
        array('jquery'),
        filemtime(dirname(__FILE__).'/js/bootstrap-tab.js'),
        true
    );
    wp_enqueue_script(
        'slider-api-bootstrap-modal',
        plugins_url("js/bootstrap-modal.js", __FILE__),
        array('jquery'),
        filemtime(dirname(__FILE__).'/js/bootstrap-modal.js'),
        true
    );
    wp_enqueue_script(
        'slider-api-admin-script',
        plugins_url("js/slider-api.js", __FILE__),
        array(
            'slider-api-bootstrap-tab',
            'slider-api-bootstrap-modal',
        ),
        filemtime(dirname(__FILE__).'/js/slider-api.js'),
        true
    );
}

public function admin_styles()
{
    wp_enqueue_style(
        'slider-api-admin-style',
        plugins_url("css/style.css", __FILE__),
        array(),
        filemtime(dirname(__FILE__).'/css/style.css')
    );
}

public function admin_panel()
{
?>
<div id="slider-api-admin" class="wrap">
<div class="row-fluid">
<h2>Slider API</h2>
<?php if (get_transient('slider-api-updated')) : ?>
<div class="alert alert-success">
Updated!
</div>
<?php endif; ?>
<?php
    if (isset($_GET['page']) && $_GET['page'] === 'slider-api') {
        require_once(dirname(__FILE__).'/admin/manage-api.php');
    } elseif (isset($_GET['page']) && $_GET['page'] === 'slider-api-settings') {
        require_once(dirname(__FILE__).'/admin/settings.php');
    }
?>
</div><!-- .row-fluid -->
</div><!-- #slider_api-admin -->
<?php
}

public function query_vars($vars)
{
    $vars[] = SLIDER_API_ENDPOINT;
    return $vars;
}

public function init()
{
    if (is_admin()) {
        global $pagenow;
        if ($pagenow === 'plugins.php') {
            if (isset($_GET['plugin'])) {
                if (basename(__FILE__) === basename($_GET['plugin'])) {
                    return; // なにもしない
                }
            }
        }
        add_rewrite_endpoint(SLIDER_API_ENDPOINT, EP_ROOT);
    }

    $sliders = get_option(self::option_sliders);
    if (is_array($sliders) && count($sliders)) {
        foreach ($sliders as $post_type => $post_type_name) {
            $this->add_post_types($post_type, $post_type_name);
        }
    }
}

private function add_post_types($post_type, $post_type_name)
{
    $args = array(
        'label' => esc_html($post_type_name),
        'labels' => array(
            'singular_name' => __('Pages', 'slider_api'),
            'add_new_item' => __('Add New Page', 'slider_api'),
            'edit_item' => __('Edit Page', 'slider_api'),
            'add_new' => __('Add New', 'slider_api'),
            'new_item' => __('New Page', 'slider_api'),
            'view_item' => __('View Page', 'slider_api'),
            'not_found' => __('No pages found.', 'slider_api'),
            'not_found_in_trash' => __(
                'No pages found in Trash.',
                'slider_api'
            ),
            'search_items' => __('Search Pages', 'slider_api'),
        ),
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => false,
        'show_in_nav_menus' => false,
        'can_export' => false,
        'menu_icon' => plugins_url('img/icon2.png', __FILE__),
        'register_meta_box_cb' => array(&$this, 'add_meta_box'),
        'supports' => array(
            'title',
            'excerpt',
            'page-attributes',
            'thumbnail',
        )
    );
    register_post_type($post_type, apply_filters('slider_api_register_post_type_args', $args));
}

public function add_meta_box()
{
    add_meta_box(
        'slider-api-linkurl',
        __('URL', 'slider_api'),
        array(&$this, 'url_meta_box'),
        get_post_type(),
        'normal',
        'low'
    );
}

public function url_meta_box($post, $box)
{
    $url = get_post_meta($post->ID, '_link_url', true);
    printf(
        '<input type="text" name="slider_api_link_url" value="%s" style="width:100%%;" />',
        esc_attr(esc_url($url, array('http', 'https')))
    );
}

public function save_post($id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $id;

    if (isset($_POST['action']) && $_POST['action'] == 'inline-save')
        return $id;

    if (isset($_POST['slider_api_link_url']) && $_POST['slider_api_link_url']) {
        update_post_meta($id, '_link_url', $_POST['slider_api_link_url']);
    }
}

public function template_redirect()
{
    global $wp_query;
    if (isset($wp_query->query[SLIDER_API_ENDPOINT])) {
        if (!post_type_exists(get_query_var(SLIDER_API_ENDPOINT))) {
            $this->send404();
            return;
        }
        $args = array(
            'post_type' => get_query_var(SLIDER_API_ENDPOINT),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'nopaging' => true,
            'orderby' => 'menu_order',
            'order' => 'DESC',
        );
        $posts = get_posts($args);
        $slides = array();
        if (has_filter('slider_api_get_sliders_posts')) {
            $slides = apply_filters('slider_api_get_sliders_posts', $posts);
        } else {
            foreach ($posts as $post) {
                setup_postdata($post);
                $thumbid = get_post_thumbnail_id($post->ID);
                if (isset($_GET['size']) && strlen($_GET['size'])) {
                    $img = wp_get_attachment_image_src($thumbid, $_GET['size']);
                } else {
                    $img = wp_get_attachment_image_src(
                        $thumbid,
                        apply_filters('slider_api_default_image_size', 'post-thumbnail')
                    );
                }
                $link_url = get_post_meta($post->ID, '_link_url', true);
                $link_url = esc_url($link_url, array('http', 'https'));
                $slides[] = array(
                    'ID' => $post->ID,
                    'post_title' => esc_html($post->post_title),
                    'post_excerpt' => esc_html($post->post_excerpt),
                    'post_thumbnail' => $img[0],
                    'link_url' => get_post_meta($post->ID, '_link_url', true),
                );
            }
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($slides);
        exit;
    }
}

public function send404()
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
}

private function get_slider_id()
{
    return substr(md5(time()), 0, 16);
}

} // end class

// EOF
