<?php namespace Neoxia\LaravelOpensslEncryption;

use Illuminate\Encryption\DecryptException;

class Encrypter {

	/**
	 * The encryption key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The algorithm used for encryption.
	 *
	 * @var string
	 */
	protected $cipher = 'aes-256';

	/**
	 * The mode used for encryption.
	 *
	 * @var string
	 */
	protected $mode = 'cbc';

	/**
	 * The block size of the cipher.
	 *
	 * @var int
	 */
	protected $block = 16;	// 16 bytes (eg 128 bits) is the block size for the AES cipher

	/**
	 * Create a new encrypter instance.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __construct($key)
	{
		$this->key = $key;
	}

	/**
	 * Encrypt the given value.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function encrypt($value)
	{
		$iv = $this->generateInputVector();

		$value = $this->addPadding(serialize($value));
		$value = base64_encode($this->doEncrypt($value, $iv));

		// Once we have the encrypted value we will go ahead base64_encode the input
		// vector and create the MAC for the encrypted value so we can verify its
		// authenticity. Then, we'll JSON encode the data in a "payload" array.
		$iv = base64_encode($iv);

		$mac = $this->hash($value);

		return base64_encode(json_encode(compact('iv', 'value', 'mac')));
	}

	/**
	 * Generate an input vector.
	 *
	 * @return string
	 */
	protected function generateInputVector()
	{
		return openssl_random_pseudo_bytes($this->getIvSize());
	}

	/**
	 * Actually encrypt the value using the given Iv with the openssl library encrypt function.
	 *
	 * @param  string  $value
	 * @param  string  $iv
	 * @return string
	 */
	protected function doEncrypt($value, $iv)
	{
		return openssl_encrypt($value, $this->cipher."-".$this->mode, $this->key, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * Decrypt the given value.
	 *
	 * @param  string  $payload
	 * @return string
	 */
	public function decrypt($payload)
	{
		$payload = $this->getJsonPayload($payload);

		// We'll go ahead and remove the PKCS7 padding from the encrypted value before
		// we decrypt it. Once we have the de-padded value, we will grab the vector
		// and decrypt the data, passing back the unserialized from of the value.
		$value = base64_decode($payload['value']);

		$iv = base64_decode($payload['iv']);

		return unserialize($this->stripPadding($this->doDecrypt($value, $iv)));
	}

	/**
	 * Actually decrypt the value using the given Iv with the openssl library decrypt function.
	 *
	 * @param  string  $value
	 * @param  string  $iv
	 * @return string
	 */
	protected function doDecrypt($value, $iv)
	{
		return openssl_decrypt($value, $this->cipher."-".$this->mode, $this->key, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * Get the JSON array from the given payload.
	 *
	 * @param  string  $payload
	 * @return array
	 */
	protected function getJsonPayload($payload)
	{
		$payload = json_decode(base64_decode($payload), true);

		// If the payload is not valid JSON or does not have the proper keys set we will
		// assume it is invalid and bail out of the routine since we will not be able
		// to decrypt the given value. We'll also check the MAC for this encryption.
		if ( ! $payload or $this->invalidPayload($payload))
		{
			throw new DecryptException("Invalid data passed to encrypter.");
		}

		if ($payload['mac'] != $this->hash($payload['value']))
		{
			throw new DecryptException("MAC for payload is invalid.");
		}

		return $payload;
	}

	/**
	 * Create a MAC for the given value.
	 *
	 * @param  string  $value
	 * @return string  
	 */
	protected function hash($value)
	{
		return hash_hmac('sha256', $value, $this->key);
	}

	/**
	 * Add PKCS7 padding to a given value.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function addPadding($value)
	{
		$pad = $this->block - (strlen($value) % $this->block);

		return $value.str_repeat(chr($pad), $pad);
	}

	/**
	 * Remove the padding from the given value.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function stripPadding($value)
	{
		$pad = ord($value[($len = strlen($value)) - 1]);

		return $this->paddingIsValid($pad, $value) ? substr($value, 0, strlen($value) - $pad) : $value;
	}

	/**
	 * Determine if the given padding for a value is valid.
	 *
	 * @param  string  $pad
	 * @param  string  $value
	 * @return bool
	 */
	protected function paddingIsValid($pad, $value)
	{
		$beforePad = strlen($value) - $pad;

		return substr($value, $beforePad) == str_repeat(substr($value, -1), $pad);
	}

	/**
	 * Verify that the encryption payload is valid.
	 *
	 * @param  array  $data
	 * @return bool
	 */
	protected function invalidPayload(array $data)
	{
		return ! isset($data['iv']) or ! isset($data['value']) or ! isset($data['mac']);
	}

	/**
	 * Get the IV size for the cipher.
	 *
	 * @return int
	 */
	protected function getIvSize()
	{
		return openssl_cipher_iv_length($this->cipher."-".$this->mode);
	}

	/**
	 * Set the encryption key.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * Set the encryption cipher.
	 *
	 * @param  string  $cipher
	 * @return void
	 */
	public function setCipher($cipher)
	{
		$this->cipher = $cipher;
	}

	/**
	 * Set the encryption mode.
	 *
	 * @param  string  $mode
	 * @return void
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}

}