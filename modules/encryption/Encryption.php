<?php
/**
 * Encryption Module for Trongate v2
 * * Provides AES-128-GCM authenticated encryption for sensitive data.
 * Requires configuration in config/encryption.php
 */
class Encryption extends Trongate {

    /**
     * @var string The encryption key loaded from config
     */
    private string $key = '';

    /**
     * @var string The cipher algorithm to use
     */
    private string $cipher = 'aes-128-gcm';

    /**
     * @var int OpenSSL options
     */
    private int $options = 0;

    /**
     * Constructor - loads encryption key from config file.
     */
    public function __construct(?string $module_name = null) {
        parent::__construct($module_name);

        $config_path = APPPATH . 'config/encryption.php';
        if (file_exists($config_path)) {
            require_once $config_path;
            if (defined('ENCRYPTION_KEY') && ENCRYPTION_KEY !== '') {
                $this->key = ENCRYPTION_KEY;
            }
        }
    }

    /**
     * Generate a cryptographically secure hex key for use with this module.
     * Only accessible in development environment.
     * * Usage: Visit /encryption/make_key in your browser (dev mode only)
     */
    public function make_key(): void {
        if (ENV !== 'dev') {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        // Generate 16 bytes (128 bits) and convert to hex (32 chars)
        $key = bin2hex(openssl_random_pseudo_bytes(16));

        echo '<h3>Your New Encryption Key</h3>';
        echo '<p>Copy this key and paste it into <code>config/encryption.php</code>:</p>';
        echo '<pre style="background:#f4f4f4; padding:1em; border:1px solid #ccc; border-radius:4px; font-family:monospace;">' . $key . '</pre>';
        echo '<p><strong>Warning:</strong> Keep this key secret. If you lose it, any data encrypted with it is gone forever.</p>';
    }

    /**
     * Ensure the encryption key has been configured.
     */
    private function ensure_key_configured(): void {
        if ($this->key === '') {
            trigger_error('Encryption key not configured. Please check config/encryption.php', E_USER_ERROR);
        }
    }

    /**
     * Encrypt a plaintext string using AES-128-GCM.
     * * Output format: [IV (24 hex chars)][Auth Tag (32 hex chars)][Ciphertext (base64)]
     * * @param string $plaintext The string to encrypt
     * @return string The encrypted string
     */
    public function encrypt($plaintext): string {
        block_url('encryption/encrypt');

        // Gracefully handle empty or non-string inputs
        if (!is_string($plaintext) || $plaintext === '') {
            return '';
        }

        $this->ensure_key_configured();

        $iv_length = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $tag = ''; // Passed by reference to openssl_encrypt

        $ciphertext = openssl_encrypt(
            $plaintext,
            $this->cipher,
            $this->key,
            $this->options,
            $iv,
            $tag
        );

        // Concatenate hex-encoded IV, hex-encoded Tag, and the ciphertext
        return bin2hex($iv) . bin2hex($tag) . $ciphertext;
    }

    /**
     * Decrypt an encrypted string using AES-128-GCM.
     * * @param string $encrypted_string The string to decrypt
     * @return string|false The decrypted plaintext, or false on failure
     */
    public function decrypt(string $encrypted_string): string|false {
        block_url('encryption/decrypt');

        // Minimum length check: IV (24) + Tag (32) = 56 chars
        if (strlen($encrypted_string) < 56) {
            return false;
        }

        $this->ensure_key_configured();

        // Extract the components based on fixed hex lengths
        $iv = substr($encrypted_string, 0, 24);
        $tag = substr($encrypted_string, 24, 32);
        $ciphertext = substr($encrypted_string, 56);

        $result = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $this->key,
            $this->options,
            hex2bin($iv),
            hex2bin($tag)
        );

        return $result;
    }

}