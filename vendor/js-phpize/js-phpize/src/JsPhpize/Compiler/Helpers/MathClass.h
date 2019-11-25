isset($Math) ? $Math : ($Math = [
    'DEG_PER_RAD' => M_PI / 180,
    'E' => M_E,
    'LN2' => M_LN2,
    'LN10' => M_LN10,
    'LOG2E' => M_LOG2E,
    'LOG10E' => M_LOG10E,
    'PI' => M_PI,
    'RAD_PER_DEG' => 180 / M_PI,
    'SQRT1_2' => M_SQRT1_2,
    'SQRT2' => M_SQRT2,

    'abs' => 'abs',
    'acos' => 'acos',
    'acosh' => 'acosh',
    'asin' => 'asin',
    'asinh' => 'asinh',
    'atan' => 'atan',
    'atan2' => 'atan2',
    'atanh' => 'atanh',
    'cbrt' => function ($value) {
        return pow($value, 1 / 3);
    },
    'ceil' => 'ceil',
    'clamp' => function ($value, $min, $max) {
        return max($min, min($value, $max));
    },
    'clz32' => function ($value) {
        if ($value === -INF) {
            return 32;
        }
        if ($value < 0 || ($value |= 0) < 0) {
            return 0;
        }
        return 32 - ceil(log(1 + $value, 2));
    },
    'cos' => 'cos',
    'cosh' => 'cosh',
    'degrees' => 'rad2deg',
    'exp' => 'exp',
    'expm1' => 'expm1',
    'floor' => 'floor',
    'fround' => function ($value) {
        return unpack('f', pack('f', $value))[1];
    },
    'fscale' => function ($value, $inLow, $inHigh, $outLow, $outHigh) {
        if (in_array(NAN, [$value, $inLow, $inHigh, $outLow, $outHigh])) {
            return NAN;
        }
        return unpack('f', pack('f', (
            $value === INF || $value === -INF
                ? $value
                : (($value - $inLow) * ($inHigh - $outLow) / ($inHigh - $inLow) + $outLow)
        )))[1];
    },
    'hypot' => function ($value1, $value2) {
        return sqrt(array_sum(array_map(function ($number) {
            return $number * $number;
        }, func_get_args())));
    },
    'iaddh' => '',
    'imul' => function ($x, $y) {
        return ($x | 0) * ($y | 0) | 0;
    },
    'imulh' => function ($u, $v) {
        $urShift = function ($n, $s) {
            return ($n >= 0) ? ($n >> $s) :
                (($n & 0x7fffffff) >> $s) |
                (0x40000000 >> ($s - 1));
        };
        $UINT16 = 0xffff;
        $u = +$u;
        $v = +$v;
        $u0 = $u & $UINT16;
        $v0 = $v & $UINT16;
        $u1 = $u >> 16;
        $v1 = $v >> 16;
        $t = ($u1 * ($urShift($v0, 1) << 1)) + $urShift($u0 * $v0, 16);

        return $u1 * $v1 + ($t >> 16) + (($urShift($u0 * $v1, 1) << 1) + ($t & $UINT16) >> 16);
    },
    'isubh' => function ($x0, $x1, $y0, $y1) {
        $urShift = function ($n, $s) {
            return ($n >= 0) ? ($n >> $s) :
                (($n & 0x7fffffff) >> $s) |
                (0x40000000 >> ($s - 1));
        };
        $x0 = $urShift($x0, 1) << 1;
        $x1 = $urShift($x1, 1) << 1;
        $y0 = $urShift($y0, 1) << 1;

        return $x1 - ($urShift($y1, 1) << 1) - $urShift((~$x0 & $y0 | ~($x0 ^ $y0) & $x0 - ($urShift($y0, 1) << 1)), 31) | 0;
    },
    'log' => 'log',
    'log1p' => function ($value) {
        return log(1 + $value);
    },
    'log2' => function ($value) {
        return log($value, 2);
    },
    'log10' => 'log10',
    'max' => 'max',
    'min' => 'min',
    'pow' => 'pow',
    'radians' => 'deg2rad',
    'random' => function () {
        return mt_rand() / (mt_getrandmax() + 1);
    },
    'round' => 'round',
    'scale' => function ($value, $inLow, $inHigh, $outLow, $outHigh) {
        if (in_array(NAN, [$value, $inLow, $inHigh, $outLow, $outHigh])) {
            return NAN;
        }
        if ($value === INF || $value === -INF) {
            return $value;
        }
        return ($value - $inLow) * ($inHigh - $outLow) / ($inHigh - $inLow) + $outLow;
    },
    'sign' => function ($value) {
        return ($value > 0 ? 1 : ($value < 0 ? -1 : 0));
    },
    'signbit' => function ($value) {
        return $value >= 0;
    },
    'sin' => 'sin',
    'sinh' => 'sinh',
    'sqrt' => 'sqrt',
    'tan' => 'tan',
    'tanh' => 'tanh',
    'trunc' => function ($value) {
        return $value >= 0 ? floor($value) : ceil($value);
    },
    'umulh' => function ($u, $v) {
        $urShift = function ($n, $s) {
            return ($n >= 0) ? ($n >> $s) :
                (($n & 0x7fffffff) >> $s) |
                (0x40000000 >> ($s - 1));
        };
        $UINT16 = 0xffff;
        $u = +$u;
        $v = +$v;
        $u0 = $u & $UINT16;
        $v0 = $v & $UINT16;
        $u1 = $urShift($u, 16);
        $v1 = $urShift($v, 16);
        $t = ($u1 * ($urShift($v0, 1) << 1)) + $urShift($u0 * $v0, 16);

        return $u1 * $v1 + $urShift($t, 16) + $urShift(($urShift($u0 * $v1, 1) << 1) + ($t & $UINT16), 16);
    },
 ])
