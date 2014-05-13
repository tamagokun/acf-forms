<?php

class ACFFormsUtil
{
    public static function print_field($field, $is_admin = false, $is_field = true)
    {
        $val = $is_field ? $field['value'] : $field;

        if ($is_field) {
            if ($field['type'] == "user") {
                if ($is_admin) {
                    return '<a href="user-edit.php?user_id=' . $val['ID'] . '">' . $val['display_name'] . '</a>';
                } else {
                    return $val['display_name'];
                }
            }

            if ($field['type'] == "relationship" && $field['return_format'] == "id") {
                foreach($val as $i => $post_id) $val[$i] = get_post($post_id);
            }

            if ($field['type'] == "taxonomy") {
                if ($field['return_format'] == "id") {
                    foreach($val as $i => $term_id) {
                        $val[$i] = get_term($term_id, $field['taxonomy']);
                    }
                }

                $output = array();
                foreach($val as $term) $output[] = $term->name;
                return implode(', ', $output);
            }
        }

        if (is_object($val)) {
            if (get_class($val) == "WP_Post") {
                return '<a href="' . $val->guid . '">' . $val->post_title . '</a>';
            }
        }

        if (is_array($val)) {
            $ouput = array();
            foreach($val as $subval) {
                $output[] = self::print_field($subval, $is_admin, false);
            }
            return implode(', ', $output);
        }

        return $val;
    }

}
