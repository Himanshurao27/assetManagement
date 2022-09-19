<?php
namespace Framework;

use Defuse\Crypto\Crypto;

/**
 * This is a class which hashes the string for storing in the database and
 * also considering the Timing Leaks
 * Reference: https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
 *
 * Dependencies ------> php-mcrypt, php-xml
 */
class Security {
	protected static $_algos = ['sha256', 'sha384'];

	protected static function _verifyAlgo($algo) {
		if (!in_array($algo, self::$_algos)) {
			throw new \Exception("Invalid Second argument algo");
		}
	}

	public static function generateToken($length = 20) {
		return bin2hex(random_bytes($length));
	}

	public static function hashStr($str, $algo = 'sha384') {
		self::_verifyAlgo($algo);

		return password_hash(
		    base64_encode(
		        hash($algo, $str, true)
		    ),
		    PASSWORD_DEFAULT
		);
	}

	public static function verifyHash($hashStr, $plainStr, $algo = 'sha384') {
		self::_verifyAlgo($algo);

		return password_verify(
		    base64_encode(
		        hash($algo, $plainStr, true)
		    ),
		    $hashStr
		);
	}

	public static function encrypt($data, $key) {
		// $e = new Security\Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		// $hashed = $e->encrypt($data, $key);
		
		// return utf8_encode($hashed);
		return Crypto::encryptWithPassword($data, $key);
	}

	public static function decrypt($data, $key) {
		// $data = utf8_decode($data);
		// $e = new Security\Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		// $normal = $e->decrypt($data, $key);

		// return $normal;
		return Crypto::decryptWithPassword($data, $key);
	}

	public static function generateUUID() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		    // 32 bits for "time_low"
		    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

		    // 16 bits for "time_mid"
		    mt_rand( 0, 0xffff ),

		    // 16 bits for "time_hi_and_version",
		    // four most significant bits holds version number 4
		    mt_rand( 0, 0x0fff ) | 0x4000,

		    // 16 bits, 8 bits for "clk_seq_hi_res",
		    // 8 bits for "clk_seq_low",
		    // two most significant bits holds zero and one for variant DCE1.1
		    mt_rand( 0, 0x3fff ) | 0x8000,

		    // 48 bits for "node"
		    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	public static function aesEncrypt($plaintext, $secret) {
		$hashKey = openssl_digest($secret, 'SHA256', FALSE);
		$key = substr($hashKey,0,32);
		$ivLen = openssl_cipher_iv_length("AES-256-CBC");
		$iv = openssl_random_pseudo_bytes($ivLen);
		$cipherTextRaw = openssl_encrypt($plaintext, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $cipherTextRaw, $key, TRUE);
		$cipherEncoded = base64_encode($cipherTextRaw);
		$ivEncoded = base64_encode($iv);
		$hmacEncoded = base64_encode($hmac);
		return $ivEncoded.$hmacEncoded.$cipherEncoded;
	}

	public static function aesDecrypt($data, $secret) {
		$hashKey = openssl_digest($secret, 'SHA256', FALSE);
		$key = substr($hashKey,0,32);
		$dataDecoded = base64_decode($data);
		$ivLen = openssl_cipher_iv_length("AES-256-CBC");
		$iv = substr($dataDecoded, 0, $ivLen);
		$cipherTextRaw = base64_decode(substr($data,68));
		$originalPlainText = openssl_decrypt($cipherTextRaw, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
		return $originalPlainText;
	}
}
