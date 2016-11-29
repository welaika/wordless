function ($base) {
    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? (isset($base[$key]) ? $base[$key] : null)
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : null
                        )
                    )
                )
                : null
            );
    }

    return $base;
}
