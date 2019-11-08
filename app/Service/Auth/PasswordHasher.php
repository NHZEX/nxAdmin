<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Service\Auth\Contracts\Hasher;
use RuntimeException;

/**
 * Class PasswordHasher
 * @package app\Service\Auth
 */
class PasswordHasher implements Hasher
{
    /**
     * The default cost factor.
     *
     * @var int
     */
    protected $rounds = 10;

    /**
     * Get information about the given hashed value.
     *
     * @param string $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    /**
     * Hash the given value.
     *
     * @param string $value
     * @param array  $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        $hash = password_hash($value, PASSWORD_DEFAULT, [
            'cost' => $this->cost($options),
        ]);

        if ($hash === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param string $value
     * @param string $hashedValue
     * @param array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param string $hashedValue
     * @param array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
    }

    /**
     * Set the default password work factor.
     *
     * @param  int  $rounds
     * @return $this
     */
    public function setRounds($rounds)
    {
        $this->rounds = (int) $rounds;

        return $this;
    }

    /**
     * Extract the cost value from the options array.
     *
     * @param  array  $options
     * @return int
     */
    protected function cost(array $options = [])
    {
        return $options['rounds'] ?? $this->rounds;
    }
}
