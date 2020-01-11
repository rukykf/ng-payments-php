<?php


namespace Kofi\NgPayments\PaymentProviders;

use Kofi\NgPayments\Exceptions\InvalidPaymentProviderConfigException;
use Kofi\NgPayments\PaymentProviders\Base\AbstractPaymentProvider;
use ReflectionClass;

class PaymentProviderFactory
{
    protected static $cachedConfig = [];

    protected static $httpExceptions = false;

    protected static $transactionExceptions = true;

    protected static $paymentProviders = [
        'paystack' => Paystack::class,
        'flutterwave' => Rave::class,
        'rave' => Rave::class
    ];

    public static function getPaymentProvider($paymentProviderConfig = []): AbstractPaymentProvider
    {
        if ($paymentProviderConfig instanceof AbstractPaymentProvider) {
            return $paymentProviderConfig;
        }

        if (is_array($paymentProviderConfig)) {
            return self::getPaymentProviderInstanceFromConfig($paymentProviderConfig);
        }

        if (is_string($paymentProviderConfig)) {
            return self::getPaymentProviderInstanceFromConfig(['provider' => strtolower($paymentProviderConfig)]);
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
        self::$cachedConfig = $config;
    }

    protected static function getPaymentProviderInstanceFromConfig($config_array = [])
    {
        $config_array = self::getValidConfig($config_array);
        $provider = $config_array['provider'];
        $public_key = $config_array['public_key'];
        $secret_key = $config_array['secret_key'];
        $app_env = $config_array['app_env'];
        $error_config = [
            "http_exceptions" => self::$httpExceptions,
            "transaction_exceptions" => self::$transactionExceptions
        ];
        $provider_class = new ReflectionClass(self::$paymentProviders[$provider]);
        return $provider_class->newInstance($public_key, $secret_key, $app_env, $error_config);
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
            ?? self::$cachedConfig['provider']
            ?? self::getConstant("METAV_PAYMENT_PROVIDER")
            ?? self::getEnv("METAV_PAYMENT_PROVIDER")
            ?? 'paystack';

        $config['app_env'] = $config_array['app_env']
            ?? self::$cachedConfig['app_env']
            ?? self::getConstant("METAV_APP_ENV")
            ?? self::getConstant("APP_ENV")
            ?? self::getEnv("METAV_APP_ENV")
            ?? self::getEnv("APP_ENV")
            ?? 'production';

        $provider_public = strtoupper($config['provider']) . "_PUBLIC_KEY";
        $config['public_key'] = $config_array['public_key']
            ?? self::$cachedConfig['public_key']
            ?? self::getConstant($provider_public)
            ?? self::getConstant("METAV_" . $provider_public)
            ?? self::getEnv($provider_public)
            ?? self::getEnv("METAV_" . $provider_public)
            ?? self::getConstant("METAV_PUBLIC_KEY")
            ?? self::getEnv("METAV_PUBLIC_KEY");

        $provider_secret = strtoupper($config['provider']) . "_SECRET_KEY";
        $config['secret_key'] = $config_array['secret_key']
            ?? self::$cachedConfig['secret_key']
            ?? self::getConstant($provider_secret)
            ?? self::getConstant("METAV_" . $provider_secret)
            ?? self::getEnv($provider_secret)
            ?? self::getEnv("METAV_" . $provider_secret)
            ?? self::getConstant("METAV_SECRET_KEY")
            ?? self::getEnv("METAV_SECRET_KEY");

        if (self::isValidConfig($config) == false) {
            throw new InvalidPaymentProviderConfigException("Required configuration values are not defined");
        }

        return $config;
    }
}
