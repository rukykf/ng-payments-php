<?php


namespace Kofi\NgPayments\Traits;

trait AttributesTrait
{
    protected $attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function set($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    public function __call($method, $args)
    {
        if ((strpos($method, 'set') === 0) && ($method !== 'with')) {
            $name = substr($method, 3);
            $name = $this->convertToCase($name, @$args[1] ?? 'snake_case');
            return $this->set($name, $args[0]);
        }
        throw new \BadMethodCallException('Call to undefined function: ' . get_class($this) . '::' . $method);
    }

    public function __get($attribute)
    {
        if (array_key_exists($attribute, $this->attributes)) {
            return $this->attributes[$attribute];
        }
        trigger_error("Property $attribute doesn't exist and cannot be obtained", E_USER_ERROR);
    }

    public function __set($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    protected function convertToCase($input, $case)
    {
        if ($case == 'snake_case') {
            return $this->toSnakeCase($input);
        }

        if ($case == 'kebab-case') {
            return $this->toKebabCase($input);
        }

        if ($case == 'none') {
            return $input;
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
