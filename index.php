<?php
/*
Plugin Name: ODM Breadcrumb
Description: 'echo odm_breadcrumb($separator = ' ',$custom_title_first_page = '')' to display breadcrumbs, for custom breadcrumbs
'odm_custom_breadcrumbs()'
*/

require_once(dirname(__FILE__) . '/class_odmb_breadcrumb.php');
require_once(dirname(__FILE__) . '/class_odmb_trail.php');

$breadcrumb_odmb = null;

if( function_exists('acf_add_local_field_group') ):
    acf_add_local_field_group(array(
        'key' => 'group_5bd0815dc17d0',
        'title' => 'ODM Breadcrumbs',
        'fields' => array(
            array(
                'key' => 'field_5bd0815f4f5dd',
                'label' => 'Display breadcrumbs',
                'name' => 'odm_display_breadcrumbs',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array(
                'key' => 'field_5bd082ad4f5de',
                'label' => 'Created custom breadcrumbs',
                'name' => 'odm_created_custom_breadcrumbs',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_5bd0815f4f5dd',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => '',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5bd082d54f5df',
                        'label' => 'Choose page',
                        'name' => 'odm_choose_page',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'page',
                        ),
                        'taxonomy' => '',
                        'allow_null' => 0,
                        'multiple' => 0,
                        'return_format' => 'object',
                        'ui' => 1,
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));
endif;

function odm_custom_breadcrumbs()
{
    if (get_field('odm_display_breadcrumbs')) {
        ?>
        <ul class="breadcrumbs">
            <?php
            foreach (get_field('odm_created_custom_breadcrumbs') as $breadcrumb) {
                if ($breadcrumb === end(get_field('odm_created_custom_breadcrumbs'))) {
                    ?>
                    <li class="breadcrumbs__item"><span class="breadcrumbs__link"><span class="breadcrumbs__span"><?= $breadcrumb->post_title ?></span></span></li>
                    <?php
                } else {
                    ?>
                    <li class="breadcrumbs__item"><a class="breadcrumbs__link" href="<?= $breadcrumb->guid ?>"><span class="breadcrumbs__span"><?= $breadcrumb->post_title ?></span><i class="breadcrumbs__icon fa fa-angle-right"></i></a></li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php
    }
}

add_action('plugins_loaded', 'odm_init', 15);
function odm_init()
{
    global $breadcrumb_odmb;
    $odm_breadcrumb_trail = new odm_breadcrumb_trail();
    $breadcrumb_odmb = new breadcrumb_odmb(apply_filters('odm_breadcrumb_trail_object', $odm_breadcrumb_trail));

}

function odm_breadcrumb($separator = ' ', $cus_title = '')
{
    $odm_breadcrumbs = '';


    if (!empty($cus_title)){
        add_filter('odm_breadcrumb_title', function ($title, $type, $id) use ($cus_title) {
            if ($type[0] === 'home')
                $title = $cus_title;

            return $title;
        }, 42, 3);
    }

    if (function_exists('odm_breadcrumbs_inside')) {
        $odm_breadcrumbs .= '<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">';
        $odm_breadcrumbs .= odm_breadcrumbs_inside(true, true, false, true, $separator);
        $odm_breadcrumbs .= '</div>';
    }
    return $odm_breadcrumbs;
}
function odm_breadcrumbs_inside($return = false, $linked = true, $reverse = false, $force = false, $cseparator = ' ')
{
    global $breadcrumb_odmb;
    if($breadcrumb_odmb !== null)
    {
        return $breadcrumb_odmb->display($return, $linked, $reverse, $force, '%1$s%2$s', $cseparator);
    }
}

class breadcrumb_odmb
{
    protected $name = 'Breadcrumb odmb';
    protected $identifier = 'breadcrumb-odmb';
    protected $unique_prefix = 'odm';
    protected $plugin_basename = null;
    protected $breadcrumb_trail = null;

    public function __construct(odm_breadcrumb_trail $breadcrumb_trail)
    {
        $this->breadcrumb_trail = $breadcrumb_trail;
        $this->opt = $this->breadcrumb_trail->opt;
        $this->plugin_basename = plugin_basename(__FILE__);
        add_action('wp_loaded', [$this, 'wp_loaded'], 15);
        add_action('init', [$this, 'init']);
    }
    public function init()
    {
        breadcrumb_odmb::setup_options($this->opt);
        if(!is_admin() || !isset($_POST[$this->unique_prefix . '_admin_reset']))
        {
            $this->get_settings();
        }
        add_filter('odm_allowed_html', [$this, 'allowed_html'], 1, 1);
        add_filter('tha_breadcrumb_navigation', array($this, 'compatible'), 99);
    }
    public function allowed_html($tags)
    {
        $allowed_html = [
            'a' => [
                'href' => true,
                'title' => true,
                'class' => true,
                'id' => true,
                'media' => true,
                'dir' => true,
                'relList' => true,
                'rel' => true,
                'aria-hidden' => true,
                'data-icon' => true,
                'itemref' => true,
                'itemid' => true,
                'itemprop' => true,
                'itemscope' => true,
                'itemtype' => true,
                'xmlns:v' => true,
                'typeof' => true,
                'property' => true,
                'vocab' => true,
                'translate' => true,
                'lang' => true
            ],
            'img' => [
                'alt' => true,
                'align' => true,
                'height' => true,
                'width' => true,
                'src' => true,
                'srcset' => true,
                'sizes' => true,
                'id' => true,
                'class' => true,
                'aria-hidden' => true,
                'data-icon' => true,
                'itemref' => true,
                'itemid' => true,
                'itemprop' => true,
                'itemscope' => true,
                'itemtype' => true,
                'xmlns:v' => true,
                'typeof' => true,
                'property' => true,
                'vocab' => true,
                'lang' => true
            ],
            'span' => [
                'title' => true,
                'class' => true,
                'id' => true,
                'dir' => true,
                'align' => true,
                'lang' => true,
                'xml:lang' => true,
                'aria-hidden' => true,
                'data-icon' => true,
                'itemref' => true,
                'itemid' => true,
                'itemprop' => true,
                'itemscope' => true,
                'itemtype' => true,
                'xmlns:v' => true,
                'typeof' => true,
                'property' => true,
                'vocab' => true,
                'translate' => true,
                'lang' => true
            ],
            'h1' => [
                'title' => true,
                'class' => true,
                'id' => true,
                'dir' => true,
                'align' => true,
                'lang' => true,
                'xml:lang' => true,
                'aria-hidden' => true,
                'data-icon' => true,
                'itemref' => true,
                'itemid' => true,
                'itemprop' => true,
                'itemscope' => true,
                'itemtype' => true,
                'xmlns:v' => true,
                'typeof' => true,
                'property' => true,
                'vocab' => true,
                'translate' => true,
                'lang' => true
            ],
            'h2' => [
                'title' => true,
                'class' => true,
                'id' => true,
                'dir' => true,
                'align' => true,
                'lang' => true,
                'xml:lang' => true,
                'aria-hidden' => true,
                'data-icon' => true,
                'itemref' => true,
                'itemid' => true,
                'itemprop' => true,
                'itemscope' => true,
                'itemtype' => true,
                'xmlns:v' => true,
                'typeof' => true,
                'property' => true,
                'vocab' => true,
                'translate' => true,
                'lang' => true
            ],
            'meta' => [
                'content' => true,
                'property' => true,
                'vocab' => true,
                'itemprop' => true
            ]
        ];
        return array_merge_recursive($tags, $allowed_html);
    }
    static function array_merge_recursive($arg1, $arg2)
    {
        foreach($arg2 as $key => $value)
        {
            if(array_key_exists($key, $arg1) && is_array($value))
            {
                $arg1[$key] = array_merge_recursive($arg1[$key], $value);
            }
            else
            {
                $arg1[$key] = $value;
            }
        }
        return $arg1;
    }
    public function wp_loaded()
    {

    }
    public function uninstall()
    {

    }

    static public function setup_options(&$opt)
    {
        breadcrumb_odmb::find_posttypes($opt);
        breadcrumb_odmb::find_taxonomies($opt);
        $opt = apply_filters('odm_settings_init', $opt);
    }
    static function find_posttypes(&$opts)
    {
        global $wp_post_types, $wp_taxonomies;
        foreach($wp_post_types as $post_type)
        {
            if(!$post_type->_builtin)
            {
                if(!isset($opts['bpost_' . $post_type->name . '_taxonomy_referer']))
                {
                    $opts['bpost_' . $post_type->name . '_taxonomy_referer'] = false;
                }
                if(!isset($opts['Hpost_' . $post_type->name . '_template']))
                {
                    $opts['Hpost_' . $post_type->name . '_template'] = odm_breadcrumb::get_default_template();
                    $opts['Hpost_' . $post_type->name . '_template_no_anchor'] = odm_breadcrumb::default_template_no_anchor;
                }
                if(!$post_type->hierarchical && !isset($opts['Spost_' . $post_type->name . '_hierarchy_type']))
                {
                    if($post_type->has_archive == true || is_string($post_type->has_archive))
                    {
                        $opts['bpost_' . $post_type->name . '_archive_display'] = true;
                    }
                    else
                    {
                        $opts['bpost_' . $post_type->name . '_archive_display'] = false;
                    }
                    $opts['apost_' . $post_type->name . '_root'] = 0;
                    $opts['bpost_' . $post_type->name . '_hierarchy_display'] = false;
                    foreach($wp_taxonomies as $taxonomy)
                    {
                        if(!apply_filters('odm_show_tax_private', $taxonomy->public, $taxonomy->name, $post_type->name))
                        {
                            continue;
                        }
                        if($taxonomy->object_type == $post_type->name || in_array($post_type->name, $taxonomy->object_type))
                        {
                            $opts['bpost_' . $post_type->name . '_hierarchy_display'] = true;
                            $opts['Spost_' . $post_type->name . '_hierarchy_type'] = $taxonomy->name;
                            break;
                        }
                    }
                    if(!isset($opts['Spost_' . $post_type->name . '_hierarchy_type']))
                    {
                        $opts['Spost_' . $post_type->name . '_hierarchy_type'] = 'odm_DATE';
                    }
                    $opts['Spost_' . $post_type->name . '_hierarchy_type'] = apply_filters('odm_default_hierarchy_type', $opts['Spost_' . $post_type->name . '_hierarchy_type'], $post_type->name);
                    $opts['bpost_' . $post_type->name . '_hierarchy_display'] = apply_filters('odm_default_hierarchy_display', $opts['bpost_' . $post_type->name . '_hierarchy_display'], $post_type->name, $opts['Spost_' . $post_type->name . '_hierarchy_type']);
                }
                if(!isset($opts['bpost_' . $post_type->name . '_hierarchy_parent_first']))
                {
                    $opts['bpost_' . $post_type->name . '_hierarchy_parent_first'] = false;
                    $opts['bpost_' . $post_type->name . '_hierarchy_parent_first'] = apply_filters('odm_default_hierarchy_parent_first', $opts['bpost_' . $post_type->name . '_hierarchy_parent_first'], $post_type->name);
                }
            }
        }
    }
    static function find_taxonomies(&$opts)
    {
        global $wp_taxonomies;
        foreach($wp_taxonomies as $taxonomy)
        {
            if(!$taxonomy->_builtin)
            {
                if(!isset($opts['Htax_' . $taxonomy->name . '_template']))
                {
                    $opts['Htax_' . $taxonomy->name . '_template'] = __(sprintf('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="Go to the %%title%% %s archives." href="%%link%%" class="%%type%%"><span property="name">%%htitle%%</span></a><meta property="position" content="%%position%%"></span>', $taxonomy->labels->singular_name), 'breadcrumb-odmb');
                    $opts['Htax_' . $taxonomy->name . '_template_no_anchor'] = __(sprintf('<span property="itemListElement" typeof="ListItem"><span property="name">%%htitle%%</span><meta property="position" content="%%position%%"></span>', $taxonomy->labels->singular_name), 'breadcrumb-odmb');
                }
            }
        }
    }
    public function compatible($breadcrumb_trail)
    {
        return $this->display(true);
    }
    private function get_settings()
    {
        $this->breadcrumb_trail->opt = wp_parse_args(get_option('odm_options'), $this->opt);
        if(is_multisite())
        {
            if(defined('odm_SETTINGS_USE_NETWORK') && odm_SETTINGS_USE_NETWORK)
            {
                $this->breadcrumb_trail->opt = wp_parse_args(get_site_option('odm_options'), $this->opt);
            }
            else if(defined('odm_SETTINGS_FAVOR_LOCAL') && odm_SETTINGS_FAVOR_LOCAL)
            {
                $this->breadcrumb_trail->opt = wp_parse_args(get_option('odm_options'), $this->breadcrumb_trail->opt);
            }
            else if(defined('odm_SETTINGS_FAVOR_NETWORK') && odm_SETTINGS_FAVOR_NETWORK)
            {
                $this->breadcrumb_trail->opt = wp_parse_args(get_site_option('odm_options'), get_option('odm_options'));
            }
        }
        $this->breadcrumb_trail->opt['bpost_page_hierarchy_display'] = true;
        $this->breadcrumb_trail->opt['Spost_page_hierarchy_type'] = 'odm_POST_PARENT';
        $this->breadcrumb_trail->opt['apost_page_root'] = get_option('page_on_front');
        $this->breadcrumb_trail->opt['apost_post_root'] = get_option('page_for_posts');

        foreach($GLOBALS['wp_post_types'] as $post_type)
        {
            if(isset($this->opt['Spost_' . $post_type->name . '_taxonomy_type']))
            {
                $this->opt['Spost_' . $post_type->name . '_hierarchy_type'] = $this->opt['Spost_' . $post_type->name . '_taxonomy_type'];
                unset($this->opt['Spost_' . $post_type->name . '_taxonomy_type']);
            }
            if(isset($this->opt['Spost_' . $post_type->name . '_taxonomy_display']))
            {
                $this->opt['Spost_' . $post_type->name . '_hierarchy_display'] = $this->opt['Spost_' . $post_type->name . '_taxonomy_display'];
                unset($this->opt['Spost_' . $post_type->name . '_taxonomy_display']);
            }
        }
    }
    public function display($return = false, $linked = true, $reverse = false, $force = false, $template = '%1$s%2$s', $cseparator = ' ')
    {
        if($force)
        {
            $this->breadcrumb_trail->breadcrumbs = [];
        }
        $this->breadcrumb_trail->fill();
        $trail_string = $this->breadcrumb_trail->display($linked, $reverse, $template, $cseparator);
        return $trail_string;
    }
}