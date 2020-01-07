<?php

namespace Metav\NgPayments\Tests\Traits;

use Metav\NgPayments\Traits\AttributesTrait;
use PHPUnit\Framework\TestCase;

class AttributesTraitTest extends TestCase
{

    private $testClass = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testClass = new class {
            use AttributesTrait;
        };
    }

    public function testWith()
    {
        $this->testClass->with('color', 'blue');
        $this->assertEquals('blue', $this->testClass->getAttributes()['color']);
    }

    public function testWithAttributes()
    {
        $attributes = [
            "color" => "blue",
            "type" => "grey"
        ];
        $this->testClass->withAttributes($attributes);
        $this->assertEquals($attributes, $this->testClass->getAttributes());
    }

    public function testWithMagicMethod()
    {
        $this->testClass->withCallbackUrl('example.com/callback');
        $this->testClass->withColorShade('dark', 'kebab-case');
        $this->testClass->withCustomerEmail('customer@email.com', 'snake_case');
        $this->testClass->withCustomerName('customer name', 'no-case');

        $attributes = $this->testClass->getAttributes();
        $this->assertEquals('example.com/callback', $attributes['callback_url']);
        $this->assertEquals('dark', $attributes['color-shade']);
        $this->assertEquals('customer@email.com', $attributes['customer_email']);
        $this->assertEquals('customer name', $attributes['CustomerName']);

        $this->expectException(\BadMethodCallException::class);
        $this->testClass->randomMagic();
    }
}
