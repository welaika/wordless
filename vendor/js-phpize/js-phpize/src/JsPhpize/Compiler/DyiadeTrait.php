<?php

namespace JsPhpize\Compiler;

trait DyiadeTrait
{
    use DependenciesTrait;

    protected function compileLazyDyiade($helper, $leftHand, $rightHand)
    {
        $variables = array_map(function ($token) {
            return $token[1];
        }, array_filter(token_get_all('<?php ' . $rightHand), function ($token) {
            return is_array($token) &&
                $token[0] === T_VARIABLE &&
                !in_array($token[1], [
                    '$GLOBALS',
                    '$_SERVER',
                    '$_GET',
                    '$_POST',
                    '$_FILES',
                    '$_COOKIE',
                    '$_SESSION',
                    '$_REQUEST',
                    '$_ENV',
                ]);
        }));
        $variables = array_map('strval', $variables);
        $use = count($variables) ? ' use (&' . implode(', &', array_unique($variables)) . ')' : '';

        return $this->helperWrap($helper, [
            $leftHand,
            "function ()$use { return $rightHand; }",
        ]);
    }
}
