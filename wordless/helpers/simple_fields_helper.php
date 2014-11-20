<?php

/**
* Awaiting for documentation
*
* @deprecated Dismissing support for simple field helper after adoption
*             of Advanced Custom Field as default custom fileds creation
*             plug-in
*
* @ingroup helperclass
*/
class SimpleFieldsHelper {

    /**
    * @deprecated Dismissing support for simple field helper after adoption
    *             of Advanced Custom Field as default custom fileds creation
    *             plug-in
    */
    function simple_fields_metas($post) {
        $connector = simple_fields_get_all_fields_and_values_for_post($post->ID);
        $metas = array();
        $groups = $connector["field_groups"];
        if (!$groups) return array();
        foreach ($groups as $group) {
        $metas[$group["name"]] = array();
        $fields = $group["fields"];
        foreach ($fields as $field) {
            if (sizeof($field["saved_values"]) == 1)
                $metas[$group["name"]][$field["name"]] = $field["saved_values"][0];
            else
                $metas[$group["name"]][$field["name"]] = $field["saved_values"];
        }
    }
    return $metas;
    }

    /**
    * @deprecated Dismissing support for simple field helper after adoption
    *             of Advanced Custom Field as default custom fileds creation
    *             plug-in
    */
    function simple_fields_meta($post, $group, $field) {
        $metas = simple_fields_metas($post);
        return $metas[$group][$field];
    }
}

Wordless::register_helper("SimpleFieldsHelper");
