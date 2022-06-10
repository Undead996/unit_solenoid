<?php

use PHPUnit\Framework\TestCase;

class Balance_Qiwi_Test extends TestCase
{
    public function testBalance_ok()
    {   
        $this->assertSame('balance_ok', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_RANDOM_POSITIVE_AMOUNT));
    }
    public function testBalance_zero()
    {   
        $this->assertSame('balance_ok', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_ZERO));
    }
    public function testBalance_negative()
    {   
        $this->assertSame('balance_ok', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_RANDOM_NEGATIVE_AMOUNT));
    }
    
    public function testBalance_invalidJson()
    {   
        $this->assertSame('balance_exception', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_INCORRECT_ANSWER));
    }
    
    public function testBalance_jsonElem()
    {   
        $this->assertSame('balance_exception', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_ELEMENT_MISSING));
    }
    
    // public function testBalance_invalidCode()
    // {   
        // $this->assertSame('balance_exception', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_WRONG_ENCODE));
    // }
    
    public function testBalance_invalidBalance()
    {   
        $this->assertSame('balance_exception', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_WRONG_NUMBER));
    }
    
    public function testBalance_TimeoutY()
    {   
        $this->assertSame('balance_ok', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_TIMEOUT_70, 80));
    }
    
    public function testBalance_TimeoutZ()
    {   
        $this->assertSame('balance_exception', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_TIMEOUT_130, 140));
    }
    
    public function testBalance_Timeout()
    {   
        $this->assertSame('balance_exception', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_TIMEOUT_FULL, 180));
    }
    
    public function testBalance_definitive_error()
    {   
        $this->assertSame('balance_definitive_error', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_PROTOCOL_FATAL));
    }
    
    public function testBalance_temporary_error()
    {   
        $this->assertSame('balance_temporary_error', $this->executeExchangeCommand('Qiwi_'.__FUNCTION__, BALANCE_PROTOCOL_NONFATAL));
    }


    
    
	private function init($test_name) 
	{
		$this->loger = new Logger($test_name, 'balance', LOGS_DIR);
		$this->db = new DBConnect(MYSQL_EXCHANGE_HOST, MYSQL_EXCHANGE_LOGIN, MYSQL_EXCHANGE_PASSWORD, MYSQL_EXCHANGE_DB, $this->loger);
	}
    
	private function executeExchangeCommand($test_name, $curr, $timeout = 10)
	{
		$this->init($test_name);
		
		$how_point = $this->db->createQiwiGate($test_name, $curr);
		
		$fields = json_encode(['curr' => $curr]);
		$transact = $this->db->insertExchange(PROTOCOL_QIWI, $how_point, 'balance', $fields);
		
        sleep($timeout);
		
		$next_operation = $this->db->get_next_operation($transact);
		$this->loger->end_log();
		// можно и оставить для истории
		// $this->db->deleteGate($how_point);
		
		return $next_operation;
    }
    
}