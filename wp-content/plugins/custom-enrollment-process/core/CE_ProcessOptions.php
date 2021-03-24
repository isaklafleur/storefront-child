<?php


class CE_ProcessOptions
{
    const PLUGIN_PREFIX = 'custom_enrollment_process_';
    const TYPE_TEXT_FIELD = 'text';
    const TYPE_SELECT_FIELD = 'select';

    const BRAND_PARTNER_SUBSCRIPTION_KEY = 'brand_partner_subscription_sku';
    const AFFILIATE_SUBSCRIPTION_KEY = 'affiliate_subscription_sku';
    const PRODUCT_PACK_POSTFIX = '_product_pack_sku';
    const UPGRADE_PRODUCTS_PREFIX = 'upgrade_product_';

    const PRODUCT_PACKS = [
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large'
    ];

    public $options = [];
    public $sections = [];

    public function __construct()
    {
        /*  Setting up actions... */
        add_action('admin_menu', array($this, 'custom_enrollment_process_add_options'));
        add_action('admin_init', array($this, 'custom_enrollment_process_register_settings'));
        add_filter('plugin_action_links', array($this, 'addPluginActionLinks'), 10, 2);

        $this->init();
    }

    /**
     * Get Wp options from Custom Settings page
     */
    public function init()
    {
        $this->sections = [
            'section_1' => 'Subscriptions',
            'section_2' => 'Product packs'
        ];

        $this->addOption(self::BRAND_PARTNER_SUBSCRIPTION_KEY, 'Brand partner subscription SKU', self::TYPE_TEXT_FIELD);
        $this->addOption(self::AFFILIATE_SUBSCRIPTION_KEY, 'Affiliate subscription SKU', self::TYPE_TEXT_FIELD);

        foreach (self::PRODUCT_PACKS as $key => $label) {
            $this->addOption($key . self::PRODUCT_PACK_POSTFIX, $label . ' product pack SKU', self::TYPE_TEXT_FIELD, 2);
        }

        $userRoles = (new WP_Roles())->roles;
        $userRolesOptions = [];
        foreach ($userRoles as $key => $value) {
            $userRolesOptions[$key] = $value['name'];
        }

        foreach ($this->options as $key => $option) {
            if (is_array($this->options[$key])) {
                $this->options[$key]['value'] = get_option($option['id']);
            }
        }

        return $this->options;
    }


    public function custom_enrollment_process_add_options()
    {
        add_submenu_page(null, 'Custom enrollment process settings', '', 'activate_plugins', self::PLUGIN_PREFIX . 'settings', [$this, 'custom_enrollment_process_options_callback']);
    }


    public function custom_enrollment_process_options_callback()
    {
        ?>
        <div>
            <h1>Custom enrollment process</h1>
            <form action="options.php" method="POST" class="repeater">
                <?php
                settings_fields(self::PLUGIN_PREFIX . 'options');
                do_settings_sections(self::PLUGIN_PREFIX . 'settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function addOption($alias, $label, $type, $section = 1, $selectOptions = [], $value = '', $afterLabel = '')
    {
        $this->options[$alias] =
            [
                'id' => self::PLUGIN_PREFIX . $alias,
                'label' => $label,
                'type' => $type,
                'after_label' => $afterLabel,
                'value' => $value,
                'section' => $section,
                'options' => $selectOptions
            ];
    }

    public function custom_enrollment_process_register_settings()
    {
        foreach ($this->sections as $key => $value) {
            add_settings_section(self::PLUGIN_PREFIX . $key, $value, '', self::PLUGIN_PREFIX . 'settings');
        }

        foreach ($this->options as $key => $option) {

            if (is_array($this->options[$key])) {

                register_setting(self::PLUGIN_PREFIX . 'options', $option['id'], '');

                add_settings_field(
                    $option['id'],
                    (isset($option['label'])) ? $option['label'] : $key,
                    array($this, 'callback_for_' . $option['type']),
                    self::PLUGIN_PREFIX . 'settings',
                    self::PLUGIN_PREFIX . 'section_' . $option['section'],
                    array(
                        'id' => $option['id'],
                        'after_label' => $option['after_label'],
                        'options' => isset($option['options']) ? $option['options'] : []
                    )
                );

            }

        }
    }

    public function addPluginActionLinks($links, $file)
    {
        if ($file != CE_ProcessPlugin::PLUGIN_BASE_NAME) {
            return $links;
        }

        $settings_link = sprintf('<a href="%s">%s</a>', menu_page_url(self::PLUGIN_PREFIX . 'settings', false), 'Options');

        array_unshift($links, $settings_link);
        return $links;
    }


    /**
     * Callback for particular option # 1
     */
    public function callback_for_text($arg)
    {

        ?><input type="text" name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>"
                 value="<?php echo esc_attr(get_option($arg['id'])) ?>" size="40" /> <?php

        if ($arg['after_label']) {
            ?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }

    }

    public function callback_for_select($arg)
    {
        ?><select id="<?= $arg['id'] ?>" name="<?= $arg['id'] ?>">
        <?php foreach ($arg['options'] as $value => $label) {
            if (get_option($arg['id']) == $value) {
                echo "<option selected value='$value'>$label</option>";
            } else {
                echo "<option value='$value'>$label</option>";
            }
        }

        if ($arg['after_label']) {
            ?> <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }
        echo '</select>';
    }
}