<?php
/**
 * Plugin Name: Earthin Security 2FA
 * Plugin URI: https://plugins.earthin.online/plugins/earthin-security-2fa
 * Description: Two-factor authentication (TOTP) security suite compatible with Google Authenticator, Authy, and 1Password with emergency scratch codes.
 * Version: 1.0.0
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * License: GPL-2.0-or-later
 * Text Domain: earthin-security-2fa
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Earthin_Security_2FA {
    private static ?Earthin_Security_2FA $instance = null;

    public static function get_instance(): Earthin_Security_2FA {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('show_user_profile', [$this, 'render_user_profile_fields']);
        add_action('edit_user_profile', [$this, 'render_user_profile_fields']);
        add_action('personal_options_update', [$this, 'save_user_profile_fields']);
        add_action('edit_user_profile_update', [$this, 'save_user_profile_fields']);
        
        // Login Hooks
        add_action('login_form', [$this, 'render_login_2fa_field']);
        add_filter('authenticate', [$this, 'verify_login_2fa'], 30, 3);
    }

    public static function generate_secret(int $length = 16): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    public static function base32_decode(string $b32): string {
        $b32 = strtoupper($b32);
        $lut = [
            'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7,
            'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15,
            'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29, '6' => 30, '7' => 31
        ];

        $buffer = 0;
        $bits_left = 0;
        $output = '';

        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $c = $b32[$i];
            if (!isset($lut[$c])) continue;

            $buffer = ($buffer << 5) | $lut[$c];
            $bits_left += 5;

            if ($bits_left >= 8) {
                $bits_left -= 8;
                $output .= chr(($buffer >> $bits_left) & 0xFF);
            }
        }

        return $output;
    }

    public static function verify_totp(string $secret, string $code): bool {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $secret_binary = self::base32_decode($secret);
        if (empty($secret_binary)) {
            return false;
        }

        $time_slice = floor(time() / 30);

        // Check current time slice +/- 1 (for clock drift)
        for ($offset = -1; $offset <= 1; $offset++) {
            $slice = $time_slice + $offset;
            $packed = pack('N*', 0) . pack('N*', $slice);
            $hash = hash_hmac('sha1', $packed, $secret_binary, true);
            $offset_idx = ord(substr($hash, -1)) & 0x0F;
            $truncated = unpack('N', substr($hash, $offset_idx, 4))[1] & 0x7FFFFFFF;
            $expected = str_pad((string)($truncated % 1000000), 6, '0', STR_PAD_LEFT);

            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    public function render_login_2fa_field(): void {
        ?>
        <p>
            <label for="earthin_2fa_code"><?php echo esc_html__('2FA Verification Code (if enabled)', 'earthin-security-2fa'); ?><br />
            <input type="text" name="earthin_2fa_code" id="earthin_2fa_code" class="input" value="" size="20" autocomplete="one-time-code" placeholder="6-digit code" />
            </label>
        </p>
        <?php
    }

    public function verify_login_2fa($user, string $username, string $password) {
        if ($user instanceof WP_User) {
            $enabled = get_user_meta($user->ID, '_earthin_2fa_enabled', true);
            if (!empty($enabled)) {
                $secret = get_user_meta($user->ID, '_earthin_2fa_secret', true);
                $code = isset($_POST['earthin_2fa_code']) ? sanitize_text_field(wp_unslash($_POST['earthin_2fa_code'])) : '';

                if (empty($code)) {
                    return new WP_Error('empty_2fa', __('<strong>ERROR</strong>: Please enter your 6-digit Two-Factor Authentication code.', 'earthin-security-2fa'));
                }

                // Check emergency backup codes
                $backup_codes = get_user_meta($user->ID, '_earthin_2fa_backup_codes', true) ?: [];
                if (in_array($code, $backup_codes, true)) {
                    // Consume backup code
                    $backup_codes = array_values(array_diff($backup_codes, [$code]));
                    update_user_meta($user->ID, '_earthin_2fa_backup_codes', $backup_codes);
                    return $user;
                }

                if (!self::verify_totp($secret, $code)) {
                    return new WP_Error('invalid_2fa', __('<strong>ERROR</strong>: Invalid Two-Factor Authentication code. Please try again.', 'earthin-security-2fa'));
                }
            }
        }

        return $user;
    }

    public function render_user_profile_fields(WP_User $user): void {
        $enabled = (bool) get_user_meta($user->ID, '_earthin_2fa_enabled', true);
        $secret = get_user_meta($user->ID, '_earthin_2fa_secret', true);
        if (empty($secret)) {
            $secret = self::generate_secret();
            update_user_meta($user->ID, '_earthin_2fa_pending_secret', $secret);
        } else {
            $secret = (string) $secret;
        }

        $otpauth_url = sprintf('otpauth://totp/EarthinSuite:%s?secret=%s&issuer=EarthinSuite', rawurlencode($user->user_email), $secret);
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode($otpauth_url);
        ?>
        <h2><?php echo esc_html__('Earthin Two-Factor Authentication (2FA)', 'earthin-security-2fa'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label><?php echo esc_html__('2FA Status', 'earthin-security-2fa'); ?></label></th>
                <td>
                    <?php if ($enabled) : ?>
                        <span style="color:#008a20;font-weight:bold;">✔ <?php echo esc_html__('Active & Enforced', 'earthin-security-2fa'); ?></span>
                        <p class="description">
                            <label>
                                <input type="checkbox" name="earthin_disable_2fa" value="1" />
                                <?php echo esc_html__('Check this box and save profile to disable 2FA for this account.', 'earthin-security-2fa'); ?>
                            </label>
                        </p>
                    <?php else : ?>
                        <span style="color:#888;">○ <?php echo esc_html__('Not Configured', 'earthin-security-2fa'); ?></span>
                        <div style="margin-top:15px;background:#f9f9f9;padding:15px;border:1px solid #ddd;border-radius:6px;max-width:420px;">
                            <p><strong>1. Scan with Google Authenticator or Authy:</strong></p>
                            <img src="<?php echo esc_url($qr_url); ?>" alt="2FA QR Code" width="160" height="160" style="display:block;margin:10px 0;" />
                            <p>Secret key: <code><?php echo esc_html($secret); ?></code></p>
                            <p><strong>2. Enter 6-digit code to confirm and activate:</strong></p>
                            <input type="text" name="earthin_verify_2fa_code" placeholder="123456" maxlength="6" class="regular-text" style="width:140px;" />
                            <input type="hidden" name="earthin_temp_secret" value="<?php echo esc_attr($secret); ?>" />
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_user_profile_fields(int $user_id): void {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        if (isset($_POST['earthin_disable_2fa']) && '1' === $_POST['earthin_disable_2fa']) {
            delete_user_meta($user_id, '_earthin_2fa_enabled');
            delete_user_meta($user_id, '_earthin_2fa_secret');
            delete_user_meta($user_id, '_earthin_2fa_backup_codes');
            return;
        }

        if (!empty($_POST['earthin_verify_2fa_code']) && !empty($_POST['earthin_temp_secret'])) {
            $secret = sanitize_text_field(wp_unslash($_POST['earthin_temp_secret']));
            $code = sanitize_text_field(wp_unslash($_POST['earthin_verify_2fa_code']));

            if (self::verify_totp($secret, $code)) {
                update_user_meta($user_id, '_earthin_2fa_enabled', 1);
                update_user_meta($user_id, '_earthin_2fa_secret', $secret);

                // Generate 5 emergency backup scratch codes
                $backup_codes = [];
                for ($i = 0; $i < 5; $i++) {
                    $backup_codes[] = sprintf('%06d', random_int(100000, 999999));
                }
                update_user_meta($user_id, '_earthin_2fa_backup_codes', $backup_codes);
            }
        }
    }
}

add_action('plugins_loaded', ['Earthin_Security_2FA', 'get_instance']);
