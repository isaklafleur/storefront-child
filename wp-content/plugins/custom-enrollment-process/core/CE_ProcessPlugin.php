<?php


class CE_ProcessPlugin
{
    const PLUGIN_BASE_NAME = 'custom-enrollment-process/custom-enrollment-process.php';

    /**
     * @var CE_ProcessOptions
     */
    public $options;

    /**
     * @var CE_Database
     */
    public $db;

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;

    /**
     * Returns an instance of this class.
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new CE_ProcessPlugin();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->addTemplates();
        $this->registerHooks();
        $this->options = new CE_ProcessOptions();
        $this->db = new CE_Database();
    }

    /**
     * Returns the option value
     *
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getOptionValue($name, $default = null)
    {
        if (isset($this->options->options[$name])) {
            return $this->options->options[$name]['value'];
        } else {
            return $default;
        }
    }

    /**
     * Registers plugin hooks
     */
    public function registerHooks()
    {
        if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {
            add_filter('page_attributes_dropdown_pages_args', array($this, 'registerProjectTemplates'));
        } else {
            add_filter('theme_page_templates', array($this, 'addNewTemplate'));
        }

        add_filter('wp_insert_post_data', array($this, 'registerProjectTemplates'));

        add_filter('template_include', array($this, 'viewProjectTemplate'));

        add_action('init', [$this, 'addRewriteRules'], 10, 1);
        //add_action('woocommerce_add_to_cart', [$this, 'addProductToCart'], 10, 2);
        add_filter('woocommerce_add_to_cart_redirect', [$this, 'addProductToCart'], 10, 2);
        add_filter('woocommerce_product_tabs', [$this, 'removeProductTabs'], 100, 1);

        add_filter('woocommerce_form_field_args', [$this, 'autofillCheckoutFields'], 10, 3);
    }

    /**
     * Adds rewriting rules for the plugin template
     */
    public function addRewriteRules()
    {
        add_rewrite_tag('%enroll_id%', '([^&]+)');
        add_rewrite_rule(CE_Process::PAGE_ENROLLMENT . '/([^/]*)?', 'index.php?pagename=' . CE_Process::PAGE_ENROLLMENT . '&enroll_id=$matches[1]', 'top');
        add_rewrite_rule(CE_Process::PAGE_UPGRADE . '/([^/]*)?', 'index.php?pagename=' . CE_Process::PAGE_UPGRADE . '&enroll_id=$matches[1]', 'top');
        flush_rewrite_rules(true);
    }

    /**
     * Adds a template for the plugin page
     */
    public function addTemplates()
    {
        $this->templates = [
            'templates/enrollment-template.php' => 'Enrollment template',
            'templates/upgrade-template.php' => 'Upgrade template',
        ];
    }

    /**
     * Adds template to the page dropdown for v4.7+
     */
    public function addNewTemplate($posts_templates)
    {
        $posts_templates = array_merge($posts_templates, $this->templates);
        return $posts_templates;
    }

    /**
     * Adds template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function registerProjectTemplates($atts)
    {
        $cache_key = 'page_templates-' . sha1(get_theme_root() . '/' . get_stylesheet());

        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        wp_cache_delete($cache_key, 'themes');
        $templates = array_merge($templates, $this->templates);
        wp_cache_add($cache_key, $templates, 'themes', 1800);
        return $atts;
    }

    /**
     * Checks if the template is assigned to the page
     */
    public function viewProjectTemplate($template)
    {
        global $post;
        if (!$post) {
            return $template;
        }
        if (!isset($this->templates[get_post_meta(
                $post->ID, '_wp_page_template', true
            )])) {
            return $template;
        }
        $file = plugin_dir_path(__FILE__) . '/../' . get_post_meta(
                $post->ID, '_wp_page_template', true
            );
        if (file_exists($file)) {
            return $file;
        } else {
            echo $file;
        }
        return $template;
    }

    /**
     * Checks if a redirect is needed after adding to cart
     *
     * @param string $url
     * @param WC_Product $adding_to_cart
     * @return string
     */
    public function addProductToCart($url, $adding_to_cart)
    {
        if (empty($adding_to_cart)) {
            return $url;
        }
        $productId = $adding_to_cart->get_id();
        $customEnrollmentProcess = new CE_Process();
        $sessionPayload = $customEnrollmentProcess->getSessionPayload();
        if (isset($sessionPayload['redirectAfterAddToCart'])) {
            $requiredProductId = $sessionPayload['redirectAfterAddToCart']['productId'];
            if ($productId == $requiredProductId) {
                $url = $sessionPayload['redirectAfterAddToCart']['redirectUrl'];
                unset($sessionPayload['redirectAfterAddToCart']);
                $customEnrollmentProcess->setSessionPayload($sessionPayload);
                return $url;
            }
        }
        return $url;
    }

    public function removeProductTabs($tabs)
    {
        $customEnrollmentProcess = new CE_Process();
        $sessionPayload = $customEnrollmentProcess->getSessionPayload();
        if (isset($sessionPayload['redirectAfterAddToCart'])) {
            return [];
        }
        return $tabs;
    }

    public function autofillCheckoutFields($args, $key, $value)
    {
        $customEnrollmentProcess = new CE_Process();
        $sessionPayload = $customEnrollmentProcess->getSessionPayload();
        if (isset($sessionPayload['autofillCheckoutFields'])) {
            $fields = $sessionPayload['autofillCheckoutFields'];
            if (isset($fields[$key])) {
                $args['default'] = $fields[$key];
            }
        }
        return $args;
    }

    public static function plugin_activate() {
        $db = new CE_Database();
        $db->install();
    }

    public static function plugin_deactivate() {
        $db = new CE_Database();
        $db->uninstall();
    }
}