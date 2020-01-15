<?php

namespace Kofi\NgPayments\Tests\unit\Traits;

use Kofi\NgPayments\Traits\AttributesTrait;
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


    public function testSet()
    {
        $this->testClass->set('color', 'blue');
        $this->assertEquals('blue', $this->testClass->getAttributes()['color']);
    }

    public function testSetAttributes()
    {
        $attributes = [
            "color" => "blue",
            "type" => "grey"
        ];
        $this->testClass->setAttributes($attributes);
        $this->assertEquals($attributes, $this->testClass->getAttributes());
    }

    public function testSetMagicMethod()
    {
        $this->testClass->setCallbackUrl('example.com/callback');
        $this->testClass->setColorShade('dark', 'kebab-case');
        $this->testClass->setMealType('a meal', 'none');
        $this->testClass->setCustomerEmail('customer@email.com', 'snake_case');
        $this->testClass->setCustomerName('customer name', 'no-case');

        $attributes = $this->testClass->getAttributes();
        $this->assertEquals('example.com/callback', $attributes['callback_url']);
        $this->assertEquals('dark', $attributes['color-shade']);
        $this->assertEquals('a meal', $attributes['MealType']);
        $this->assertEquals('customer@email.com', $attributes['customer_email']);
        $this->assertEquals('customer name', $attributes['CustomerName']);

        $this->expectException(\BadMethodCallException::class);
        $this->testClass->randomMagicMethod();
    }

    public function testMagicSettersAndGetters()
    {
        $this->testClass->id = 00000;
        $this->testClass->callback_url = 'example.com/callback';

        $this->assertEquals(00000, $this->testClass->getAttributes()['id']);
        $this->assertEquals('example.com/callback', $this->testClass->getAttributes()['callback_url']);

        $this->testClass->setAttributes(['color_shade' => 'dark', 'customer_email' => 'customer@email.com']);
        $this->assertEquals('dark', $this->testClass->color_shade);
        $this->assertEquals('customer@email.com', $this->testClass->customer_email);
    }
}
