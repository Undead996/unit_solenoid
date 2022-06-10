<?php

use PHPUnit\Framework\TestCase;

class Merchant_Dev_Guava_Test extends TestCase
{ 
    public function testMerchant_3ds()
    {   
        $this->assertSame('transactsString', $this->executeExchangeCommand(__FUNCTION__));
    }
    
    
    
	private function init($test_name) 
	{
		$this->loger = new Logger($test_name, 'merchant', LOGS_DIR);
		$this->db = new DBConnect(MYSQL_DEV_EXCHANGE_HOST, MYSQL_DEV_EXCHANGE_LOGIN, MYSQL_DEV_EXCHANGE_PASSWORD, MYSQL_DEV_EXCHANGE_DB, $this->loger);
	}

    private function executeExchangeCommand($test_name)
    {   
		$this->init($test_name);
		$how_point = '342';
        $transacts = '';
        
        for ($i = 0; $i < 20; $i++) {
        $fields = json_encode([
            "url_decline" => "https://my-lab.paypoint.pro/proc/fill/ps/show_result/",
            "url_success" => "https://my-lab.paypoint.pro/proc/fill/ps/show_result/",
            "skeys" => "",
            "cvv" => "555",
            "client_country_code" => "RU",
            "free_param" => "",
            "request" => "PAY",
            "token" => "",
            "token_name" => "",
            "keyt" => "40903810000100146545",
            "expMonth" => "10",
            "amount" => "10",
            "url_callback" => "",
            "sender_curr_code" => "RUB",
            "sid" => "83597",
            "curr" => "810",
            "expYear" => "2025",
            "ext_transact" => "202205301341497161",
            "pan" => "4111111111111111",
            "is_tokenize" => "",
            ]);
		$transact = $this->db->insertExchange(PROTOCOL_DEV_GUAVASANDBOX, $how_point, 'merchant_purchase_pretransact', $fields);
        $transacts = $transacts. '/'. $transact;
        }
		$this->loger->end_log();
		// можно и оставить для истории
		// $this->db->deleteGate($how_point);
		
		return $transacts;
    }
    

}