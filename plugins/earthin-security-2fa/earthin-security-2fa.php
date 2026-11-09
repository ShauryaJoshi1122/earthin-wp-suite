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

        $backup_codes = get_user_meta($user->ID, '_earthin_2fa_backup_codes', true) ?: [];
        $otpauth_url = sprintf('otpauth://totp/EarthinSuite:%s?secret=%s&issuer=EarthinSuite', rawurlencode($user->user_email), $secret);
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode($otpauth_url);
        ?>
        <div class="earthin-card" style="background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:24px 28px; max-width:750px; margin:24px 0; box-shadow:0 1px 3px rgba(0,0,0,0.05); font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding-bottom:14px; margin-bottom:18px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:#059669; color:#fff; font-weight:800; font-size:12px; padding:4px 10px; border-radius:6px; letter-spacing:0.04em;">🌱 Earthin Security</span>
                    <h2 style="margin:0; font-size:18px; font-weight:700; color:#0f172a;"><?php echo esc_html__('Two-Factor Authentication (TOTP 2FA)', 'earthin-security-2fa'); ?></h2>
                </div>
                <?php if ($enabled) : ?>
                    <span style="background:#ecfdf5; color:#059669; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;">✔ <?php echo esc_html__('2FA Active & Enforced', 'earthin-security-2fa'); ?></span>
                <?php else : ?>
                    <span style="background:#f1f5f9; color:#64748b; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;">○ <?php echo esc_html__('Not Configured', 'earthin-security-2fa'); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($enabled) : ?>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px; margin-bottom:18px;">
                    <strong style="display:block; font-size:13px; color:#334155; margin-bottom:8px;"><?php echo esc_html__('Emergency Recovery Scratch Codes:', 'earthin-security-2fa'); ?></strong>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        <?php if (!empty($backup_codes) && is_array($backup_codes)) : ?>
                            <?php foreach ($backup_codes as $code) : ?>
                                <code style="background:#ffffff; border:1px solid #cbd5e1; padding:4px 10px; border-radius:6px; font-size:13px; font-weight:700; color:#0f172a;"><?php echo esc_html($code); ?></code>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span style="font-size:12px; color:#64748b;"><?php echo esc_html__('Standard TOTP active.', 'earthin-security-2fa'); ?></span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size:12px; color:#64748b; margin:8px 0 0;"><?php echo esc_html__('Store these emergency codes in a password manager. Each single-use code allows emergency login if you lose your phone.', 'earthin-security-2fa'); ?></p>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#ef4444; font-weight:600; cursor:pointer;">
                    <input type="checkbox" name="earthin_disable_2fa" value="1" />
                    <?php echo esc_html__('Check this box and click "Update Profile" below to revoke and disable 2FA.', 'earthin-security-2fa'); ?>
                </label>
            <?php else : ?>
                <div style="display:grid; grid-template-columns: 180px 1fr; gap:20px; align-items:start;">
                    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:10px; text-align:center;">
                        <img src="<?php echo esc_url($qr_url); ?>" alt="2FA QR Code" width="160" height="160" style="display:block; border-radius:6px;" />
                        <span style="display:block; font-size:11px; color:#64748b; margin-top:6px;"><?php echo esc_html__('Scan with Google Authenticator', 'earthin-security-2fa'); ?></span>
                    </div>

                    <div>
                        <div style="margin-bottom:14px;">
                            <strong style="display:block; font-size:13px; color:#0f172a;"><?php echo esc_html__('Manual Setup Secret Key:', 'earthin-security-2fa'); ?></strong>
                            <code style="background:#f1f5f9; padding:4px 10px; border-radius:6px; font-size:13px; font-weight:700; color:#059669; display:inline-block; margin-top:4px;"><?php echo esc_html($secret); ?></code>
                        </div>

                        <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">
                            <?php echo esc_html__('Verify 6-Digit Authenticator Code:', 'earthin-security-2fa'); ?>
                        </label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="earthin_verify_2fa_code" placeholder="123456" maxlength="6" style="border:1px solid #cbd5e1; border-radius:8px; padding:8px 12px; font-size:16px; font-weight:700; letter-spacing:0.1em; width:130px;" />
                            <input type="hidden" name="earthin_temp_secret" value="<?php echo esc_attr($secret); ?>" />
                            <span style="font-size:12px; color:#64748b;"><?php echo esc_html__('Enter code and save profile to activate.', 'earthin-security-2fa'); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('profile.php')) . '">' . __('Settings', 'earthin-security-2fa') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
