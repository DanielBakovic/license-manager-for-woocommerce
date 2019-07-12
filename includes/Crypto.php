<?php

namespace LicenseManagerForWooCommerce;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto as DefuseCrypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce Crypto class.
 *
 * @version 1.0.1
 * @since 1.0.0
 */
class Crypto
{
    /**
     * The defuse key file name.
     *
     * @var   string
     * @since 1.0.0
     */
    const DEFUSE_FILE = 'defuse.txt';

    /**
     * The secret file name.
     *
     * @var   string
     * @since 1.0.0
     */
    const SECRET_FILE = 'secret.txt';

    /**
     * Folder name inside the wp_contents directory where the cryptographic secrets
     * are stored.
     * 
     * @var   string
     * @since 1.1.1
     */
    const PLUGIN_SLUG = 'lmfwc-files';

    /**
     * The defuse key file content.
     *
     * @var string
     */
    private $key_ascii;

    /**
     * The hashing key
     * 
     * @var string
     */
    private $key_secret;

    /**
     * Directory path to the plugin folder inside wp-content/uploads
     * 
     * @var string
     */
    private $uploads_dir;

    /**
     * Setup Constructor.
     */
    public function __construct()
    {
        $uploads = wp_upload_dir(null, false);

        $this->uploads_dir = $uploads['basedir'] . '/lmfwc-files/';
        $this->setDefuse();
        $this->setSecret();

        add_filter('lmfwc_encrypt', array($this, 'encrypt'), 10, 1);
        add_filter('lmfwc_decrypt', array($this, 'decrypt'), 10, 1);
        add_filter('lmfwc_hash',    array($this, 'hash'),    10, 1);
    }

    private function setDefuse()
    {
        /* When the cryptographic secrets are loaded into these constants, no other files are needed */
        if (defined('LMFWC_PLUGIN_DEFUSE')) {
            $this->key_ascii = LMFWC_PLUGIN_DEFUSE;
            return;
        }

        if (file_exists($this->uploads_dir . self::DEFUSE_FILE)) {
            $this->key_ascii = file_get_contents($this->uploads_dir . self::DEFUSE_FILE);
        }
    }

    private function setSecret()
    {
        /* When the cryptographic secrets are loaded into these constants, no other files are needed */
        if (defined('LMFWC_PLUGIN_SECRET')) {
            $this->key_secret = LMFWC_PLUGIN_SECRET;
            return;
        }

        if (file_exists($this->uploads_dir . self::SECRET_FILE)) {
            $this->key_secret = file_get_contents($this->uploads_dir . self::SECRET_FILE);
        }
    }

    /**
     * Load the defuse key from the plugin folder.
     *
     * @since 1.0.0
     *
     * @return string
     */
    private function loadEncryptionKeyFromConfig()
    {
        if (!$this->key_ascii) {
            return;
        }

        return Key::loadFromAsciiSafeString($this->key_ascii);
    }

    /**
     * Encrypt a string and return the encrypted cipher text.
     *
     * @since 1.0.0
     *
     * @param string $value - The text which will be encrypted.
     *
     * @return string
     */
    public function encrypt($value)
    {
        return DefuseCrypto::encrypt($value, $this->loadEncryptionKeyFromConfig());
    }

    /**
     * Decrypt a cipher and return the decrypted value.
     *
     * @since 1.0.0
     *
     * @param string $cipher - The cipher text which will be decrypted.
     *
     * @return string
     */
    public function decrypt($cipher)
    {
        if (!$cipher) {
            return '';
        }

        try {
            return DefuseCrypto::decrypt($cipher, $this->loadEncryptionKeyFromConfig());
        } catch (WrongKeyOrModifiedCiphertextException $ex) {
            // An attack! Either the wrong key was loaded, or the ciphertext has changed since it was created -- either
            // corrupted in the database or intentionally modified by someone trying to carry out an attack.
        }
    }

    public function hash($value)
    {
        return hash_hmac('sha256', $value, $this->key_secret);
    }
}