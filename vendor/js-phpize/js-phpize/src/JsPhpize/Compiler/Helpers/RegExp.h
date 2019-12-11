function ($value) {
    $chunks = strlen($value) ? explode(substr($value, 0, 1), $value) : [''];
    $flags = preg_replace('/[^imsxeADSUXJu]/', '', end($chunks));

    return (object) array(
        'isRegularExpression' => true,
        'flags' => $flags,
        'regExp' => rtrim($value, 'gimuy'),
    );
}
