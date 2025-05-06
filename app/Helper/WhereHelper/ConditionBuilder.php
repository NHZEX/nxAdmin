<?php

declare(strict_types=1);

namespace app\Helper\WhereHelper;

use Closure;
use InvalidArgumentException;
use think\helper\Str;

class ConditionBuilder
{
    protected ?Closure $convert = null;
    protected ?Closure $beforeValid = null;
    protected ?Closure $afterValid = null;

    protected array $inputParams;
    protected bool $autoCamel = true;
    protected bool $allowZero = true;

    private function __construct(
        protected string|Closure $field,
        /**
         * @var string[]|Closure(ConditionBuilder $builder): (string|null)
         */
        protected array|Closure|null $inputKey,
        protected ?string $operator,
        protected ?string $default,
    ) {
    }

    /**
     * @param string|Closure(string $value, string $key, ConditionBuilder $builder): mixed $field
     * @param string|string[]|Closure(ConditionBuilder $builder): (string|null) $inputKey
     */
    public static function make(
        string|Closure $field,
        string|array|Closure|null $inputKey,
        ?string $operator = null,
        ?string $default = null,
    ): self {
        if ($field instanceof Closure && null === $inputKey) {
            throw new InvalidArgumentException('inputKey must not be null when field is Closure');
        }

        return new self(
            field: $field,
            inputKey: \is_string($inputKey) ? [$inputKey] : $inputKey,
            operator: $operator,
            default: $default
        );
    }

    public function setAutoCamel(bool $autoCamel): self
    {
        $this->autoCamel = $autoCamel;

        return $this;
    }

    public function allowZero(bool $enable = true): static
    {
        $this->allowZero = $enable;

        return $this;
    }

    /**
     * @param Closure(mixed $value, string $key, ConditionBuilder $builder): mixed $convert
     */
    public function convert(Closure $convert): self
    {
        $this->convert = $convert;

        return $this;
    }

    /**
     * @param ?Closure(mixed $value, string $key, ConditionBuilder $builder): bool $before
     * @param ?Closure(mixed $value, string $key, ConditionBuilder $builder): bool $after
     */
    public function test(?Closure $before = null, ?Closure $after = null): self
    {
        $this->beforeValid = $before;
        $this->afterValid = $after;

        return $this;
    }

    public function getInputParams(): array
    {
        return $this->inputParams;
    }

    protected function extractValue(array $params): mixed
    {
        $this->inputParams = $params;

        if (null === $this->inputKey && !($this->field instanceof Closure)) {
            if ($this->autoCamel) {
                $inputKey = [Str::camel($this->field)];
            } else {
                $inputKey = [$this->field];
            }
        } elseif ($this->inputKey instanceof Closure) {
            $resultKey = \call_user_func($this->inputKey, $this);
            if (null === $resultKey) {
                return null;
            }
            $inputKey = [$resultKey];
        } else {
            $inputKey = $this->inputKey;
        }

        foreach ($inputKey as $key) {
            if (!\array_key_exists($key, $params)) {
                if (null === $this->default) {
                    continue;
                } else {
                    $value = $this->default;
                }
            } else {
                if (null === $params[$key] && null !== $this->default) {
                    $value = $this->default;
                } else {
                    $value = $params[$key];
                }
            }

            if (null !== $this->beforeValid) {
                if (true !== \call_user_func($this->beforeValid, $value, $key, $this)) {
                    continue;
                }
            } elseif ($this->allowZero) {
                if (!(0 === $value || '0' === $value) && empty($value)) {
                    continue;
                }
            } elseif (empty($value)) {
                continue;
            }

            if (null !== $this->convert) {
                $value = \call_user_func($this->convert, $value, $key, $this);
            }

            if (null !== $this->afterValid) {
                if (true !== \call_user_func($this->afterValid, $value, $key, $this)) {
                    continue;
                }
            } elseif ($this->allowZero) {
                if (!(0 === $value || '0' === $value) && empty($value)) {
                    continue;
                }
            } elseif (empty($value)) {
                continue;
            }

            if ($this->field instanceof Closure) {
                $value = \call_user_func($this->field, $value, $key, $this);
                if (null === $value) {
                    continue;
                }

                return $value;
            }
            if (null === $this->operator) {
                return [$this->field, $value];
            } else {
                return [$this->field, $this->operator, $value];
            }
        }

        return null;
    }

    public function __invoke(array $input): mixed
    {
        return $this->extractValue($input);
    }
}
