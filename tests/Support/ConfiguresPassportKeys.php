<?php

namespace Tests\Support;

trait ConfiguresPassportKeys
{
    private static array $oauthKeys = [];

    protected function configurePassportKeys(): void
    {
        if (self::$oauthKeys === []) {
            $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
            $windowsConfig = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
            if (is_file($windowsConfig)) {
                $options['config'] = $windowsConfig;
            }
            $key = openssl_pkey_new($options);
            openssl_pkey_export($key, $private, null, $options);
            self::$oauthKeys = ['private' => $private, 'public' => openssl_pkey_get_details($key)['key']];
        }
        config(['passport.private_key' => self::$oauthKeys['private'], 'passport.public_key' => self::$oauthKeys['public']]);
    }
}
