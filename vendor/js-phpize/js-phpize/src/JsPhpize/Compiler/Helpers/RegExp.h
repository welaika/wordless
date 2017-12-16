function ($value) {
    return (object) array(
        'isRegularExpression' => true,
        'regExp' => rtrim($value, 'gimuy'),
    );
}
