<?php

namespace Phug\Formatter\Partial;

trait AssignmentHelpersTrait
{
    /**
     * @return $this
     */
    protected function provideAttributeAssignments()
    {
        return $this->provideHelper('attribute_assignments', [
            'available_attribute_assignments',
            'get_helper',
            function ($availableAssignments, $getHelper) {
                return function (&$attributes, $name, $value) use ($availableAssignments, $getHelper) {
                    if (!in_array($name, $availableAssignments)) {
                        return $value;
                    }

                    $helper = $getHelper($name.'_attribute_assignment');

                    return $helper($attributes, $value);
                };
            },
        ]);
    }

    /**
     * @return $this
     */
    protected function provideAttributeAssignment()
    {
        return $this->provideHelper('attribute_assignment', [
            'attribute_assignments',
            function ($attributeAssignments) {
                return function (&$attributes, $name, $value) use ($attributeAssignments) {
                    if (isset($name) && $name !== '') {
                        $result = $attributeAssignments($attributes, $name, $value);
                        if (($result !== null && $result !== false && ($result !== '' || $name !== 'class'))) {
                            $attributes[$name] = $result;
                        }
                    }
                };
            },
        ]);
    }

    /**
     * @return $this
     */
    protected function provideStandAloneAttributeAssignment()
    {
        return $this->provideHelper('stand_alone_attribute_assignment', [
            'attribute_assignment',
            function ($attributeAssignment) {
                return function ($name, $value) use ($attributeAssignment) {
                    $attributes = [];
                    $attributeAssignment($attributes, $name, $value);

                    return $attributes[$name];
                };
            },
        ]);
    }

    /**
     * @return $this
     */
    protected function provideMergeAttributes()
    {
        return $this->provideHelper('merge_attributes', [
            'attribute_assignment',
            function ($attributeAssignment) {
                return function () use ($attributeAssignment) {
                    $attributes = [];
                    foreach (array_filter(func_get_args(), 'is_array') as $input) {
                        foreach ($input as $name => $value) {
                            $attributeAssignment($attributes, $name, $value);
                        }
                    }

                    return $attributes;
                };
            },
        ]);
    }

    /**
     * @return $this
     */
    protected function provideAttributesAssignment()
    {
        return $this
            ->registerHelper(
                'attributes_mapping',
                (array) $this->getOption('attributes_mapping') ?: []
            )
            ->provideHelper('attributes_assignment', [
                'attributes_mapping',
                'merge_attributes',
                'pattern',
                'pattern.html_text_escape',
                'pattern.attribute_pattern',
                'pattern.boolean_attribute_pattern',
                function ($attrMapping, $mergeAttr, $pattern, $escape, $attr, $bool) {
                    return function () use ($attrMapping, $mergeAttr, $pattern, $escape, $attr, $bool) {
                        $attributes = call_user_func_array($mergeAttr, func_get_args());
                        $code = '';
                        foreach ($attributes as $originalName => $value) {
                            if ($value !== null && $value !== false && ($value !== '' || $originalName !== 'class')) {
                                $name = isset($attrMapping[$originalName])
                                    ? $attrMapping[$originalName]
                                    : $originalName;
                                if ($value === true) {
                                    $code .= $pattern($bool, $name, $name);

                                    continue;
                                }
                                if (is_array($value) || is_object($value) &&
                                    !method_exists($value, '__toString')) {
                                    $value = json_encode($value);
                                }

                                $code .= $pattern($attr, $name, $value);
                            }
                        }

                        return $code;
                    };
                },
            ]);
    }

    /**
     * @return $this
     */
    protected function provideArrayEscape()
    {
        return $this
            ->provideHelper('array_escape', [
                'array_escape',
                'pattern.html_text_escape',
                function ($arrayEscape, $escape) {
                    return function ($name, $input) use ($arrayEscape, $escape) {
                        if (is_array($input) && in_array(strtolower($name), ['class', 'style'])) {
                            $result = [];
                            foreach ($input as $key => $value) {
                                $result[$escape($key)] = $arrayEscape($name, $value);
                            }

                            return $result;
                        }
                        if (is_array($input) || is_object($input) && !method_exists($input, '__toString')) {
                            return $escape(json_encode($input));
                        }
                        if (is_string($input)) {
                            return $escape($input);
                        }

                        return $input;
                    };
                },
            ]);
    }

    /**
     * @return $this
     */
    protected function provideClassAttributeAssignment()
    {
        return $this->addAttributeAssignment('class', function (&$attributes, $value) {
            $split = function ($input) {
                return preg_split('/(?<![\[\{\<\=\%])\s+(?![\]\}\>\=\%])/', strval($input));
            };
            $classes = isset($attributes['class']) ? array_filter($split($attributes['class'])) : [];
            foreach ((array) $value as $key => $input) {
                if (!is_string($input) && is_string($key)) {
                    if (!$input) {
                        continue;
                    }

                    $input = $key;
                }
                foreach ($split($input) as $class) {
                    if (!in_array($class, $classes)) {
                        $classes[] = $class;
                    }
                }
            }

            return implode(' ', $classes);
        });
    }

    /**
     * @return $this
     */
    protected function provideStyleAttributeAssignment()
    {
        return $this->addAttributeAssignment('style', function (&$attributes, $value) {
            if (is_string($value) && mb_substr($value, 0, 7) === '{&quot;') {
                $value = json_decode(htmlspecialchars_decode($value));
            }
            $styles = isset($attributes['style']) ? array_filter(explode(';', $attributes['style'])) : [];
            foreach ((array) $value as $propertyName => $propertyValue) {
                if (!is_int($propertyName)) {
                    $propertyValue = $propertyName.':'.$propertyValue;
                }
                $styles[] = $propertyValue;
            }

            return implode(';', $styles);
        });
    }

    /**
     * @return $this
     */
    protected function provideStandAloneClassAttributeAssignment()
    {
        return $this->provideHelper('stand_alone_class_attribute_assignment', [
            'class_attribute_assignment',
            function ($classAttributeAssignment) {
                return function ($value) use ($classAttributeAssignment) {
                    $attributes = [];

                    return $classAttributeAssignment($attributes, $value);
                };
            },
        ]);
    }

    /**
     * @return $this
     */
    protected function provideStandAloneStyleAttributeAssignment()
    {
        return $this->provideHelper('stand_alone_style_attribute_assignment', [
            'style_attribute_assignment',
            function ($styleAttributeAssignment) {
                return function ($value) use ($styleAttributeAssignment) {
                    $attributes = [];

                    return $styleAttributeAssignment($attributes, $value);
                };
            },
        ]);
    }
}
