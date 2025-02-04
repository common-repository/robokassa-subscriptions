<?php

namespace Robokassa\Payment;

class RobokassaPayAPI {

    /**
     * @var string
     */
    private $mrh_login;

    /**
     * @var string
     */
    private $mrh_pass1;

    /**
     * @var string
     */
    private $mrh_pass2;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $reply = '';

    /**
     * @var string
     */
    private $request = '';

    /**
     * @return string
     */
    public function getReply() {
        return $this->reply;
    }

    /**
     * @return string
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getSendResult() {
        return json_encode(array(
            'request' => $this->request,
            'reply' => $this->reply,
        ));
    }

    /**
     * @param string $login
     * @param string $pass1
     * @param string $pass2
     * @param string $method
     */
    public function __construct($login, $pass1, $pass2, $method = 'md5') {
        $this->mrh_login = $login;
        $this->mrh_pass1 = $pass1;
        $this->mrh_pass2 = $pass2;
        $this->method = $method;

        $this->apiUrl = substr($_SERVER['SERVER_PROTOCOL'], 0, -4).'://auth.robokassa.ru/Merchant/WebService/Service.asmx/';
    }

    /**
     * @param string $mthd
     * @param array  $data
     *
     * @return array
     */
    private function sendRequest($mthd, $data) {
        return json_decode($this->parseXmlAndConvertToJson($this->apiUrl.$mthd.'?'.http_build_query($data)), true);
    }

    /**
     * Если $receiptJson пустой (то есть имеет значение "[]") - то в формировании сигнатуры
     * он не использоваться, а если не пустой - используем его json-представление
     *
     * @param string $sum
     * @param string $invId
     * @param string $receiptJson
     *
     * @return string
     */
    private function getSignatureString($sum, $invId, $receiptJson, $outCurrency = null, $recurring = false)
    {
        if ($outCurrency !== false) {
            $outCurrency = $outCurrency ?: get_option('robokassa_out_currency');
            $outCurrency = get_option('robokassa_country_code') == 'KZ' && $outCurrency == 'KZT' ? null : $outCurrency;
        }

        return \implode(
        	':',
            \array_diff(
	            array(
	                $this->mrh_login,
	                $sum,
	                $invId,
		            $outCurrency,
	                $receiptJson,
	                $this->mrh_pass1,
	                'shp_label=official_wordpress_subs'
	            ),
	            array(
	            	false,
		            '',
		            null
	            )
            )
        );
    }

    /**
     * Генерирует хеш для строки $string с помощью метода $method
     *
     * @param string $string
     * @param string $method
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSignature($string, $method = 'md5') {
        if (in_array($method, array('md5', 'ripemd160', 'sha1', 'sha256', 'sha384', 'sha512'))) {
            return strtoupper(hash($method, $string));
        }

        throw new \Exception('Wrong Signature Method');
    }

    /**
     * Генерирует форму, в Opencart модуле НЕ ИСПОЛЬЗУЕТСЯ!
     *
     * @param float $sum
     * @param int $invId
     * @param string $invDesc
     * @param string $test
     * @param string $incCurrLabel
     * @param array $receipt
     *
     * @param null $email
     * @return string
     *
     * @throws \Exception
     */
    public function createForm(
        $sum,
        $invId,
        $invDesc,
        $test = 'false',
        $incCurrLabel = 'all',
        $receipt = null,
        $email = null,
        $currency = null,
        $recurring = false
    ) {
		
		$kzUrl = 'https://auth.robokassa.kz/Merchant/Index.aspx';
        $ruUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';
        $kzIframe = "<script type=\"text/javascript\" src=\"https://auth.robokassa.kz/Merchant/bundle/robokassa_iframe.js\"></script>";
        $ruIframe = "<script type=\"text/javascript\" src=\"https://auth.robokassa.ru/Merchant/bundle/robokassa_iframe.js\"></script>";

        if (get_option('robokassa_country_code') == "RU")
            $paymentUrl = $ruUrl;
        elseif(get_option('robokassa_country_code') == "KZ")
            $paymentUrl = $kzUrl;

	    $receiptJson = (!empty($receipt) && \is_array($receipt))
	        ? \urlencode(\json_encode($receipt, 256))
		    : null;

	    $currency = $currency ?: get_option('robokassa_out_currency');
        $currency = get_option('robokassa_country_code') == 'KZ' && $outCurrency == 'KZT' ? null : $currency;
        $currency = $currency == 'RUB' ? 'RUR' : $currency;

        $formData = array_filter(array(
            'Encoding'       => 'utf-8',
            'MrchLogin'      => $this->mrh_login,
            'OutSum'         => $sum,
            'InvId'          => $invId,
            'Desc'           => $invDesc,
            'shp_label'      => 'official_wordpress_subs',
            'OutSumCurrency' => $currency,
            'recurring'      => $recurring ? 'true' : '',
            'SignatureValue' => $this->getSignature($this->getSignatureString($sum, $invId, $receiptJson, $currency)),
        ));


        if($email !== null)
            $formData['Email'] = $email;

        $culture = get_option('robokassa_culture');
        if($culture !== Helper::CULTURE_AUTO)
            $formData['Culture'] = $culture;

        if (!empty($receipt)) {
            $formData['Receipt'] = $receiptJson;
        }

        if ($test == 'true') {
            $formData['IsTest'] = 1;
        }

        if ($incCurrLabel !== 'all') {
            $formData['IncCurrLabel'] = $incCurrLabel;
        }

        $robokassaEnabled = get_option('robokassa_payment_wc_robokassa_enabled');

        switch ($robokassaEnabled) {
            case 'torobomarket':
                $formUrl = 'http://robo.market/cart/insert';
                break;
            case 'yes':
                $formUrl = $paymentUrl;
                break;
            default:
                throw new \Exception('Не ожиданное значение опции "wc_robokassa_enabled"');
        }

        return $this->renderForm($formUrl, $formData);
    }

    /**
     * @param string $formUrl
     * @param array  $formData
     *
     * @return string
     */
    private function renderForm($formUrl, array $formData) {		
		
		if (get_option('robokassa_iframe')) {
			unset($formData['IsTest']);
			
			$kzIframe = "<script type=\"text/javascript\" src=\"https://auth.robokassa.kz/Merchant/bundle/robokassa_iframe.js\"></script>";
            $ruIframe = "<script type=\"text/javascript\" src=\"https://auth.robokassa.ru/Merchant/bundle/robokassa_iframe.js\"></script>";

            if (get_option('robokassa_country_code') == "RU")
                $iframeUrl = $ruIframe;
            elseif(get_option('robokassa_country_code') == "KZ")
                $iframeUrl = $kzIframe;
			
			$params = '';
			$lastParam = end($formData);
						
			foreach ($formData as $inputName => $inputValue){
				if($inputName != 'IsTest'){
					$value = htmlspecialchars($inputValue, ENT_COMPAT, 'UTF-8');
					
					if($lastParam == $inputValue){
						$params .= $inputName . ": '" . $value . "'";
					}else{
						$params .= $inputName . ": '" . $value . "', ";
					}
				}
			}
			$form = $iframeUrl;
			$form .= "<input id=\"robokassa\" type=\"submit\" onclick=\"Robokassa.StartPayment({" . $params . "})\" value=\"Оплатить\">";
			$form .= "<script type=\"text/javascript\"> document.getElementById('robokassa').click(); </script>";
		} else {
			$form = '<div class="preloader">
			  <svg class="preloader__image" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
				<path fill="currentColor"
				  d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z">
				</path>
			  </svg>
			</div>
			<style>
			.preloader {
			  position: fixed;
			  left: 0;
			  top: 0;
			  right: 0;
			  bottom: 0;
			  overflow: hidden;
			  /* фоновый цвет */
			  background: #e0e0e0;
			  z-index: 1001;
			}

			.preloader__image {
			  position: relative;
			  top: 50%;
			  left: 50%;
			  width: 70px;
			  height: 70px;
			  margin-top: -35px;
			  margin-left: -35px;
			  text-align: center;
			  animation: preloader-rotate 2s infinite linear;
			}

			@keyframes preloader-rotate {
			  100% {
				transform: rotate(360deg);
			  }
			}

			.loaded_hiding .preloader {
			  transition: 0.3s opacity;
			  opacity: 0;
			}

			.loaded .preloader {
			  display: none;
			}
			</style>
			<script>
			  window.onload = function () {
				document.body.classList.add("loaded_hiding");
				window.setTimeout(function () {
				  document.body.classList.add("loaded");
				  document.body.classList.remove("loaded_hiding");
				}, 1000);
			  }
			</script>';
			$form .= "<form action=\"$formUrl\" method=\"POST\">";

			foreach ($formData as $inputName => $inputValue) {
				$value = htmlspecialchars($inputValue, ENT_COMPAT, 'UTF-8');

				$form .= "<input type=\"hidden\" name=\"$inputName\" value=\"$value\">";
			}
			
			$form .= "<input id=\"robokassa\"  type=\"submit\" value=\"Оплатить\"></form>";
			$form .= "<script type=\"text/javascript\"> document.getElementById('robokassa').click(); </script>";
		}
		
		return $form;
    }

	/**
	 * Отправляет СМС с помощью GET-запроса на робокассу
	 *
	 * @param string $phone
	 * @param string $message
	 *
	 * @return bool
	 * @throws \Exception
	 */
    public function sendSms($phone, $message) {
        $data = array(
            'login' => $this->mrh_login,
            'phone' => $phone,
            'message' => $message,
            'signature' => $this->getSignature("$this->mrh_login:$phone:$message:$this->mrh_pass1"),
        );

        $url = substr($_SERVER['SERVER_PROTOCOL'], 0, -4).'://services.robokassa.ru/SMS/?'.http_build_query($data);

        $response = wp_remote_get($url);
        $parsed = json_decode($response, true);

        $this->request = $url;
        $this->reply = $response;

        return ($parsed['result'] == 1);
    }

    /**
     * Запрашиват размер комиссии в процентах для конкретного способа оплаты
     *
     * @param string $incCurrLabel Кодовое имя метода оплаты
     * @param int    $sum          Стоимость товара
     *
     * @return float Комиссия метода в %
     */
    public function getCommission($incCurrLabel, $sum = 10000) {
        if ($incCurrLabel == 'all') {
            $incCurrLabel = '';
        }

        $parsed = $this->sendRequest('CalcOutSumm', array(
            'MerchantLogin' => $this->mrh_login,
            'IncCurrLabel' => $incCurrLabel,
            'IncSum' => (int) $sum,
        ));

        if($parsed['OutSum'] != 0){
			return abs(round(($sum - $parsed['OutSum']) / $parsed['OutSum'] * 100));
		}else{
			return $sum;
		}
    }

    /**
     * Возвращает сумму к оплате с учетом комиссий.
     *
     * @param string $incCurrLabel Кодовое имя метода оплаты
     * @param int    $sum          Стоимость товара
     *
     * @return float Стоимость, которую необходимо передавать в Робокассу.
     */
    public function getCommissionSum($incCurrLabel, $sum) {
        $parsed = $this->sendRequest('CalcOutSumm', array(
            'MerchantLogin' => $this->mrh_login,
            'IncCurrLabel' => $incCurrLabel,
            'IncSum' => $sum,
        ));

        return $parsed['OutSum'];
    }

    /**
     * Запрашивает и парсит в массив все возможные способы оплаты для данного магазина
     *
     * @return array
     */
    public function getCurrLabels() {
        $parsed = $this->sendRequest('GetCurrencies', array(
            'MerchantLogin' => $this->mrh_login,
            'Language' => 'ru',
        ));

        $outArr = array();
		if(isset($parsed['Groups']))
		{
			foreach ($parsed['Groups']['Group'] as $value) {
				foreach ($value['Items']['Currency'] as $value2) {
					if (isset($value2['@attributes'])) {
						$attr = $value2['@attributes'];

						if ($attr['Name']) {
							$valLabel = $attr['Label'];

							$outArr[$valLabel] = array(
								'Name' => $attr['Name'],
								'Label' => $valLabel,
								'Alias' => $attr['Alias'],
								'Commission' => $this->GetCommission($valLabel),
								'MinValue' => isset($attr['MinValue']) ? $attr['MinValue'] : 0,
								'MaxValue' => isset($attr['MaxValue']) ? $attr['MaxValue'] : 9999999,
							);
						}
					}
				}
			}
		}

        return $outArr;
    }

    /**
     * Парсит XML в JSON
     *
     * @param string $url
     *
     * @return string
     */
    public function parseXmlAndConvertToJson($url) {
        return json_encode(simplexml_load_string(trim(str_replace('"', "'", str_replace(array(
            "\n",
            "\r",
            "\t",
        ), '', wp_remote_retrieve_body(wp_remote_get($url)))))));
    }

    /**
     * Запрашивает у робокассы подтверждение платежа
     *
     * @param int $invId
     *
     * @return bool
     */
    public function reCheck($invId) {
        $result = $this->sendRequest('OpState', array(
            'MerchantLogin' => $this->mrh_login,
            'InvoiceID' => $invId,
            'Signature' => strtoupper(md5("$this->mrh_login:$invId:$this->mrh_pass2")),
        ));

        return ($result['Result']['Code'] == '0');
    }

    public function getRecurringPaymentData($invoiceId, $parentInvoiceId, $amount, $receipt, $description = '')
    {
        // $receipt = (get_option('robokassa_payment_type_commission') == 'false' && get_option('robokassa_country_code') != 'KZ') ? $receipt : [];
        $receiptJson = (!empty($receipt) && \is_array($receipt)) ? \urlencode(\json_encode($receipt, 256)) : null;

        $data = array_filter([
            'MerchantLogin'     => $this->mrh_login,
            'InvoiceID'         => $invoiceId,
            'PreviousInvoiceID' => $parentInvoiceId,
            'Description'       => '',
            'SignatureValue'    => $this->getSignature($this->getSignatureString($amount, $invoiceId, $receiptJson, false)),
            'OutSum'            => $amount,
            'shp_label'         => 'official_wordpress_subs',
            'Receipt'           => $receiptJson
        ], function($val) { return $val !== null; });

        return $data;
    }
}
