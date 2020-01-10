<?php

namespace Metav\NgPayments\Tests\unit\PaymentProviders;

use Metav\NgPayments\Exceptions\InvalidPaymentProviderConfigException;
use Metav\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;
use Metav\NgPayments\PaymentProviders\PaymentProviderFactory;
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
        define('METAV_APP_ENV', 'local');
        define('METAV_SECRET_KEY', 'secret');
        define('METAV_PUBLIC_KEY', 'public');

        $payment_provider = PaymentProviderFactory::getPaymentProvider();
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
    }

    public function testGetPaymentProviderLoadsConfigFromEnv()
    {
        $_ENV['METAV_PAYMENT_PROVIDER'] = 'paystack';
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
        putenv('METAV_PAYSTACK_SECRET_KEY=secret');
        putenv('METAV_PAYSTACK_PUBLIC_KEY=public');
        $payment_provider = PaymentProviderFactory::getPaymentProvider('paystack');
        $this->assertInstanceOf(AbstractPaymentProvider::class, $payment_provider);
    }

    public function testGetPaymentProviderPrioritizesProviderSpecificConfig()
    {
        define('METAV_PUBLIC_KEY', 'public');
        $_ENV['METAV_PAYMENT_PROVIDER'] = 'paystack';
        $_ENV['METAV_APP_ENV'] = 'local';
        $_ENV['METAV_SECRET_KEY'] = 'secret';
        putenv('METAV_PAYSTACK_SECRET_KEY=paystack_secret');
        putenv('PAYSTACK_PUBLIC_KEY=paystack_public');
        putenv('METAV_FLUTTERWAVE_SECRET_KEY=flutterwave_secret');
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
