<?php


class GeoRedirect_OptionsBase
{
    private $pluginPrefix;
    private $optionsTitle;
    private $pluginBaseName;

    /** @var GeoRedirectOptionItem[] */
    private $options;

    private $sections;

    private $tabs;

    const TYPE_TEXT_FIELD = 'text';
    const TYPE_SELECT_FIELD = 'select';
    const TYPE_TABLE = 'table';
    const TYPE_CUSTOM_ACTION = 'custom_action';

    public function __construct($pluginPrefix, $optionsTitle, $pluginBaseName)
    {
        $this->pluginPrefix = $pluginPrefix;
        $this->optionsTitle = $optionsTitle;
        $this->pluginBaseName = $pluginBaseName;

        $this->sections = [];
        $this->tabs = [];

        add_action('admin_menu', array($this, 'add_options'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links', array($this, 'addPluginActionLinks'), 10, 2);
        add_action('admin_post_' . $this->pluginPrefix . 'custom_action', [$this, 'custom_action_handler']);
    }

    public function _init()
    {
        foreach ($this->options as $key => $option) {
            $this->initRecursive($option);
        }
    }

    /**
     * @param $option GeoRedirectOptionItem
     */
    protected function initRecursive($option) {
        $option->value = get_option($option->id);
        if (isset($option->payload['tableData'])) {
            foreach ($option->payload['tableData']['optionRows'] as $row) {
                foreach ($row as $opt) {
                    $this->initRecursive($opt);
                }
            }
        }
    }

    public function get_option_value($key, $default = null)
    {
        $id = $this->pluginPrefix . $key;
        $val = get_option($id);
        if ($val) {
            return $val;
        } else {
            return $default;
        }
    }

    public function add_options()
    {
        add_submenu_page(null, $this->optionsTitle, '', 'activate_plugins', $this->pluginPrefix . 'settings', [$this, 'options_callback']);
    }

    public function options_callback()
    {
        ?>
        <div>
            <h1><?php echo $this->optionsTitle ?></h1>
            <form action="options.php" method="POST" class="repeater">
                <?php
                settings_fields($this->pluginPrefix . 'options');
                do_settings_sections($this->pluginPrefix . 'settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function custom_action_handler()
    {
        $optionId = $_GET['option'];
        if (!$optionId) {
            return;
        }
        foreach ($this->options as $option) {
            $this->customActionRecursive($option, $optionId);
        }
        wp_redirect('/wp-admin/admin.php?page=' . $this->pluginPrefix . 'settings');
    }

    /**
     * @param $option GeoRedirectOptionItem
     * @param $id
     */
    protected function customActionRecursive($option, $id) {
        if ($option->id == $id && $option->payload['callback']) {
            call_user_func($option->payload['callback'], $option);
            return;
        }
        if (isset($option->payload['tableData'])) {
            foreach ($option->payload['tableData']['optionRows'] as $row) {
                foreach ($row as $opt) {
                    $this->customActionRecursive($opt, $id);
                }
            }
        }
    }

    protected function addOption($alias, $label, $type, $section, $selectOptions = [], $value = '', $afterLabel = '')
    {
        $this->options[$alias] = new GeoRedirectOptionItem([
            'id' => $this->pluginPrefix . $alias,
            'label' => $label,
            'type' => $type,
            'afterLabel' => $afterLabel,
            'value' => $value,
            'section' => $section,
            'selectOptions' => $selectOptions
        ]);
    }

    /**
     * @param $option GeoRedirectOptionItem
     */
    protected function addOptionRaw($option)
    {
        $alias = $option->id;
        $this->addPluginPrefixRecursive($option);
        $this->options[$alias] = $option;
    }

    /**
     * @param $option GeoRedirectOptionItem
     */
    protected function addPluginPrefixRecursive(&$option)
    {
        $option->id = $this->pluginPrefix . $option->id;
        if (isset($option->payload['tableData'])) {
            foreach ($option->payload['tableData']['optionRows'] as $row) {
                foreach ($row as $opt) {
                    $this->addPluginPrefixRecursive($opt);
                }
            }
        }
    }

    protected function addSection($alias, $title)
    {
        $this->sections[$alias] = $title;
    }

    public function register_settings()
    {
        foreach ($this->sections as $key => $value) {
            add_settings_section($this->pluginPrefix . $key, $value, '', $this->pluginPrefix . 'settings');
        }

        foreach ($this->options as $key => $option) {
            register_setting($this->pluginPrefix . 'options', $option->id, '');

            add_settings_field(
                $option->id,
                isset($option->label) ? $option->label : $key,
                array($this, 'callback_for_' . $option->type),
                $this->pluginPrefix . 'settings',
                $this->pluginPrefix . $option->section,
                [$option]
            );
            if (isset($option->payload['tableData'])) {
                foreach ($option->payload['tableData']['optionRows'] as $row) {
                    foreach ($row as $opt) {
                        $this->registerSettingsRecursive($opt);
                    }
                }
            }
        }
    }

    /**
     * @param $option GeoRedirectOptionItem
     */
    protected function registerSettingsRecursive($option)
    {
        register_setting($this->pluginPrefix . 'options', $option->id, '');
        add_settings_field(
            $option->id,
            $option->label,
            null,
            $this->pluginPrefix . 'settings',
            $this->pluginPrefix . $option->section,
            [$option]
        );
        if (isset($option->payload['tableData'])) {
            foreach ($option->payload['tableData']['optionRows'] as $row) {
                foreach ($row as $opt) {
                    $this->registerSettingsRecursive($opt);
                }
            }
        }
    }

    public function addPluginActionLinks($links, $file)
    {
        if ($file != $this->pluginBaseName) {
            return $links;
        }

        $settings_link = sprintf('<a href="%s">%s</a>', menu_page_url($this->pluginPrefix . 'settings', false), 'Options');

        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * @param $option GeoRedirectOptionItem[]
     */
    public function callback_for_text($option)
    {
        $option = $option[0];
        $val = esc_attr(get_option($option->id));
        echo "<input type='text' name='$option->id' id='$option->id' value='$val' size='40'/>";

        if ($option->afterLabel) {
            echo "<span class='after_label'>$option->afterLabel</span>";
        }
    }

    /**
     * @param $option GeoRedirectOptionItem[]
     */
    public function callback_for_select($option)
    {
        $option = $option[0];
        echo "<select id='$option->id' name='$option->id'>";

        foreach ($option->payload['selectOptions'] as $value => $label) {
            if (get_option($option->id) == $value) {
                echo "<option selected value='$value'>$label</option>";
            } else {
                echo "<option value='$value'>$label</option>";
            }
        }

        if ($option->afterLabel) {
            echo "<span class='after_label'>$option->afterLabel</span>";
        }
        echo '</select>';
    }

    /**
     * @param $option GeoRedirectOptionItem[]
     */
    public function callback_for_table($option)
    {
        $option = $option[0];
        echo '<table><thead><tr>';
        $head = $option->payload['tableData']['head'];
        foreach ($head as $item) {
            echo "<th>$item</th>";
        }
        $optionRows = $option->payload['tableData']['optionRows'];
        echo '</tr></thead>';
        echo '<tbody>';
        foreach ($optionRows as $row) {
            echo '<tr>';
            /** @var GeoRedirectOptionItem $opt */
            foreach ($row as $opt) {
                $callback = 'callback_for_' . $opt->type;
                if (method_exists($this, $callback)) {
                    echo '<td>';
                    $this->$callback([$opt]);
                    echo '</td>';
                }
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * @param $option GeoRedirectOptionItem[]
     */
    public function callback_for_custom_action($option)
    {
        $option = $option[0];
        echo '<a href="' . get_admin_url() . 'admin-post.php?action=' . $this->pluginPrefix . 'custom_action&option=' . $option->id . '">' . $option->label . '</a>';
    }
}