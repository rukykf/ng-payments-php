<?php


namespace Kofi\NgPayments\PaymentProviders;

use Kofi\NgPayments\Exceptions\InvalidPaymentProviderConfigException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;
use ReflectionClass;

class PaymentProviderFactory
{
    protected static $config = null;

    protected static $httpExceptions = false;

    protected static $transactionExceptions = true;

    protected static $paymentProviders = [
        'paystack' => Paystack::class,
        'flutterwave' => Rave::class,
        'rave' => Rave::class
    ];

    public static function getPaymentProvider($payment_provider_config = []): AbstractPaymentProvider
    {
        if ($payment_provider_config instanceof AbstractPaymentProvider) {
            return $payment_provider_config;
        }

        if (is_array($payment_provider_config)) {
            return self::getPaymentProviderInstanceFromConfig($payment_provider_config);
        }

        if (is_string($payment_provider_config)) {
            return self::getPaymentProviderInstanceFromConfig(['provider' => strtolower($payment_provider_config)]);
        }

        throw new InvalidPaymentProviderConfigException();
    }

    public static function enableHttpExceptions()
    {
        self::$httpExceptions = true;
    }

    public static function disableHttpExceptions()
    {
        self::$httpExceptions = false;
    }

    public static function enableTransactionExceptions()
    {
        self::$transactionExceptions = true;
    }

    public static function disableTransactionExceptions()
    {
        self::$transactionExceptions = false;
    }

    public static function setPaymentProviderConfig(array $config)
    {
        self::$config = $config;
    }

    protected static function getPaymentProviderInstanceFromConfig($config = [])
    {
        if (self::$config !== null && empty($config)) {
            $config = self::$config;
        }

        $config = self::getValidConfig($config);
        $provider = $config['provider'];
        $public_key = $config['public_key'];
        $secret_key = $config['secret_key'];
        $app_env = $config['app_env'];
        $error_config = [
            "http_exceptions" => self::$httpExceptions,
            "transaction_exceptions" => self::$transactionExceptions
        ];
        $provider_class = new ReflectionClass(self::$paymentProviders[$provider]);
        $provider_instance = $provider_class->newInstance($public_key, $secret_key, $app_env);
        $provider_instance->setErrorConfig($error_config);
        return $provider_instance;
    }

    protected static function isValidConfig($config)
    {
        if ($config['provider'] == null || $config['app_env'] == null || $config['public_key'] == null
            || $config['secret_key'] == null) {
            return false;
        }

        if (!isset(self::$paymentProviders[$config['provider']])) {
            return false;
        }

        return true;
    }

    protected static function getConstant($constant_name)
    {
        return @constant($constant_name) ?? null;
    }

    protected static function getEnv($param)
    {
        $val = @$_ENV[$param] ?? getenv($param);
        return $val ? $val : null;
    }

    protected static function getValidConfig($config_array = [])
    {
        $config['provider'] = $config_array['provider']
            ?? self::$config['provider']
            ?? self::getConstant("NG_PAYMENT_PROVIDER")
            ?? self::getEnv("NG_PAYMENT_PROVIDER")
            ?? 'paystack';

        $config['app_env'] = $config_array['app_env']
            ?? self::$config['app_env']
            ?? self::getConstant("APP_ENV")
            ?? self::getEnv("APP_ENV")
            ?? 'production';

        $provider_public = strtoupper($config['provider']) . "_PUBLIC_KEY";
        $config['public_key'] = $config_array['public_key']
            ?? self::$config['public_key']
            ?? self::getConstant($provider_public)
            ?? self::getEnv($provider_public);

        $provider_secret = strtoupper($config['provider']) . "_SECRET_KEY";
        $config['secret_key'] = $config_array['secret_key']
            ?? self::$config['secret_key']
            ?? self::getConstant($provider_secret)
            ?? self::getEnv($provider_secret);

        if (self::isValidConfig($config) == false) {
            throw new InvalidPaymentProviderConfigException("Required configuration values are not defined");
        }

        return $config;
    }
}
