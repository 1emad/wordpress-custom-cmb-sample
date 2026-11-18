<?php
namespace Hamt\Components\CMB;

// Original save handler from Components/CMB/bootstrap.php.
// Depends on the full theme's field sanitizers and JSON helpers.
trait CMBSaveExcerpt {
    function save_custom_meta_box_data($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'update-post_' . $post_id)) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['home_section_layout'])) {
            $layout = $this->normalize_home_section_layout(wp_unslash($_POST['home_section_layout']));
            update_post_meta($post_id, 'home_section_layout', $layout);
        }

        foreach ($this->extract_files(CMB_PATH . 'posts') as $schema) {
            if (!is_array($schema)) {
                continue;
            }

            $clear_fields  = $this->explicit_clear_field_ids();
            $is_repeatable = !empty($schema['repeatable']);
            if ($is_repeatable) {
                if (empty($schema['id'])) {
                    continue;
                }

                $id = sanitize_key((string) $schema['id']);
                if (in_array($id, $clear_fields, true)) {
                    delete_post_meta($post_id, $id);
                    continue;
                }

                if (!isset($_POST[$id])) {
                    continue;
                }

                $rows = $this->decode_json_rows($_POST[$id]);
                if ($rows === null) {
                    continue;
                }

                $fields = isset($schema['options']) && is_array($schema['options']) ? $schema['options'] : [];
                $value  = $this->sanitize_json_rows($rows, $fields);

                // A valid non-empty payload is safe to persist. Empty rows are
                // preserved unless the UI sent the explicit clear marker above.
                if (!$this->is_empty_field_value($value)) {
                    update_post_meta($post_id, $id, $this->prepare_json_metadata_value($value));
                }
                continue;
            }

            $fields = isset($schema['options']) && is_array($schema['options']) ? $schema['options'] : [];
            foreach ($fields as $field) {
                if (!is_array($field) || empty($field['id'])) {
                    continue;
                }

                $id   = sanitize_key((string) $field['id']);
                $type = isset($field['type']) ? sanitize_key((string) $field['type']) : 'text';

                if ($type === 'group') {
                    $group_fields = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
                    $legacy_key = !empty($group_fields['legacy_meta_key'])
                        ? sanitize_key((string) $group_fields['legacy_meta_key'])
                        : '';

                    if (in_array($id, $clear_fields, true)) {
                        delete_post_meta($post_id, $id);
                        if ($legacy_key !== '') {
                            delete_post_meta($post_id, $legacy_key);
                        }
                        continue;
                    }

                    if (!isset($_POST[$id])) {
                        continue;
                    }

                    $rows = $this->decode_json_rows($_POST[$id]);
                    if ($rows === null) {
                        continue;
                    }

                    $value = $this->sanitize_json_rows($rows, $group_fields);
                    if (!$this->is_empty_field_value($value)) {
                        update_post_meta($post_id, $id, $this->prepare_json_metadata_value($value));
                        if ($legacy_key !== '') {
                            delete_post_meta($post_id, $legacy_key);
                        }
                    }
                    continue;
                }

                if (!isset($_POST[$id])) {
                    continue;
                }

                $value = $this->sanitize_field_value($field, wp_unslash($_POST[$id]));

                if ($this->is_empty_field_value($value)) {
                    if (!empty($field['preserve_empty'])) {
                        update_post_meta($post_id, $id, '');
                    } else {
                        delete_post_meta($post_id, $id);
                    }
                } else {
                    update_post_meta($post_id, $id, $value);
                }
            }
        }
    }
}
