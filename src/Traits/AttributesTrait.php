<?php


namespace Metav\NgPayments\Traits;

trait AttributesTrait
{
    protected $attributes = [];

    public function withAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function with($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    public function __call($method, $args)
    {
        if ((strpos($method, 'with') === 0) && ($method !== 'with')) {
            $name = substr($method, 4);
            $name = $this->convertToCase($name, @$args[1] ?? 'snake_case');
            return $this->with($name, $args[0]);
        }
        throw new \BadMethodCallException('Call to undefined function: ' . get_class($this) . '::' . $method);
    }

    protected function convertToCase($input, $case)
    {
        if ($case == 'snake_case') {
            return $this->toSnakeCase($input);
        }

        if ($case == 'kebab-case') {
            return $this->toKebabCase($input);
        }

        return $input;
    }

    protected function toSnakeCase($input)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input)), '_');
    }

    protected function toKebabCase($input)
    {
        return strtolower(preg_replace('%([a-z])([A-Z])%', '\1-\2', $input));
    }
}
