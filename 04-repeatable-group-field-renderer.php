<?php
namespace Hamt\Components\Fields;

// Original group() method from Components/Fields/bootstrap.php.
// Additional field-type methods and supporting JS are in the full theme.
trait CMBGroupRendererExcerpt {
    function group($id, $name, $value, $title, $fields) {
        $add_button_label = isset($fields['add_button_label']) && trim((string) $fields['add_button_label']) !== ''
            ? (string) $fields['add_button_label']
            : 'Add new item';
        if (is_rtl()) {
            $add_button_label = isset($fields['add_button_label_rtl']) && trim((string) $fields['add_button_label_rtl']) !== ''
                ? (string) $fields['add_button_label_rtl']
                : 'إضافة عنصر جديد';
        }

        $rows = $value ? json_decode($value, true) : [];
        if (!is_array($rows)) {
            $rows = [];
        }
        echo '<div class="group-holder">';
        echo '<textarea hidden name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" class="group"' . $this->field_data_key($name) . '>' . esc_textarea($value) . '</textarea>';
        echo '<span class="wid-title">' . esc_html($title) . '</span>';
        echo '<div class="group-fields">';

        if (empty($rows)) {
            $rows = [[]];
        }

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $first_id = !empty($fields[0]['id']) ? $fields[0]['id'] : '';
            $summary = $first_id !== '' && isset($row[$first_id]) ? $row[$first_id] : '';

            echo '<div class="box">';
            echo '<div class="flex-between"><span><num>' . esc_html($index + 1) . '</num>' . esc_html($summary) . '</span><i class="fa-solid fa-arrow-left"></i></div>';
            echo '<div class="all-fields">';

            foreach ($fields as $field) {
                if (!is_array($field) || empty($field['type']) || empty($field['id'])) {
                    continue;
                }
                $method = sanitize_key($field['type']);
                if (!method_exists($this, $method)) {
                    continue;
                }
                $field_id = (string) $field['id'];
                $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
                $field_value = isset($row[$field_id]) ? $row[$field_id] : '';
                $this->$method($field_id . '_' . $index, $field_id, $field_value, isset($field['title']) ? $field['title'] : '', $options);
            }

            echo '<button type="button" class="fa-solid fa-xmark remove-box" aria-label="' . esc_attr(is_rtl() ? 'حذف العنصر' : 'Remove item') . '"></button>';
            echo '</div></div>';
        }

        echo '</div>';
        echo '<button type="button" class="add-more"><i class="fa-solid fa-plus"></i> ' . esc_html($add_button_label) . '</button>';
        echo '</div>';
    }
}
