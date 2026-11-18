<?php
namespace Hamt\Components\CMB;

// Original methods extracted from Components/CMB/bootstrap.php.
// Part of a larger trait: supporting methods live in the full theme.
trait CMBRegistrationExcerpt {
    function extract_files($directory) {
        $files = [];
        if (!is_dir($directory)) {
            return $files;
        }

        $paths = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && strtolower($item->getExtension()) === 'php') {
                $paths[] = $item->getPathname();
            }
        }

        sort($paths, SORT_STRING);

        foreach ($paths as $file) {
            ob_start();
            $result = include $file;
            ob_end_clean();

            if (is_array($result) && !empty($result)) {
                $files[] = $result;
            }
        }

        return $files;
    }

    function custom_meta_box() {
     
        $directory = CMB_PATH . 'posts';
        $files = $this->extract_files($directory);
        
        // Ensure $files is always an array, never false
        if (!is_array($files)) {
            $files = [];
        }

        $home_components_by_box = [];
        foreach ($this->get_home_component_registry() as $component_key => $component) {
            $home_components_by_box[$component['box_id']] = $component_key;
        }

        if (!empty($files)) {
            foreach ($files as $el) {
                // Ensure $el is an array
                if (!is_array($el)) {
                    continue;
                }
                
                $fields = isset($el['options']) && is_array($el['options']) ? $el['options'] : [];
                $callback = isset($el['callback']) ? $el['callback'] : '';
                $is_repeatable = isset($el['repeatable']) && $el['repeatable'] === true;
                $component_key = isset($el['id'], $home_components_by_box[$el['id']]) ? $home_components_by_box[$el['id']] : '';

                if ($is_repeatable) {
                    if (!isset($el['id']) || !isset($el['title']) || !isset($el['ptype']) || !isset($el['context']) || !isset($el['priority'])) {
                        continue;
                    }
                    $templates = isset($el['templates']) && is_array($el['templates']) ? $el['templates'] : self::parse_templates_from_id($el['id']);
                    $ptypes   = is_array($el['ptype']) ? $el['ptype'] : [$el['ptype']];
                    foreach ($ptypes as $pt) {
                        $screen = $pt === 'page' ? 'page' : $pt;
                        add_filter('postbox_classes_' . $screen . '_' . $el['id'], function ($classes) use ($templates, $component_key) {
                            foreach ($templates as $t) {
                                $classes[] = 'hamt-template-' . sanitize_html_class($t);
                            }
                            if ($component_key !== '') {
                                $classes[] = 'hamt-home-component';
                                $classes[] = 'hamt-home-component-' . sanitize_html_class($component_key);
                            }
                            return array_values(array_unique($classes));
                        }, 10, 1);
                    }
                    add_meta_box(
                        $el['id'],
                        $el['title'],
                        function ($post) use ($fields, $el) {
                            $meta_box_id = $el['id'];
                            $value = get_post_meta($post->ID, $meta_box_id, true);
                            $meta_boxes = [];
                            if (!empty($value)) {
                                $decoded = json_decode($value, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $meta_boxes = $decoded;
                                }
                            }
                            echo '<div class="repeatable-meta-box" data-id="' . esc_attr($meta_box_id) . '">';
                            echo '<textarea style="display:none" name="' . esc_attr($meta_box_id) . '" id="' . esc_attr($meta_box_id) . '" class="repeatable-meta-box-data">' . esc_textarea($value ?: '') . '</textarea>';
                            echo '<div class="meta-boxes">';

                            if (!empty($meta_boxes) && is_array($meta_boxes)) {
                                foreach ($meta_boxes as $index => $meta_box_data) {
                                    if (!is_array($meta_box_data)) {
                                        continue;
                                    }
                                    echo '<div class="meta-box">';
                                    echo '<div class="flex-between">';
                                    $first_field_id = !empty($fields) && isset($fields[0]['id']) ? $fields[0]['id'] : '';
                                    echo '<span><num>' . ($index + 1) . '</num>' . (isset($meta_box_data[$first_field_id]) ? esc_html($meta_box_data[$first_field_id]) : '') . '</span>';
                                    echo '<i class="fa-solid fa-arrow-left"></i>';
                                    echo '</div>';
                                    echo '<div class="all-fields">';
                                    if (!empty($fields) && is_array($fields)) {
                                        foreach ($fields as $fd) {
                                            if (!is_array($fd) || !isset($fd['type']) || !isset($fd['id'])) {
                                                continue;
                                            }
                                            $method = $fd['type'];
                                            if (!method_exists($this, $method)) {
                                                continue;
                                            }
                                            $options = isset($fd['options']) && is_array($fd['options']) ? $fd['options'] : [];
                                            $field_value = isset($meta_box_data[$fd['id']]) ? $meta_box_data[$fd['id']] : '';
                                            $title = isset($fd['title']) ? $fd['title'] : '';
                                            $this->$method($fd['id'] . '_' . $index, $fd['id'], $field_value, $title, $options);
                                        }
                                    }
                                    echo '<button type="button" class="fa-solid fa-xmark remove-meta-box" aria-label="' . esc_attr(is_rtl() ? 'حذف المكون' : 'Remove component') . '"></button>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="meta-box">';
                                echo '<div class="flex-between">';
                                echo '<span>' . (is_rtl() ? 'المكون' : 'component') . '</span>';
                                echo '<i class="fa-solid fa-arrow-left"></i>';
                                echo '</div>';
                                echo '<div class="all-fields">';
                                if (!empty($fields) && is_array($fields)) {
                                    foreach ($fields as $fd) {
                                        if (!is_array($fd) || !isset($fd['type']) || !isset($fd['id'])) {
                                            continue;
                                        }
                                        $method = $fd['type'];
                                        if (!method_exists($this, $method)) {
                                            continue;
                                        }
                                        $options = isset($fd['options']) && is_array($fd['options']) ? $fd['options'] : [];
                                        $title = isset($fd['title']) ? $fd['title'] : '';
                                        $this->$method($fd['id'] . '_0', $fd['id'], '', $title, $options);
                                    }
                                }
                                echo '<button type="button" class="fa-solid fa-xmark remove-meta-box" aria-label="' . esc_attr(is_rtl() ? 'حذف المكون' : 'Remove component') . '"></button>';
                                echo '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '<div class="add-more-meta-box">';
                            echo '<i class="fa-solid fa-plus"></i>';
                            echo is_rtl() ? 'إضافة صندوق جديد' : 'Add new meta box';
                            echo '</div>';
                            echo '</div>';
                        },
                        is_array($el['ptype']) ? $el['ptype'] : [$el['ptype']],
                        $el['context'],
                        $el['priority']
                    );
                } else {
                    if (!isset($el['id']) || !isset($el['title']) || !isset($el['ptype']) || !isset($el['context']) || !isset($el['priority'])) {
                        continue;
                    }
                    $templates = isset($el['templates']) && is_array($el['templates']) ? $el['templates'] : self::parse_templates_from_id($el['id']);
                    $ptypes   = is_array($el['ptype']) ? $el['ptype'] : [$el['ptype']];
                    foreach ($ptypes as $pt) {
                        $screen = $pt === 'page' ? 'page' : $pt;
                        add_filter('postbox_classes_' . $screen . '_' . $el['id'], function ($classes) use ($templates, $component_key) {
                            foreach ($templates as $t) {
                                $classes[] = 'hamt-template-' . sanitize_html_class($t);
                            }
                            if ($component_key !== '') {
                                $classes[] = 'hamt-home-component';
                                $classes[] = 'hamt-home-component-' . sanitize_html_class($component_key);
                            }
                            return array_values(array_unique($classes));
                        }, 10, 1);
                    }
                    add_meta_box(
                        $el['id'],
                        $el['title'],
                        function ($post) use ($fields, $callback) {
                            if (!empty($fields) && is_array($fields)) {
                                foreach ($fields as $fd) {
                                    if (!is_array($fd) || !isset($fd['type']) || !isset($fd['id'])) {
                                        continue;
                                    }
                                    $method = $fd['type'];
                                    if (!method_exists($this, $method)) {
                                        continue;
                                    }
                                    $value = get_post_meta($post->ID, $fd['id'], 1);
                                    $has_saved_value = function_exists('metadata_exists')
                                        ? metadata_exists('post', $post->ID, $fd['id'])
                                        : $value !== '';
                                    $options = isset($fd['options']) && is_array($fd['options']) ? $fd['options'] : [];

                                    if (!$has_saved_value && $method === 'group' && !empty($options['legacy_meta_key'])) {
                                        $legacy_key = sanitize_key((string) $options['legacy_meta_key']);
                                        $legacy_value = $legacy_key !== '' ? get_post_meta($post->ID, $legacy_key, true) : '';
                                        if ($legacy_value !== '') {
                                            $legacy_field_id = '';
                                            foreach ($options as $child_field) {
                                                if (is_array($child_field) && !empty($child_field['id'])) {
                                                    $legacy_field_id = sanitize_key((string) $child_field['id']);
                                                    break;
                                                }
                                            }
                                            if ($legacy_field_id !== '') {
                                                $value = wp_json_encode([[$legacy_field_id => (string) $legacy_value]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                            }
                                        }
                                    } elseif (!$has_saved_value && array_key_exists('default', $fd)) {
                                        $value = $fd['default'];
                                    }

                                    $title = isset($fd['title']) ? $fd['title'] : '';
                                    $this->$method($fd['id'], $fd['id'], $value, $title, $options);
                                }
                            } else {
                                $callback_path = is_string($callback) ? realpath($callback) : '';
                                $cmb_base      = realpath(CMB_PATH);
                                if ($callback_path && $cmb_base && file_exists($callback_path) && strpos($callback_path, $cmb_base) === 0) {
                                    require $callback_path;
                                }
                            }
                        },
                        is_array($el['ptype']) ? $el['ptype'] : [$el['ptype']],
                        $el['context'],
                        $el['priority']
                    );
                }
            }
        }
    }
}
