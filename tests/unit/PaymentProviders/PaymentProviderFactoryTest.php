<?php

namespace Kofi\NgPayments\Tests\unit\PaymentProviders;

use Kofi\NgPayments\Exceptions\InvalidPaymentProviderConfigException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;
use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\PaymentProviders\Rave;
use PHPUnit\Framework\TestCase;

/**
 * These tests require complete environment isolation and I have not been able to get the environment variables
 * to work properly on TravisCI, but they work fine locally.
 *
 * @runTestsInSeparateProcesses
 * @group dev
 */
class PaymentProviderFactoryTest extends TestCase
{

    public function testGetPaymentProviderLoadsConfigFromConstants()
    {
        //define constants
        define('APP_ENV', 'local');
        define('PAYSTACK_SECRET_KEY', 'secret');
        define('PAYSTACK_PUBLIC_KEY', 'public');

        $payment_provider = PaymentProviderFactory::getPaymentProvider();
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
    }

    public function testGetPaymentProviderLoadsConfigFromEnv()
    {
        $_ENV['NG_PAYMENT_PROVIDER'] = 'paystack';
        $_ENV['APP_ENV'] = 'local';
        putenv('PAYSTACK_SECRET_KEY=secret');
        putenv('PAYSTACK_PUBLIC_KEY=public');

        $payment_provider = PaymentProviderFactory::getPaymentProvider();
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
    }

    public function testGetPaymentProviderLoadsConfigFromArray()
    {
        $config = [
            'provider' => 'paystack',
            'public_key' => 'public',
            'secret_key' => 'secret'
        ];

        $payment_provider = PaymentProviderFactory::getPaymentProvider($config);
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
    }

    public function testGetPaymentProviderLoadsConfigFromString()
    {
        putenv('PAYSTACK_SECRET_KEY=secret');
        putenv('PAYSTACK_PUBLIC_KEY=public');
        $payment_provider = PaymentProviderFactory::getPaymentProvider('paystack');
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
    }

    public function testGetPaymentProviderLoadsConfigFromCachedConfig()
    {
        PaymentProviderFactory::setPaymentProviderConfig([
            'public_key' => 'public',
            'secret_key' => 'secret',
            'provider' => 'rave'
        ]);
        $payment_provider = PaymentProviderFactory::getPaymentProvider();
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
        $this->assertInstanceOf(Rave::class, $payment_provider);
    }

    public function testGetPaymentProviderPrioritizesProviderSpecificConfig()
    {
        $_ENV['NG_PAYMENT_PROVIDER'] = 'paystack';
        putenv('PAYSTACK_SECRET_KEY=paystack_secret');
        putenv('PAYSTACK_PUBLIC_KEY=paystack_public');
        putenv('FLUTTERWAVE_SECRET_KEY=flutterwave_secret');
        putenv('FLUTTERWAVE_PUBLIC_KEY=flutterwave_public');

        $payment_provider = PaymentProviderFactory::getPaymentProvider();
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
        $this->assertEquals('paystack_secret', $payment_provider->getSecretKey());
        $this->assertEquals('paystack_public', $payment_provider->getPublicKey());
        $this->assertEquals('https://api.paystack.co', $payment_provider->getBaseUrl());

        $payment_provider = PaymentProviderFactory::getPaymentProvider('flutterwave');
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
        $this->assertEquals('flutterwave_secret', $payment_provider->getSecretKey());
        $this->assertEquals('flutterwave_public', $payment_provider->getPublicKey());
        $this->assertEquals('https://api.ravepay.co', $payment_provider->getBaseUrl());
    }

    public function testGetPaymentProviderThrowsExceptionWithInvalidConfig()
    {
        $this->expectException(InvalidPaymentProviderConfigException::class);
        $payment_provider = PaymentProviderFactory::getPaymentProvider('provider');
    }
}
