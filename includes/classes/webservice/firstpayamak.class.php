<?php
	class firstpayamak extends WP_SMS {
		private $wsdl_link = "http://ui.firstpayamak.ir/webservice/v2.asmx?WSDL";
		public $tariff = "http://firstpayamak.ir/";
		public $unitrial = false;
		public $unit;
		public $flash = "enable";
		public $isflash = false;

		public function __construct() {
			parent::__construct();
			$this->validateNumber = "09xxxxxxxx";
			
			ini_set("soap.wsdl_cache_enabled", "0");
		}

		public function SendSMS() {

			// Check credit for the gateway
			if(!$this->GetCredit()) return;
			
			/**
			 * Modify sender number
			 *
			 * @since 3.4
			 * @param string $this->from sender number.
			 */
			$this->from = apply_filters('wp_sms_from', $this->from);
			
			/**
			 * Modify Receiver number
			 *
			 * @since 3.4
			 * @param array $this->to receiver number
			 */
			$this->to = apply_filters('wp_sms_to', $this->to);
			
			/**
			 * Modify text message
			 *
			 * @since 3.4
			 * @param string $this->msg text message.
			 */
			$this->msg = apply_filters('wp_sms_msg', $this->msg);

			$msg = urlencode($this->msg);
			
			foreach($this->to as $number) {
				$result = file_get_contents("http://ui.firstpayamak.ir/tools/urlservice/send/?username={$this->username}&password={$this->password}&form={$this->from}&to={$number}&message={$msg}");
			}
			
			if($result) {
				$this->InsertToDB($this->from, $this->msg, $this->to);
				
				/**
				 * Run hook after send sms.
				 *
				 * @since 2.4
				 * @param string $result result output.
				 */
				do_action('wp_sms_send', $result);
			}
		}

		public function GetCredit() {
			$client = new SoapClient($this->wsdl_link);
			$result = $client->GetCredit( array('username' => $this->username, 'password' => $this->password) );
			return $result->GetCreditResult;
		}
	}