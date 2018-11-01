<?php
//The trail class
class odm_breadcrumb_trail
{
    public $breadcrumbs = [];
    public $trail = [];
    public $opt;

    public function __construct()
    {
        //@see https://core.trac.wordpress.org/ticket/10527
        if(!is_textdomain_loaded('breadcrumb-odmb'))
        {
            load_plugin_textdomain('breadcrumb-odmb', false, 'breadcrumb-odmb/languages');
        }
        $this->trail = &$this->breadcrumbs;
        $this->opt = [
            'bmainsite_display' => true,
            'Hmainsite_template' => odm_breadcrumb::get_default_template(),
            'Hmainsite_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'bhome_display' => true,
            'Hhome_template' => odm_breadcrumb::get_default_template(),
            'Hhome_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'bblog_display' => true,
            'blimit_title' => false,
            'amax_title_length' => 20,
            'bcurrent_item_linked' => false,
            'bpost_page_hierarchy_display' => true,
            'bpost_page_hierarchy_parent_first' => false,
            'Spost_page_hierarchy_type' => 'odm_POST_PARENT',
            'Hpost_page_template' => odm_breadcrumb::get_default_template(),
            'Hpost_page_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'apost_page_root' => get_option('page_on_front'),
            'Hpaged_template' => sprintf('<span property="itemListElement" typeof="ListItem"><span property="name">%1$s</span><meta property="position" content="%%position%%"></span>', esc_attr__('Page %htitle%', 'breadcrumb-odmb')),
            'bpaged_display' => false,
            'Hpost_post_template' => odm_breadcrumb::get_default_template(),
            'Hpost_post_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'apost_post_root' => get_option('page_for_posts'),
            'bpost_post_hierarchy_display' => true,
            'bpost_post_hierarchy_parent_first' => false,
            'bpost_post_taxonomy_referer' => false,
            'Spost_post_hierarchy_type' => 'category',
            'bpost_attachment_hierarchy_display' => true,
            'bpost_attachment_hierarchy_parent_first' => true,
            'Spost_attachment_hierarchy_type' => 'odm_POST_PARENT',
            'apost_attachment_root' => 0,
            'Hpost_attachment_template' => odm_breadcrumb::get_default_template(),
            'Hpost_attachment_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'H404_template' => odm_breadcrumb::default_template_no_anchor,
            'S404_title' => __('404', 'breadcrumb-odmb'),
            'Hsearch_template' => sprintf('<span property="itemListElement" typeof="ListItem"><span property="name">%1$s</span><meta property="position" content="%%position%%"></span>',
                sprintf(esc_attr__('Search results for &#39;%1$s&#39;', 'breadcrumb-odmb'),
                    sprintf('<a property="item" typeof="WebPage" title="%1$s" href="%%link%%" class="%%type%%">%%htitle%%</a>', esc_attr__('Go to the first page of search results for %title%.', 'breadcrumb-odmb')))),
            'Hsearch_template_no_anchor' => sprintf('<span property="itemListElement" typeof="ListItem"><span property="name">%1$s</span><meta property="position" content="%%position%%"></span>',
                sprintf(esc_attr__('Search results for &#39;%1$s&#39;', 'breadcrumb-odmb'), '%htitle%')),
            'Htax_post_tag_template' => sprintf('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="%1$s" href="%%link%%" class="%%type%%"><span property="name">%%htitle%%</span></a><meta property="position" content="%%position%%"></span>', esc_attr__('Go to the %title% tag archives.', 'breadcrumb-odmb')),
            'Htax_post_tag_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'Htax_post_format_template' => sprintf('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="%1$s" href="%%link%%" class="%%type%%"><span property="name">%%htitle%%</span></a><meta property="position" content="%%position%%"></span>', esc_attr__('Go to the %title% archives.', 'breadcrumb-odmb')),
            'Htax_post_format_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'Hauthor_template' => sprintf('<span property="itemListElement" typeof="ListItem"><span property="name">%1$s</span><meta property="position" content="%%position%%"></span>',
                sprintf(esc_attr__('Articles by: %1$s', 'breacrumb-odmb'),
                    sprintf('<a title="%1$s" href="%%link%%" class="%%type%%">%%htitle%%</a>', esc_attr__('Go to the first page of posts by %title%.', 'breadcrumb-odmb')))),
            'Hauthor_template_no_anchor' => sprintf('<span property="itemListElement" typeof="ListItem"><span property="name">%1$s</span><meta property="position" content="%%position%%"></span>',
                sprintf(esc_attr__('Articles by: %1$s', 'breacrumb-odmb'), '%htitle%')),
            'Sauthor_name' => 'display_name',
            'aauthor_root' => 0,
            'Htax_category_template' => sprintf('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="%1$s" href="%%link%%" class="%%type%%"><span property="name">%%htitle%%</span></a><meta property="position" content="%%position%%"></span>', esc_attr__('Go to the %title% category archives.', 'breadcrumb-odmb')),
            'Htax_category_template_no_anchor' => odm_breadcrumb::default_template_no_anchor,
            'Hdate_template' => sprintf('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="%1$s" href="%%link%%" class="%%type%%"><span property="name">%%htitle%%</span></a><meta property="position" content="%%position%%"></span>', esc_attr__('Go to the %title% archives.', 'breadcrumb-odmb')),
            'Hdate_template_no_anchor' => odm_breadcrumb::default_template_no_anchor
        ];
    }
    public function &add(odm_breadcrumb $object)
    {
        $this->breadcrumbs[] = $object;
        return $this->breadcrumbs[count($this->breadcrumbs) - 1];
    }
    protected function do_search($search_query, $is_paged = false)
    {
        $breadcrumb = $this->add(new odm_breadcrumb($search_query, $this->opt['Hsearch_template_no_anchor'], ['search', 'current-item']));
        if($this->opt['bcurrent_item_linked'] || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hsearch_template']);
            $breadcrumb->set_url(get_search_link($search_query));
        }
    }
    protected function do_author($author_data, $is_paged = false)
    {
        $valid_author_name = ['display_name', 'nickname', 'first_name', 'last_name'];
        if(in_array($this->opt['Sauthor_name'], $valid_author_name))
        {
            $breadcrumb = $this->add(new odm_breadcrumb(get_the_author_meta($this->opt['Sauthor_name'], $author_data->ID), $this->opt['Hauthor_template_no_anchor'], ['author', 'current-item'], null, $author_data->ID));
            if($this->opt['bcurrent_item_linked'] || ($is_paged && $this->opt['bpaged_display']))
            {
                $breadcrumb->set_template($this->opt['Hauthor_template']);
                $breadcrumb->set_url(get_author_posts_url($author_data->ID));
            }
        }
    }
    protected function query_var_to_taxonomy($query_var)
    {
        global $wp_taxonomies;
        foreach($wp_taxonomies as $taxonomy)
        {
            if($taxonomy->query_var === $query_var)
            {
                return $taxonomy->name;
            }
        }
        return false;
    }
    protected function determine_taxonomy()
    {
        global $wp;
        $bk_req = $_SERVER['REQUEST_URI'];
        $url_split = explode(home_url(), esc_url(wp_get_referer()));
        if(isset($url_split[1]))
        {
            $_SERVER['REQUEST_URI'] = $url_split[1];
        }
        else
        {
            return false;
        }
        $odm_wp = new WP();
        $odm_wp->public_query_vars = $wp->public_query_vars;
        $odm_wp->parse_request();
        $_SERVER['REQUEST_URI'] = $bk_req;
        if(is_array($odm_wp->query_vars))
        {
            foreach($odm_wp->query_vars as $query_var => $value)
            {
                if($taxonomy = $this->query_var_to_taxonomy($query_var))
                {
                    return $taxonomy;
                }
            }
        }
        return false;
    }
    protected function pick_post_term($id, $type, $taxonomy)
    {
        $odm_object = get_the_terms($id, $taxonomy);
        $potential_parent = 0;
        if(is_array($odm_object))
        {
            $odm_use_term = key($odm_object);
            foreach($odm_object as $key => $object)
            {
                if($object->parent > 0  && ($potential_parent === 0 || $object->parent === $potential_parent))
                {
                    $odm_use_term = $key;
                    $potential_parent = $object->term_id;
                }
            }
            return $odm_object[$odm_use_term];
        }
        return false;
    }
    protected function post_hierarchy($id, $type, $parent = null)
    {
        if($this->opt['bpost_' . $type . '_hierarchy_display'])
        {
            if($this->opt['Spost_' . $type . '_hierarchy_type'] === 'odm_DATE')
            {
                $post = get_post($id);
                $this->do_day($post, $type, false, false);
                $this->do_month($post, $type, false, false);
                $this->do_year($post, $type, false, false);
            }
            else if($this->opt['Spost_' . $type . '_hierarchy_type'] === 'odm_POST_PARENT')
            {
                if($parent == null)
                {
                    $parent = get_post($id);
                    $parent = $parent->post_parent;
                }
                $frontpage = get_option('page_on_front');
                if($parent > 0 && $id != $parent && $frontpage != $parent)
                {
                    $parent = $this->post_parents($parent, $frontpage);
                }
            }
            else
            {
                $taxonomy = $this->opt['Spost_' . $type . '_hierarchy_type'];
                if($this->opt['bpost_' . $type . '_taxonomy_referer'] && $referrer_taxonomy = $this->determine_taxonomy())
                {
                    $terms = get_the_terms($id, $referrer_taxonomy);
                    if(is_array($terms))
                    {
                        $taxonomy = $referrer_taxonomy;
                    }
                }
                if(is_taxonomy_hierarchical($taxonomy))
                {
                    $term = apply_filters('odm_pick_post_term', $this->pick_post_term($id, $type, $taxonomy), $id, $type, $taxonomy);
                    if($term instanceof WP_Term)
                    {
                        $parent = $this->term_parents($term->term_id, $taxonomy);
                    }
                }
                else
                {
                    $this->post_terms($id, $taxonomy);
                }
            }
        }
        if(!($parent instanceof WP_Post))
        {
            $parent = get_post($id);
        }
        $this->type_archive($parent, $type);
    }
    protected function post_terms($id, $taxonomy)
    {
        $odm_terms = apply_filters('odm_post_terms', get_the_terms($id, $taxonomy), $taxonomy, $id);
        if(is_array($odm_terms))
        {
            $title = '';
            $is_first = true;
            foreach($odm_terms as $term)
            {
                if($is_first == false)
                {
                    $title .= ', ';
                }
                $title .= str_replace(
                   ['%title%', '%link%', '%htitle%', '%type%'],
                   [$term->name, $this->_add_post_type_arg(get_term_link($term), null, $term->taxonomy), $term->name, $term->taxonomy],
                    $this->opt['Htax_' . $term->taxonomy . '_template']);
                $is_first = false;
            }
            $breadcrumb = $this->add(new odm_breadcrumb($title, '%htitle%', ['taxonomy', $taxonomy]));
        }
    }
    protected function term_parents($id, $taxonomy)
    {
        $term = get_term($id, $taxonomy);
        if($term instanceof WP_Term)
        {
            $breadcrumb = $this->add(new odm_breadcrumb($term->name, $this->opt['Htax_' . $taxonomy . '_template'], ['taxonomy', $taxonomy], $this->_add_post_type_arg(get_term_link($term), null, $taxonomy), $id));
            if($term->parent && $term->parent != $id)
            {
                $ret_term = $this->term_parents($term->parent, $taxonomy);
                if($ret_term instanceof WP_Term)
                {
                    $term = $ret_term;
                }
            }
        }
        return $term;
    }
    protected function post_parents($id, $frontpage)
    {
        $parent = get_post($id);
        $breadcrumb = $this->add(new odm_breadcrumb(get_the_title($id), $this->opt['Hpost_' . $parent->post_type . '_template'], ['post', 'post-' . $parent->post_type], get_permalink($id), $id));
        if($parent->post_parent > 0 && $id != $parent->post_parent && $frontpage != $parent->post_parent)
        {
            $parent = $this->post_parents($parent->post_parent, $frontpage);
        }
        return $parent;
    }
    protected function do_post($post, $force_link = false, $is_paged = false, $is_current_item = true)
    {
        if(!($post instanceof WP_Post))
        {
            _doing_it_wrong(__CLASS__ . '::' . __FUNCTION__, __('$post global is not of type WP_Post', 'breadcrumb-odmb'), '5.1.1');
            return;
        }
        $breadcrumb = $this->add(new odm_breadcrumb(get_the_title($post), $this->opt['Hpost_' . $post->post_type . '_template_no_anchor'], array('post', 'post-' . $post->post_type), null, $post->ID));
        if($is_current_item)
        {
            $breadcrumb->add_type('current-item');
        }
        if($force_link || ($is_current_item && $this->opt['bcurrent_item_linked']) || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hpost_' . $post->post_type . '_template']);
            $breadcrumb->set_url(get_permalink($post));
        }
        $frontpage = get_option('page_on_front');
        if($this->opt['bpost_' . $post->post_type. '_hierarchy_parent_first'] && $post->post_parent > 0 && $post->ID != $post->post_parent && $frontpage != $post->post_parent)
        {
            $parent = get_post($post->post_parent);
            $this->do_post($parent, true, false, false);
        }
        else
        {
            $this->post_hierarchy($post->ID, $post->post_type, $post->post_parent);
        }
    }
    protected function do_attachment()
    {
        _deprecated_function( __FUNCTION__, '6.0', 'odm_breadcrumb_trail::do_post');
        $this->do_post(get_post());
    }
    protected function do_archive_by_term($term, $is_paged = false)
    {
        $breadcrumb = $this->add(new odm_breadcrumb($term->name, $this->opt['Htax_' . $term->taxonomy . '_template_no_anchor'], ['archive', 'taxonomy', $term->taxonomy, 'current-item'], null, $term->term_id));
        if($this->opt['bcurrent_item_linked'] || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Htax_' . $term->taxonomy . '_template']);
            $breadcrumb->set_url($this->_add_post_type_arg(get_term_link($term), null, $term->taxonomy));
        }
        if($term->parent)
        {
            $this->term_parents($term->parent, $term->taxonomy);
        }
    }
    protected function do_day($post, $type, $is_paged = false, $is_current_item = true)
    {
        $breadcrumb = $this->add(new odm_breadcrumb(get_the_time(_x('d', 'day archive breadcrumb date format', 'breadcrumb-odmb'), $post), $this->opt['Hdate_template_no_anchor'], ['archive', 'date-day']));
        if($is_current_item)
        {
            $breadcrumb->add_type('current-item');
        }
        if(!$is_current_item || ($is_current_item && $this->opt['bcurrent_item_linked']) || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hdate_template']);
            $url = get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d'));
            $breadcrumb->set_url($this->_add_post_type_arg($url, $type));
        }
    }
    protected function do_month($post, $type, $is_paged = false, $is_current_item = true)
    {
        $breadcrumb = $this->add(new odm_breadcrumb(get_the_time(_x('F', 'month archive breadcrumb date format', 'breadcrumb-odmb'), $post), $this->opt['Hdate_template_no_anchor'], ['archive', 'date-month']));
        if($is_current_item)
        {
            $breadcrumb->add_type('current-item');
        }
        if(!$is_current_item || ($is_current_item && $this->opt['bcurrent_item_linked']) || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hdate_template']);
            $url = get_month_link(get_the_time('Y'), get_the_time('m'));
            $breadcrumb->set_url($this->_add_post_type_arg($url, $type));
        }
    }
    protected function do_year($post, $type, $is_paged = false, $is_current_item = true)
    {
        $breadcrumb = $this->add(new odm_breadcrumb(get_the_time(_x('Y', 'year archive breadcrumb date format', 'breadcrumb-odmb'), $post), $this->opt['Hdate_template_no_anchor'], ['archive', 'date-year']));
        if($is_current_item)
        {
            $breadcrumb->add_type('current-item');
        }
        if(!$is_current_item || ($is_current_item && $this->opt['bcurrent_item_linked']) || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hdate_template']);
            $url = get_year_link(get_the_time('Y'));
            $breadcrumb->set_url($this->_add_post_type_arg($url, $type));
        }
    }
    protected function do_archive_by_date($type)
    {
        _deprecated_function( __FUNCTION__, '6.0', 'odm_breadcrumb_trail::do_day, odm_breadcrumb_trail::do_month, and/or odm_breadcrumb_trail::do_year');
        if(is_day() || is_single())
        {
            $this->do_day(get_post(), $type, is_paged(), is_day());
        }
        if(is_month() || is_day() || is_single())
        {
            $this->do_month(get_post(), $type, is_paged(), is_month());
        }
        $this->do_year(get_post(), $type, is_paged(), is_year());
    }
    protected function do_archive_by_post_type($type_str, $is_paged = false)
    {
        $post_type_obj = get_post_type_object($type_str);
        $title = apply_filters('post_type_archive_title', $post_type_obj->labels->name, $type_str);
        $breadcrumb = $this->add(new odm_breadcrumb($title, $this->opt['Hpost_' . $type_str . '_template_no_anchor'], ['archive', 'post-' . $type_str . '-archive', 'current-item']));
        if($this->opt['bcurrent_item_linked'] || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hpost_' . $type_str . '_template']);
            $breadcrumb->set_url(get_post_type_archive_link($type_str));
        }
    }
    protected function do_home($force_link = false, $is_paged = false, $is_current_item = true)
    {
        global $current_site;
        if(!$this->opt['bhome_display'])
        {
            return;
        }
        $site_name = get_option('blogname');
        $breadcrumb = $this->add(new odm_breadcrumb($site_name, $this->opt['Hhome_template_no_anchor'], ['home']));
        if($is_current_item)
        {
            $breadcrumb->add_type('current-item');
        }
        if($force_link || ($is_current_item && $this->opt['bcurrent_item_linked']) || ($is_paged && $this->opt['bpaged_display']))
        {
            $breadcrumb->set_template($this->opt['Hhome_template']);
            $breadcrumb->set_url(get_home_url());
        }
        if($this->opt['bmainsite_display'] && !is_main_site())
        {
            $site_name = get_site_option('site_name');
            $breadcrumb = $this->add(new odm_breadcrumb($site_name, $this->opt['Hmainsite_template'], ['main-home'], get_home_url($current_site->blog_id)));
        }
    }
    protected function post_type_archive_title($object)
    {
        if(isset($object->labels->name))
        {
            return apply_filters('post_type_archive_title', $object->labels->name, $object->name);
        }
    }
    protected function is_builtin($post_type)
    {
        $type = get_post_type_object($post_type);
        if($type === null)
        {
            return true;
        }
        else
        {
            return $type->_builtin;
        }
    }
    protected function treat_as_root_page($post_type)
    {
        return (is_home() || (is_post_type_archive() && !$this->opt['bpost_' . $post_type . '_archive_display']));
    }
    protected function has_archive($post_type)
    {
        $type = get_post_type_object($post_type);
        return $type->has_archive;
    }
    protected function get_type_string_query_var($default = 'post')
    {
        $type_str = get_query_var('post_type', $default);
        if($type_str === '' || is_array($type_str))
        {
            $post = get_post();
            if($post instanceof WP_Post)
            {
                $type_str = $post->post_type;
            }
            else
            {
                $type_str = $default;
            }
        }
        return esc_attr($type_str);
    }
    protected function is_type_query_var_array()
    {
        return is_array(get_query_var('post_type'));
    }
    protected function _add_post_type_arg($url, $type = null, $taxonomy = null)
    {
        global $wp_taxonomies;
        if($type == null)
        {
            $type = $this->get_type_string_query_var();
        }
        $add_query_arg = (!($taxonomy && $type === $wp_taxonomies[$taxonomy]->object_type[0]) && $type !== 'post');
        if(apply_filters('odm_add_post_type_arg', $add_query_arg, $type, $taxonomy))
        {
            $url = add_query_arg(['post_type' => $type], $url);
        }
        return $url;
    }
    protected function type_archive($type, $type_str = false)
    {
        global $wp_taxonomies;
        if(!isset($type->taxonomy) && $type_str === false)
        {
            $type_str = $this->get_type_string_query_var();
        }
        if($type_str && !$this->is_builtin($type_str) && $this->opt['bpost_' . $type_str . '_archive_display'] && $this->has_archive($type_str))
        {
            $breadcrumb = $this->add(new odm_breadcrumb($this->post_type_archive_title(get_post_type_object($type_str)), $this->opt['Hpost_' . $type_str . '_template'], ['post', 'post-' . $type_str . '-archive'], get_post_type_archive_link($type_str)));
        }
        else if(isset($type->taxonomy) && isset($wp_taxonomies[$type->taxonomy]->object_type[0])
            && !$this->is_builtin($this->get_type_string_query_var($wp_taxonomies[$type->taxonomy]->object_type[0]))
            && $this->opt['bpost_' . $this->get_type_string_query_var($wp_taxonomies[$type->taxonomy]->object_type[0]) . '_archive_display']
            && $this->has_archive($this->get_type_string_query_var($wp_taxonomies[$type->taxonomy]->object_type[0]))
            && !$this->is_type_query_var_array())
        {
            $post_type = apply_filters('odm_type_archive_post_type', $this->get_type_string_query_var($wp_taxonomies[$type->taxonomy]->object_type[0]));
            $breadcrumb = $this->add(new odm_breadcrumb($this->post_type_archive_title(get_post_type_object($post_type)), $this->opt['Hpost_' . $post_type . '_template'], ['post', 'post-' . $post_type . '-archive'], get_post_type_archive_link($post_type)));
        }
    }
    protected function do_root($type_str, $root_id, $is_paged = false, $is_current_item = true)
    {
        if($type_str === 'page')
        {
            return;
        }
        $frontpage_id = get_option('page_on_front');
        $odm_post = get_post($root_id);
        if($odm_post instanceof WP_Post && $root_id > 0 && $root_id != $frontpage_id)
        {
            $breadcrumb = $this->add(new odm_breadcrumb(get_the_title($root_id), $this->opt['Hpost_' . $type_str . '_template_no_anchor'], [$type_str . '-root', 'post', 'post-' . $type_str], null, $root_id));
            if($is_current_item)
            {
                $breadcrumb->add_type('current-item');
            }
            if(!$is_current_item || ($is_current_item && $this->opt['bcurrent_item_linked']) || ($is_paged && $this->opt['bpaged_display']))
            {
                $breadcrumb->set_template($this->opt['Hpost_' . $type_str . '_template']);
                $breadcrumb->set_url(get_permalink($root_id));
            }
            if($odm_post->post_parent > 0 && $odm_post->ID != $odm_post->post_parent && $frontpage_id != $odm_post->post_parent)
            {
                $this->post_parents($odm_post->post_parent, $frontpage_id);
            }
        }
    }
    protected function do_404()
    {
        $this->breadcrumbs[] = new odm_breadcrumb($this->opt['S404_title'], $this->opt['H404_template'], ['404', 'current-item']);
    }
    protected function do_paged($page_number)
    {
        $this->breadcrumbs[] = new odm_breadcrumb((string) $page_number, $this->opt['Hpaged_template'], ['paged']);
    }
    public function fill()
    {
        global $wpdb, $wp_query, $wp;
        if(count($this->breadcrumbs) > 0)
        {
            return null;
        }
        if($this->opt['bblog_display'])
        {
            $this->opt['apost_post_root'] = get_option('page_for_posts');
        }
        else
        {
            $this->opt['apost_post_root'] = false;
        }
        do_action('odm_before_fill', $this);
        $type = $wp_query->get_queried_object();
        if($this->opt['bpaged_display'] && (is_paged() || is_singular() && get_query_var('page') > 1))
        {
            if(get_query_var('paged') > 0)
            {
                $page_number = (int) abs(get_query_var('paged'));
            }
            else
            {
                $page_number = (int) abs(get_query_var('page'));
            }
            $this->do_paged($page_number);
        }
        if(is_front_page())
        {
            if($this->opt['bhome_display'])
            {
                $this->do_home(false, is_paged());
            }
        }
        else if(is_singular())
        {
            $this->do_post(get_post(), false, (get_query_var('page') > 1));
            if(is_attachment())
            {
                $post = get_post();
                $type = get_post($post->post_parent);
            }
            $this->do_root($type->post_type, $this->opt['apost_' . $type->post_type . '_root'], is_paged(), false);
        }
        else if(is_search())
        {
            $this->do_search(get_search_query(), is_paged());
        }
        else if(is_author())
        {
            $this->do_author($type, is_paged());
            $this->do_root('post', $this->opt['aauthor_root'], is_paged(), false);
        }
        else if(is_archive())
        {
            $type_str = get_query_var('post_type');
            if(is_array($type_str))
            {
                $type_str = reset($type_str);
            }
            if(is_date())
            {
                if(is_day())
                {
                    $this->do_day(get_post(), $this->get_type_string_query_var(), is_paged(), true);
                }
                if(is_month() || is_day())
                {
                    $this->do_month(get_post(), $this->get_type_string_query_var(), is_paged(), is_month());
                }
                $this->do_year(get_post(), $this->get_type_string_query_var(), is_paged(), is_year());
                $type_str = $this->get_type_string_query_var();
                $this->type_archive($type, $type_str);
            }
            else if(is_post_type_archive() && !isset($type->taxonomy)
                && (!is_numeric($this->opt['apost_' . $type_str . '_root']) || $this->opt['bpost_' . $type_str . '_archive_display']))
            {
                $this->do_archive_by_post_type($this->get_type_string_query_var(), is_paged());
            }
            else if(is_category() || is_tag() || is_tax())
            {
                $this->do_archive_by_term($type, is_paged());
                $this->type_archive($type);
                $type_str = $this->get_type_string_query_var($GLOBALS['wp_taxonomies'][$type->taxonomy]->object_type[0]);
            }
            else
            {
                $this->type_archive($type);
            }
            $this->do_root($type_str, $this->opt['apost_' . $type_str . '_root'], is_paged(), $this->treat_as_root_page($type_str));
        }
        else if(is_404())
        {
            $this->do_404();
        }
        else
        {
            if(isset($type->taxonomy))
            {
                $this->do_archive_by_term($type, is_paged());
                $this->type_archive($type);
                $type_str = $this->get_type_string_query_var($wp_taxonomies[$type->taxonomy]->object_type[0]);
            }
            //Otherwise, it's likely the blog page
            else if($this->opt['bblog_display'] || is_home())
            {
                $type_str = 'post';
            }
            if(isset($type_str) && isset($this->opt['apost_' . $type_str . '_root']))
            {
                $this->do_root($type_str, $this->opt['apost_' . $type_str . '_root'], is_paged(), $this->treat_as_root_page($type_str));
            }
        }
        if(!is_front_page())
        {
            $this->do_home(true, false, false);
        }
        do_action('odm_after_fill', $this);
    }
    protected function order($reverse = false)
    {
        if($reverse)
        {
            ksort($this->breadcrumbs);
        }
        else
        {
            krsort($this->breadcrumbs);
        }
    }
    public function display($linked = true, $reverse = false, $template = '%1$s%2$s', $cseparator = ' ')
    {
        $this->order($reverse);
        $trail_str = $this->display_loop($linked, $reverse, $template, $cseparator);
        return $trail_str;
    }
    protected function display_loop($linked, $reverse, $template, $cseparator)
    {
        $position = 1;
        $last_position = count($this->breadcrumbs);
        $trail_str = '';
        foreach($this->breadcrumbs as $key => $breadcrumb)
        {
            $types = $breadcrumb->get_types();
            array_walk($types, 'sanitize_html_class');
            $class = sprintf(' class="%s"', esc_attr(implode(' ', $types)));
            if($position < $last_position)
            {
//                $separator = $this->opt['hseparator'];
                $separator = $cseparator;
            }
            else
            {
                $separator = '';
            }
            $attribs = apply_filters_deprecated('odm_li_attributes', [$class, $breadcrumb->get_types(), $breadcrumb->get_id()], '6.0.0', 'odm_display_attributes');
            $attribs = apply_filters('odm_display_attributes', $class, $breadcrumb->get_types(), $breadcrumb->get_id());
            if($this->opt['blimit_title'] && $this->opt['amax_title_length'] > 0)
            {
                $breadcrumb->title_trim($this->opt['amax_title_length']);
            }
            $trail_str .= sprintf($template, $breadcrumb->assemble($linked, $position), $separator, $attribs);
            $position++;
        }
        return $trail_str;
    }
}
