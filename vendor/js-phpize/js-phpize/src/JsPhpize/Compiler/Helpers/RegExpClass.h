RegExp::class; class RegExp {
    private $regExp = '';

    public function __construct($regExp) {
        $this->regExp = $regExp;
    }

    public function __toString() {
        return '/' . str_replace('/', '\\/', $this->regExp) . '/';
    }
}
