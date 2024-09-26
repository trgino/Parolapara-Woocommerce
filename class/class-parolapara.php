<?php
if (!defined('ABSPATH')) {
    exit;
}

class parolapara
{
    private $parolapara_settings;
    private $base_url;
    private $token_url;
    private $installment_url;
    private $payment_url;
    private $complete_url;
    private $confirm_payment_url;

    public function __construct()
    {
        $this->parolapara_settings = get_option("woocommerce_parolapara_settings");
        $this->base_url = $this->parolapara_settings['api_type'];
        $this->token_url = $this->parolapara_settings['api_type'] . "api/token";
        $this->installment_url = $this->parolapara_settings['api_type'] . "api/getpos";
        $this->payment_url = $this->parolapara_settings['api_type'] . "api/paySmart3D";
        $this->complete_url = $this->parolapara_settings['api_type'] . "payment/complete";
        $this->confirm_payment_url = $this->parolapara_settings['api_type'] . "api/confirmPayment ";
    }

    public function getToken()
    {

        $par = [
            'app_id' => $this->parolapara_settings['app_key'],
            'app_secret' => $this->parolapara_settings['app_secret'],
        ];
        $token_result = json_decode($this->curl($par, $this->token_url));

        if ($token_result->status_code == 100) {
            return $token_result->data->token;
        }
        return false;
    }

    public function getInstallment($par, $order)
    {

        $token = $this->getToken();
        $installament = json_decode($this->curl($par, $this->installment_url, $token));

        if ($installament->status_code == 100) {

            $html = ['<div class="col wid100" id="tlist">'];
            $html[] = '<div class="col wid100" id="tlist">';
            $html[] = '<table class="table border rounded bg-white">';
            $html[] = '<tbody>';

            foreach ($installament->data as $key => $taksitler) {
                if ($taksitler->installments_number == 1) {
                    $text_value = "Tek Çekim";
                } else {
                    $text_value = $taksitler->installments_number . " x " . $this->fixNumberFormat($taksitler->amount_to_be_paid);
                }

                $total = $this->fixNumberFormat($taksitler->payable_amount);

                $html[] = '<tr><td class="px-3 py-2"><div class="form-check taksit-radio">';
                $html[] = '<input type="radio" data-sayi="' . $taksitler->installments_number . '" data-total="' . $total . '" data-currency="' . $order->get_currency() . '" data-no="' . $taksitler->installments_number . '" id="tno' . $taksitler->installments_number . '" class="form-check-input p-1" name="tno" ' . ($taksitler->installments_number == 1 ? 'checked' : '') . ' value="' . $taksitler->installments_number . '" tag="' . $total . '">';
                $html[] = '<label class="form-check-label" for="tno' . $taksitler->installments_number . '"><strong>' . $text_value . '</strong></label></div></td>';

                $html[] = '<td class="px-3 py-2" id="instalmentTd' . $key . '"><label for="tno1">' . $total . ' ' . $order->get_currency() . ' </label></td>';
                $html[] = '<td class="px-3 py-2 text-primary"></td></tr>';
                $html[] = '<input type="hidden" name="tsx' . $taksitler->installments_number . '" value="' . $taksitler->hash_key . '">';
            }

            $html[] = '</tbody></table></div>';

            return implode('', $html);
        } else {
            return "<h5>Bu karta taksit yapılmamaktadır.</h5>";
        }

    }

    public function start2d($order_id)
    {

    }

    public function start3d($order_id)
    {

        $order = wc_get_order($order_id);

        $items = [];
        foreach ($order->get_items() as $item_id => $item) {

            $product = $item->get_product();
            $items[] = [
                'price' => floatval($this->fixNumberFormat($product->get_price())),
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'description' => $item->get_name(),
            ];
        }

        $items[] = [
            'price' => $order->get_shipping_total(),
            'name' => "Kargo",
            'quantity' => 1,
            'description' => "Gönderi ücreti",
        ];

        $items = json_encode($items);

        $okUrl = get_site_url(get_current_blog_id(), '/wc-api/parolapara_complete_payment');
        $failUrl = $order->get_checkout_payment_url(true);

        $amount = floatval($this->fixNumberFormat($_REQUEST['odenecek_tutar']));

        $hash_key = $this->generateHash($amount, $_REQUEST['secilen_taksit'], "TRY", $this->parolapara_settings['merchant_key'], $order_id, $this->parolapara_settings['app_secret']);

        $vars = [
            'cc_holder_name' => $_REQUEST['cardHolderName'],
            'cc_no' => str_replace(" ", "", $_REQUEST['cardNumber']),
            'expiry_month' => $_REQUEST['ay'],
            'expiry_year' => $_REQUEST['yil'],
            'cvv' => $_REQUEST['cvv'],
            'currency_code' => "TRY",
            'installments_number' => intval($_REQUEST['secilen_taksit']),
            'invoice_id' => $order_id,
            'invoice_description' => $order_id . " Nolu Sipariş Ödemesi",
            'name' => $order->get_billing_first_name(),
            'surname' => $order->get_billing_last_name(),
            'total' => $amount,
            'merchant_key' => $this->parolapara_settings['merchant_key'],
            'items' => $items,
            'return_url' => (string) $okUrl,
            'cancel_url' => (string) $failUrl,
            'hash_key' => $hash_key,
            'bill_address1' => $order->get_billing_address_1(),
            'bill_address2' => $order->get_billing_address_2(),
            'bill_city' => WC()->countries->get_states()['TR'][$order->get_billing_state()],
            'bill_postcode' => $order->get_billing_postcode(),
            'bill_state' => $order->get_billing_city(),
            'bill_country' => "Türkiye",
            'bill_email' => $order->get_billing_email(),
            'bill_phone' => $order->get_billing_phone(),
            'ip' => $this->getIp(),
            'transaction_type' => "Auth",
            'sale_webhook_key' => $order_id,
            'payment_completed_by' => "app",
            'response_method' => "POST",
        ];

        ?>

            <style>
                /* Absolute Center Spinner */
                .loading {
                    position: fixed;
                    z-index: 999;
                    height: 2em;
                    width: 2em;
                    overflow: visible;
                    margin: auto;
                    top: 0;
                    left: 0;
                    bottom: 0;
                    right: 0;
                }

                /* Transparent Overlay */
                .loading:before {
                    content: '';
                    display: block;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.3);
                }

                /* :not(:required) hides these rules from IE9 and below */
                .loading:not(:required) {
                    /* hide "loading..." text */
                    font: 0/0 a;
                    color: transparent;
                    text-shadow: none;
                    background-color: transparent;
                    border: 0;
                }

                .loading:not(:required):after {
                    content: '';
                    display: block;
                    font-size: 10px;
                    width: 1em;
                    height: 1em;
                    margin-top: -0.5em;
                    -webkit-animation: spinner 1500ms infinite linear;
                    -moz-animation: spinner 1500ms infinite linear;
                    -ms-animation: spinner 1500ms infinite linear;
                    -o-animation: spinner 1500ms infinite linear;
                    animation: spinner 1500ms infinite linear;
                    border-radius: 0.5em;
                    -webkit-box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.5) -1.5em 0 0 0, rgba(0, 0, 0, 0.5) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
                    box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) -1.5em 0 0 0, rgba(0, 0, 0, 0.75) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
                }

                /* Animation */

                @-webkit-keyframes spinner {
                    0% {
                        -webkit-transform: rotate(0deg);
                        -moz-transform: rotate(0deg);
                        -ms-transform: rotate(0deg);
                        -o-transform: rotate(0deg);
                        transform: rotate(0deg);
                    }
                    100% {
                        -webkit-transform: rotate(360deg);
                        -moz-transform: rotate(360deg);
                        -ms-transform: rotate(360deg);
                        -o-transform: rotate(360deg);
                        transform: rotate(360deg);
                    }
                }

                @-moz-keyframes spinner {
                    0% {
                        -webkit-transform: rotate(0deg);
                        -moz-transform: rotate(0deg);
                        -ms-transform: rotate(0deg);
                        -o-transform: rotate(0deg);
                        transform: rotate(0deg);
                    }
                    100% {
                        -webkit-transform: rotate(360deg);
                        -moz-transform: rotate(360deg);
                        -ms-transform: rotate(360deg);
                        -o-transform: rotate(360deg);
                        transform: rotate(360deg);
                    }
                }

                @-o-keyframes spinner {
                    0% {
                        -webkit-transform: rotate(0deg);
                        -moz-transform: rotate(0deg);
                        -ms-transform: rotate(0deg);
                        -o-transform: rotate(0deg);
                        transform: rotate(0deg);
                    }
                    100% {
                        -webkit-transform: rotate(360deg);
                        -moz-transform: rotate(360deg);
                        -ms-transform: rotate(360deg);
                        -o-transform: rotate(360deg);
                        transform: rotate(360deg);
                    }
                }

                @keyframes spinner {
                    0% {
                        -webkit-transform: rotate(0deg);
                        -moz-transform: rotate(0deg);
                        -ms-transform: rotate(0deg);
                        -o-transform: rotate(0deg);
                        transform: rotate(0deg);
                    }
                    100% {
                        -webkit-transform: rotate(360deg);
                        -moz-transform: rotate(360deg);
                        -ms-transform: rotate(360deg);
                        -o-transform: rotate(360deg);
                        transform: rotate(360deg);
                    }
                }
            </style>
            <div class="loading">Loading&#8230;</div>
            <form action="<?php echo $this->payment_url ?>" method="post"><?php
foreach ($vars as $key => $value) {
            echo "<input type='hidden' name='" . $key . "' value='" . $value . "'><br>
                                ";
        }
        ?>
                <input type="submit" value="GÖNDER" id="devam" style="display: none">
            </form>

            <script>
                document.getElementById("devam").click();
            </script><?php

    }

    public function completePayment($order_id)
    {

        global $woocommerce;
        $order = wc_get_order($order_id);
        $sonuc = $this->validateHashKey($_REQUEST['hash_key']);

        if ($sonuc['0'] == $_REQUEST['payment_status'] && $sonuc['2'] == $_REQUEST['invoice_id'] && $sonuc['3'] == $_REQUEST['order_id']) {
            if ($_REQUEST['transaction_type'] == "Auth") {

                $order->payment_complete();
                $woocommerce->cart->empty_cart();
                header("Location: " . $order->get_checkout_order_received_url());

                exit;

                $array = [
                    'merchant_key' => $this->parolapara_settings['merchant_key'],
                    'invoice_id' => $_REQUEST['invoice_id'],
                    'order_id' => $_REQUEST['order_id'],
                    'status' => "complete",
                    'hash_key' => $this->generateHashComplete($this->parolapara_settings['merchant_key'], $_REQUEST['invoice_id'], $_REQUEST['order_id'], "complete", $this->parolapara_settings['app_secret']),
                ];

                $sonuc = json_decode($this->curl($array, $this->complete_url, $this->getToken()));

                if ($sonuc->status_code == 100) {
                    $order->payment_complete();
                    $woocommerce->cart->empty_cart();
                    header("Location: " . $order->get_checkout_order_received_url());
                } else {
                    wc_print_notice($sonuc->status_description, "error");
                    header("Location: " . $order->get_checkout_payment_url());
                }

            }

        }

        exit;
    }

    public function curl($payload, $url, $token = "")
    {
        $header = [
            "Accept: application/json",
            "Content-Type: application/json",
        ];

        if (!empty($token)) {
            $header[] = "Authorization: Bearer $token";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($header));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    public function generateHash($total, $installment, $currency_code, $merchant_key, $invoice_id, $app_secret)
    {

        $data = $total . '|' . $installment . '|' . $currency_code . '|' . $merchant_key . '|' . $invoice_id;
        $iv = substr(sha1(mt_rand()), 0, 16);
        $password = sha1($app_secret);
        $salt = substr(sha1(mt_rand()), 0, 4);
        $saltWithPassword = hash('sha256', $password . $salt);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $saltWithPassword, 0, $iv);
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);
        return $msg_encrypted_bundle;
    }

    public function generateHashComplete($merchant_key, $invoice_id, $order_id, $status, $app_secret)
    {
        $data = $merchant_key . '|' . $invoice_id . '|' . $order_id . '|' . $status;
        $iv = substr(sha1(mt_rand()), 0, 16);
        $password = sha1($app_secret);

        $salt = substr(sha1(mt_rand()), 0, 4);
        $saltWithPassword = hash('sha256', $password . $salt);

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $saltWithPassword, 0, $iv);

        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);
        return $msg_encrypted_bundle;
    }

    public function validateHashKey($hashKey)
    {
        $status = $currencyCode = "";
        $total = $invoiceId = $orderId = 0;
        if (!empty($hashKey)) {
            $hashkey = str_replace('__', '/', $hashKey);
            $password = sha1($this->parolapara_settings['app_secret']);
            $components = explode(':', $hashkey);
            if (count($components) > 2) {
                $iv = isset($components[0]) ? $components[0] : "";
                $salt = isset($components[1]) ? $components[1] : "";
                $salt = hash('sha256', $password . $salt);
                $encryptedMsg = isset($components[2]) ? $components[2] : "";
                $decryptedMsg = openssl_decrypt($encryptedMsg, 'aes-256-cbc', $salt, 0, $iv);
                if (strpos($decryptedMsg, '|') !== false) {
                    $array = explode('|', $decryptedMsg);
                    $status = isset($array[0]) ? $array[0] : 0;
                    $total = isset($array[1]) ? $array[1] : 0;
                    $invoiceId = isset($array[2]) ? $array[2] : '0';
                    $orderId = isset($array[3]) ? $array[3] : 0;
                    $currencyCode = isset($array[4]) ? $array[4] : '';
                }
                return [$status, $total, $invoiceId, $orderId, $currencyCode];
            }
        }
    }

    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function fixNumberFormat($price, $decimal = 2)
    {
        $price = floatval($price);
        $price = number_format($price, ($decimal + 1), '.', '');
        $price = substr($price, 0, -1);
        return $price;
    }

}