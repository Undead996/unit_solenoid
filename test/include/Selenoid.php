<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class Selenoid 
{   
    protected $webDriver;
    protected $url = '';
 
    public function __construct($loger)
    {   
        $this->loger = $loger;
        $this->loger->write_log("SELENOID HERE");

        // $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'firefox');
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability('enableVideo', true);
        $capabilities->setCapability('enableLog', true);
        $this->webDriver = RemoteWebDriver::create('http://192.168.121.5:4444/wd/hub', $capabilities);
    }
    
    public function set_url($url) 
    {
        $this->url = $url;
    }
    
    public function simple_referrer()
    {
        $this->webDriver->get($this->url);
        sleep(10);
        $this->loger->write_log("URL_acs: ".$this->webDriver->getCurrentURL());
        $elem = $this->webDriver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $elem->click();
        sleep(10);
        $this->loger->write_log("URL_cabinet: ".$this->webDriver->getCurrentURL());
        $this->loger->write_log("SELENOID END");
        $this->webDriver->quit();
    }  
}