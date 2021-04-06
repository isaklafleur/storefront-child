<?php


class MLMSoftWalletCoupons_Options
{
    const PLUGIN_PREFIX = 'mlmsoft_wallet_coupons';
    const TYPE_TEXT_FIELD = 'text';

    const MLMSOFT_WALLET_TYPE_ID_KEY = 'wallet_type_id';
    const MLMSOFT_WALLET_OPERATION_ID_KEY = 'wallet_operation_id';

    public $options = [];
    public $sections = [];

    public function __construct()
    {
        /*  Setting up actions... */
        add_action('admin_menu', array($this,  'add_options'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links', array($this, 'addPluginActionLinks'), 10, 2);

        $this->init();
    }

    /**
     * Get Wp options from Custom Settings page
     */
    public function init()
    {
        $this->sections = [
            'section_1' => 'Server options'
        ];

        $this->addOption(self::MLMSOFT_WALLET_TYPE_ID_KEY, 'Wallet type ID', self::TYPE_TEXT_FIELD);
        $this->addOption(self::MLMSOFT_WALLET_OPERATION_ID_KEY, 'Wallet operation ID', self::TYPE_TEXT_FIELD);

        foreach ($this->options as $key => $option) {
            if (is_array($this->options[$key])) {
                $this->options[$key]['value'] = get_option($option['id']);
            }
        }

        return $this->options;
    }

    public function get_option_value($key, $default = null)
    {
        if (isset($this->options[$key]) && !empty($this->options[$key]['value'])) {
            return $this->options[$key]['value'];
        } else {
            return $default;
        }
    }


    public function add_options()
    {
        add_submenu_page(null, 'MLMSoft wallet coupons options', '', 'activate_plugins', self::PLUGIN_PREFIX . 'settings', [$this, 'options_callback']);
    }


    public function options_callback()
    {
        ?>
        <div>
            <h1>MLMSoft wallet coupons</h1>
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

    public function register_settings()
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
        if ($file != MLMSoftWalletCoupons_Plugin::PLUGIN_BASE_NAME) {
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