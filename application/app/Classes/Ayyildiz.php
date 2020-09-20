<?php

class sendSMS {
	private $username = "";
	private $password = "";
	private $BayiKodu = "";
	private $requestedpass;
	private $originator = "";
	private $gsmno;
	private $msgtext;
	private $sendMulti = false;

	public function __construct($username, $password, $BayiKodu, $sender_id, $phone, $message)
    {
    	$this->username = $username;
    	$this->password= $password;
    	$this->BayiKodu = $BayiKodu;
    	$this->originator = $sender_id;
    	$this->gsmno = $phone;
    	$this->msgtext = $message;
    }

    function setGsmNo($gsmno) {
		if (is_array ( $gsmno )) {
			$nos = "";
			foreach ( $gsmno as $no ) {
				if (preg_match_all ( '/(?:[+]|[0]{1,2}){0,1}(?:[\s]{0,})(?P<icode>90|9[\s]0){0,1}(?:[\s]{0,})(?P<t1>5[0-9]{2})(?:[\s]{0,})(?P<t2>[0-9]{3})(?:[\s]{0,})(?P<t3>[0-9]{2})(?:[\s]{0,})(?P<t4>[0-9]{2})(?:[\s]{0,})/im', $no, $result, PREG_PATTERN_ORDER )) {
					$no = $result ['t1'] [0] . $result ['t2'] [0] . $result ['t3'] [0] . $result ['t4'] [0];
					$nos .= $no . ",";
				}
			}
			$this->gsmno = substr ( $nos, 0, - 1 );
		} else {
			if (preg_match_all ( '/(?:[+]|[0]{1,2}){0,1}(?:[\s]{0,})(?P<icode>90|9[\s]0){0,1}(?:[\s]{0,})(?P<t1>5[0-9]{2})(?:[\s]{0,})(?P<t2>[0-9]{3})(?:[\s]{0,})(?P<t3>[0-9]{2})(?:[\s]{0,})(?P<t4>[0-9]{2})(?:[\s]{0,})/im', $gsmno, $result, PREG_PATTERN_ORDER )) {
				$this->gsmno = $result ['t1'] [0] . $result ['t2'] [0] . $result ['t3'] [0] . $result ['t4'] [0];
			}
		}
	
	}
	
	function setMessageText($text) {
		$text = str_replace ( array ("&#304;", "\u0130", "\xDD", "İ" ), "I", $text );
		$text = str_replace ( array ("&#305;", "\u0131", "\xFD", "ı" ), "i", $text );
		$text = str_replace ( array ("&#286;", "\u011e", "\xD0", "Ğ" ), "G", $text );
		$text = str_replace ( array ("&#287;", "\u011f", "\xF0", "ğ" ), "g", $text );
		$text = str_replace ( array ("&Uuml;", "\u00dc", "\xDC", "U" ), "U", $text );
		$text = str_replace ( array ("&uuml;", "\u00fc", "\xFC", "ü" ), "u", $text );
		$text = str_replace ( array ("&#350;", "\u015e", "\xDE", "Ş" ), "S", $text );
		$text = str_replace ( array ("&#351;", "\u015f", "\xFE", "ş" ), "s", $text );
		$text = str_replace ( array ("&Ouml;", "\u00d6", "\xD6", "Ö" ), "O", $text );
		$text = str_replace ( array ("&ouml;", "\u00f6", "\xF6", "ö" ), "o", $text );
		$text = str_replace ( array ("&Ccedil;", "\u00c7", "\xC7", "Ç" ), "C", $text );
		$text = str_replace ( array ("&ccedil;", "\u00e7", "\xE7", "ç" ), "c", $text );
		$this->msgtext = $text;
	}
	
	function send() {

		$xml = <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<MainmsgBody>
	<UserName>{$this->username}</UserName>
	<PassWord>{$this->password}</PassWord>
	<CompanyCode>{$this->BayiKodu}</CompanyCode>
	<Developer></Developer>
	<Version>xVer.4.0</Version>
	<Originator>{$this->originator}</Originator>
	<Mesgbody>{$this->msgtext}</Mesgbody>
	<Numbers>{$this->gsmno}</Numbers>
	<SDate></SDate>
	<EDate></EDate>
</MainmsgBody>	
EOH;

		$result = $this->postViaCurl ( "http://sms.ayyildiz.net/SendSmsMany.aspx", $xml );


        $msg ['00'] = "Kullanıcı Bilgileri Boş";
		$msg ['01'] = "Kullanıcı Bilgileri Hatalı";
		$msg ['02'] = "Hesap Kapalı";
		$msg ['03'] = "Kontör Hatası";
		$msg ['04'] = "Bayi Kodunuz Hatalı";
		$msg ['05'] = "Originator Bilginiz Hatalı";
		$msg ['06'] = "Yapılan İşlem İçin Yetkiniz Yok";
		$msg ['10'] = "Geçersiz IP Adresi";
		$msg ['14'] = "Mesaj Metni Girilmemiş";
		$msg ['15'] = "GSM Numarası Girilmemiş";
		$msg ['20'] = "Rapor Hazır Değil";
		$msg ['27'] = "Aylık Atım Limitiniz Yetersiz";
		$msg ['100'] = "XML Hatası";
		if (is_numeric ( $result ) && isset ( $msg [$result] )) {
			$result = array ("basari" => false, "mesaj" => $msg [$result] );
		} else {
			$result = array ("basari" => true, "mesaj" => $result );
		}
		return $result;
	}
	
	private function postViaCurl($url, $data) {

		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_VERBOSE, true );
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 300 );
		curl_setopt ( $curl, CURLE_OPERATION_TIMEOUTED, 300 );
		curl_setopt ( $curl, CURLOPT_HEADER, false );
		curl_setopt ( $curl, CURLOPT_POST, true );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $curl, CURLOPT_URL, $url );
		$result = curl_exec ( $curl );
		curl_close ( $curl );
        return $result;
	}
}
?>
