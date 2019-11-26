<?php

namespace Phug\Util;

class Hasher
{
    /**
     * Input string to hash.
     *
     * @var string
     */
    protected $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Return a hashed print for the current input file or content.
     *
     * @return string
     */
    public function hash()
    {
        // Get the stronger hashing algorithm available to minimize collision risks
        $algorithms = hash_algos();
        $algorithm = $algorithms[0];
        $number = 0;

        foreach ($this->getMdAndShaAlgorithms($algorithms) as list($hashAlgorithm, $hashNumber)) {
            if ($hashNumber > $number) {
                $number = $hashNumber;
                $algorithm = $hashAlgorithm;
            }
        }

        return rtrim(strtr(base64_encode(hash($algorithm, $this->input, true)), '+/', '-_'), '=');
    }

    private function getMdAndShaAlgorithms($algorithms)
    {
        foreach ($algorithms as $hashAlgorithm) {
            $lettersLength = $this->getPrefixLength($hashAlgorithm);

            if ($lettersLength) {
                $hashNumber = substr($hashAlgorithm, $lettersLength);

                yield [$hashAlgorithm, $hashNumber];
            }
        }
    }

    private function getPrefixLength($hashAlgorithm)
    {
        return substr($hashAlgorithm, 0, 2) === 'md'
            ? 2
            : (substr($hashAlgorithm, 0, 3) === 'sha'
                ? 3
                : 0
            );
    }
}
