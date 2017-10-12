<?php

namespace SmartCAT\Drupal\Helpers;

class Cryptographer {

  private static function get_salt() {
    return drupal_get_private_key();
  }

  private static function get_IV() {
    $iv_len = openssl_cipher_iv_length('AES-256-CBC');
    $key = hash('sha512', drupal_get_hash_salt(), PASSWORD_DEFAULT);

    return substr($key, -$iv_len);
  }

  static function encrypt($text) {
    return openssl_encrypt($text, 'AES-256-CBC', self::get_salt(), 0, self::get_IV());
  }

  static function decrypt($text) {
    return openssl_decrypt($text, 'AES-256-CBC', self::get_salt(), 0, self::get_IV());
  }
}