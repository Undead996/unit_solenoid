<?php

use PHPUnit\Framework\TestCase;

class ConfigUpdate_Paymega_Test extends TestCase
{
    public function testСonfigUpdate_ok()
    {   
		$this->init(__FUNCTION__);
        $how_point = $this->db->createPaymegaGate(__FUNCTION__);
        $this->assertSame('config_update_ok', $this->executeExchangeCommand($how_point));
    }
    public function testСonfigUpdate_definitive_error()
    {   
		$this->init(__FUNCTION__);
		$how_point = $this->db->createPaymegaGate(__FUNCTION__);
		# для получения ошибки отправляем запрос по несуществующей точке
        $this->assertSame('config_update_definitive_error', $this->executeExchangeCommand($how_point+1));
    }
	
	
	
	private function init($test_name)
	{
		$this->loger = new Logger('Paymega_' . $test_name, 'config_update', LOGS_DIR);
		$this->db = new DBConnect(MYSQL_EXCHANGE_HOST, MYSQL_EXCHANGE_LOGIN, MYSQL_EXCHANGE_PASSWORD, MYSQL_EXCHANGE_DB, $this->loger);
	}
	
    private function executeExchangeCommand($how_point)
    {
		$transact = $this->db->insertExchange(PROTOCOL_PAYMEGA, $how_point, 'config_update', '{}');
		
        sleep(10);
		
		$next_operation = $this->db->get_next_operation($transact);
		$this->loger->end_log();
		// можно и оставить для истории
		// $this->db->deleteGate($how_point);
		
		return $next_operation;
    }
    

}