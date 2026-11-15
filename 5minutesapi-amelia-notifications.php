<?php
/**
 * Plugin Name: 5MinutesAPI Notifications for Amelia Booking
 * Plugin URI: https://5minutesapi.com
 * Description: Automate appointment confirmations and reminders for Amelia Booking using 5MinutesAPI. The most cost-effective Twilio alternative.
 * Version: 1.0.0
 * Author: 5MinutesAPI
 * Author URI: https://5minutesapi.com
 * Text Domain: 5minutesapi-amelia-notifications
 */

if (!defined('ABSPATH')) {
    exit; // Security Check
}

// 1. Create the Settings Menu (Traffic & Money Trap)
add_action('admin_menu', 'fivemin_amelia_menu');
function fivemin_amelia_menu() {
    add_options_page('5MinutesAPI Amelia', '5MinutesAPI Amelia', 'manage_options', 'fivemin-amelia-alerts', 'fivemin_amelia_settings_page');
}

function fivemin_amelia_settings_page() {
    ?>
    <div class="wrap" style="background:#fff; padding:30px; border:1px solid #e2e8f0; border-radius:12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h1 style="color:#0f172a; font-weight:900; font-size:24px;">🚀 5MinutesAPI Notifications for Amelia Booking</h1>
        <p style="font-size:15px; color:#475569; margin-bottom:20px;">Stop no-shows. Connect Amelia to our high-speed Official Meta API Gateway instantly.</p>
        
        <div style="background:#f8fafc; border-left:4px solid #25D366; padding:20px; margin-bottom:25px; border-radius:4px;">
            <strong style="color:#0f172a;">Step 1:</strong> Get your Master API Key from <a href="https://5minutesapi.com/dashboard" target="_blank" style="color:#10a37f; font-weight:bold; text-decoration:none;">5MinutesAPI.com</a> (Zero Markup API).<br><br>
            <strong style="color:#0f172a;">Step 2:</strong> Ensure you have an approved Meta template named <code>amelia_booking_alert</code> in your 5MinutesAPI dashboard.
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('fivemin_amelia_settings_group'); ?>
            <?php do_settings_sections('fivemin_amelia_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                <th scope="row" style="font-weight:bold; color:#0f172a;">Master API Key</th>
                <td>
                    <input type="password" name="fivemin_api_key" value="<?php echo esc_attr(get_option('fivemin_api_key')); ?>" style="width:100%; max-width:450px; padding:10px; border-radius:6px; border:1px solid #cbd5e1;" placeholder="Paste your 5m_live_... key here" />
                    <p class="description" style="margin-top:8px;">Your key connects securely to the 5MinutesAPI high-speed delivery engine.</p>
                </td>
                </tr>
            </table>
            <?php submit_button('Save & Activate Alerts', 'primary', 'submit', true, ['style' => 'background:#0f172a; color:#fff; border:none; padding:10px 24px; border-radius:6px; font-weight:bold; cursor:pointer;']); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'fivemin_amelia_register_settings');
function fivemin_amelia_register_settings() {
    register_setting('fivemin_amelia_settings_group', 'fivemin_api_key');
}

// 2. Hook into Amelia Booking to Send Alert
add_action('AmeliaBookingAdded', 'fivemin_send_api_amelia_alert', 10, 2);

function fivemin_send_api_amelia_alert($reservationObject, $bookingData) {
    $api_key = get_option('fivemin_api_key');
    if (empty($api_key)) return;

    $customer_phone = isset($bookingData['customer']['phone']) ? $bookingData['customer']['phone'] : '';
    $customer_name = isset($bookingData['customer']['firstName']) ? $bookingData['customer']['firstName'] : 'Customer';
    $service_name = isset($bookingData['service']['name']) ? $bookingData['service']['name'] : 'Service';
    $booking_time = isset($bookingData['bookingStart']) ? $bookingData['bookingStart'] : '';

    if (empty($customer_phone)) return;

    $clean_phone = preg_replace('/[^0-9]/', '', $customer_phone);
    
    // Call 5MinutesAPI Engine
    $url = 'https://api.5minutesapi.com/v1/send-message';
    $payload = json_encode([
        'to' => $clean_phone,
        'whatsapp_template' => 'amelia_booking_alert',
        'variables' => [$customer_name, $service_name, $booking_time]
    ]);

    wp_remote_post($url, [
        'body'        => $payload,
        'timeout'     => 10,
        'headers'     => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ]
    ]);
}
?>
