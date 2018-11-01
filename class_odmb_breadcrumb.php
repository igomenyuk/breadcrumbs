<?php
//The breadcrumb class
class odm_breadcrumb
{
    public $cus_title;
    protected $title;
    protected $template;
    protected $template_no_anchor;
    protected $linked = false;
    protected $url;
    protected $id = null;
    private $_title = null;
    protected $type;
    protected $allowed_html = [];
    const default_template_no_anchor = '%htitle%';

    public function __construct($title = '', $template = '', array $type = [], $url = '', $id = null)
    {
        $this->allowed_html = apply_filters('odm_allowed_html', wp_kses_allowed_html('post'));
        $this->type = $type;
        $this->set_id($id);
        $this->set_title($title);
        $this->template_no_anchor = odm_breadcrumb::default_template_no_anchor;
        if($template == null)
        {
            $this->set_template(odm_breadcrumb::get_default_template());
        }
        else
        {
            if($url == null)
            {
                $this->template_no_anchor = wp_kses(apply_filters('odm_breadcrumb_template_no_anchor', $template, $this->type, $this->id), $this->allowed_html);
                $this->set_template(odm_breadcrumb::get_default_template());
            }
            else
            {
                $this->set_template($template);
            }
        }
        $this->set_url($url);
    }
    static public function get_default_template()
    {
        return sprintf('<span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" title="%1$s" href="%%link%%" class="%%type%%"><span property="name">%%htitle%%</span></a><meta property="position" content="%%position%%"></span>', esc_attr__('Go to %title%.','breadcrumb-odmb'));
    }
    public function set_title($title)
    {
        $this->title = apply_filters('odm_breadcrumb_title', $title, $this->type, $this->id);
        $this->_title = $this->title;
    }
    public function get_title()
    {
        return $this->title;
    }
    public function set_url($url)
    {
        $url = trim($url);
        $this->url = apply_filters('odm_breadcrumb_url', $url, $this->type, $this->id);
        if($this->url === '')
        {
            $this->linked = false;
        }
        else
        {
            $this->linked = true;
        }
    }
    public function set_template($template)
    {
        $this->template = wp_kses(apply_filters('odm_breadcrumb_template', $template, $this->type, $this->id), $this->allowed_html);
    }
    public function set_id($id)
    {
        $this->id = $id;
    }
    public function get_id()
    {
        return $this->id;
    }
    public function add_type($type)
    {
        $this->type[] = $type;
    }
    public function get_types()
    {
        return $this->type;
    }
    public function assemble($linked, $position)
    {
        $replacements = [
            '%title%' => esc_attr(strip_tags($this->title)),
            '%link%' => esc_url($this->url),
            '%htitle%' => $this->title,
            '%type%' => apply_filters('odm_breadcrumb_types', $this->type, $this->id),
            '%ftitle%' => esc_attr(strip_tags($this->_title)),
            '%fhtitle%' => $this->_title,
            '%position%' => $position
        ];
        if(is_array($replacements['%type%']))
        {
            array_walk($replacements['%type%'], 'sanitize_html_class');
            $replacements['%type%'] = esc_attr(implode(' ', $replacements['%type%']));
        }
        $replacements = apply_filters('odm_template_tags', $replacements, $this->type, $this->id);
        if($this->linked && $linked)
        {
            return str_replace(array_keys($replacements), $replacements, $this->template);
        }
        else
        {
            return str_replace(array_keys($replacements), $replacements, $this->template_no_anchor);
        }
    }
}