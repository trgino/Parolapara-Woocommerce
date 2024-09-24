<?php
/*
Plugin Name: Parolapara
Plugin URI:
Description: Parolapara ile ödemeye almaya başlayın
Version: 1.0.1
Author: Ravensoft
Author URI:
License: GNU
Text Domain: parolapara
Domain Path: /languages
 */
if (!defined('ABSPATH')) {
    exit;
}
// Çoklu Dil Desteği Ekler
add_action('init', 'wpdocs_load_parolapara');
function wpdocs_load_parolapara()
{
    load_plugin_textdomain('parolapara', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

define("plugin_url", plugin_dir_url(__FILE__));
add_filter('woocommerce_payment_gateways', 'parolapara_add_gateway_class');
function parolapara_add_gateway_class($gateways)
{
    $gateways[] = 'WC_parolapara_Gateway';
    return $gateways;
}

require_once "class/class-parolapara.php";
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'parolapara_init_gateway_class');
function parolapara_init_gateway_class()
{
    class WC_parolapara_Gateway extends WC_Payment_Gateway
    {
        private $parolapara_settings;
        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct()
        {

            $this->parolapara_settings = get_option("woocommerce_parolapara_settings");

            $this->id = 'parolapara';
            //$this->icon = plugin_dir_url(__FILE__) . "images/logo.png";
            $this->has_fields = true;
            $this->method_title = 'parolapara';
            $this->method_description = "Parolapara ile ödeme almaya başlayın";
            $this->supports = array('products');
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = "" . $this->get_option('description') . "";
            $this->enabled = $this->get_option('enabled');
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_receipt_parolapara', array($this, 'parolapara_payment_redirect'));
            add_action('woocommerce_api_parolapara_installment', array($this, 'getInstallment'));
            add_action('woocommerce_api_parolapara_start_payment', array($this, 'startPayment'));
            add_action('woocommerce_api_parolapara_complete_payment', array($this, 'completePayment'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable parolapara',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no',
                ),
                'title' => array(
                    'title' => esc_html__('Başlık', 'parolapara'),
                    'type' => 'text',
                    'description' => esc_html__('Sitenizde müşterilerinizin göreceği ödeme yöntemi başlığı', 'parolapara'),
                    'default' => 'parolapara Sanal Pos',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => esc_html__('Açıklama', 'parolapara'),
                    'type' => 'text',
                    'description' => esc_html__('Sitenizde müşterilerinizin göreceği ödeme yöntemi açıklaması', 'parolapara'),
                    'default' => 'Kredi kartınızla ödeme yapın.',
                    'desc_tip' => true,
                ),
                'api_type' => array(
                    'title' => esc_html__('Api Türü', 'parolapara'),
                    'type' => 'select',
                    'options' => array(
                        'https://ccpayment.parolapara.com/ccpayment/' => esc_html__('Gerçek Ortam', 'parolapara'),
                        'https://testccpayment.parolapara.com/ccpayment/' => esc_html__('Test Ortamı', 'parolapara'),
                    ),
                ),
                '3d_secure' => array(
                    'title' => esc_html__('3d Secure', 'parolapara'),
                    'type' => 'select',
                    'options' => array(
                        '3d' => esc_html__('3d Ödeme', 'parolapara'),
//                                '2d' => esc_html__('2d Ödeme', 'parolapara')
                    ),
                ),
                'form_type' => array(
                    'title' => esc_html__('Form Türü', 'parolapara'),
                    'type' => 'select',
                    'options' => array(
                        'payment-form-api' => esc_html__('Ödeme Formu', 'parolapara'),
//                                'payment-form-iframe' => esc_html__('İframe Ödeme', 'parolapara')
                    ),
                ),
                'merchant_id' => array(
                    'title' => esc_html__('Merchant ID', 'parolapara'),
                    'type' => 'text',
                ),
                'merchant_key' => array(
                    'title' => esc_html__('Merchant Key', 'parolapara'),
                    'type' => 'text',
                ),
                'app_key' => array(
                    'title' => esc_html__('App Key', 'parolapara'),
                    'type' => 'text',
                ),
                'app_secret' => array(
                    'title' => esc_html__('App Secret', 'parolapara'),
                    'type' => 'text',
                ),
            );
        }

        public function process_payment($order_id)
        {

            $order = wc_get_order($order_id);
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true),
            );
        }

        public function parolapara_payment_redirect($order_id)
        {

            if (isset($_REQUEST['error']) && !empty($_REQUEST['error'])) {
                wc_print_notice($_REQUEST['error'], "error");
            }

            $order = wc_get_order($order_id);

            require_once "view/" . $this->parolapara_settings['form_type'] . ".php";
        }

        /*
         * Taksit sayısını çeker
         */
        public function getInstallment()
        {

            $order = wc_get_order($_REQUEST['order_id']);
            $parolapara = new parolapara();
            $par = [
                'merchant_key' => $this->parolapara_settings['merchant_key'],
                'amount' => $order->get_total(),
                'credit_card' => trim($_REQUEST['cardnumber']),
                'currency_code' => "TRY",
            ];

            echo $parolapara->getInstallment($par);

            exit;
        }

        public function startPayment()
        {

            if (isset($_REQUEST['d3Yonlendir'])) {

                $order_id = $_REQUEST['d3Yonlendir'];
                $parolapara = new parolapara();

                if ($this->parolapara_settings['3d_secure'] == "2d") {

                    /*
                     * 2d işlemler için buradan işlem yapılır
                     */

                    $parolapara->start2d($order_id);

                } else {

                    /*
                     * İşlem 3d ise bu kısımdan işlem yapar
                     */

                    $parolapara->start3d($order_id);
                }
            }
            exit;
        }

        /*
         * 3d ödeme tamamla
         */
        public function completePayment()
        {
            $order_id = $_REQUEST['invoice_id'];
            $parolapara = new parolapara();
            $parolapara->completePayment($order_id);
            exit;
        }

    }
}
