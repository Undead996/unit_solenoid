<?php

class Logger
{   
    public function __construct($name, $grp, $dir)
    {   
        $this->logdir = $dir.'/'.$grp;
        if (!file_exists($this->logdir)) {
            mkdir($this->logdir, 0777, true);
        }
        chmod($this->logdir, 0777);
        $this->name = $name;
        $this->grp = $grp;
        $this->file_log = $this->logdir."/".$name.".log";
        $this->msg = '';
        $this->init_log();
    }
    
    private function add_dt($fw)
    {   
        $dt = date('Y-m-d H:i:s');
        fwrite($fw, $dt."\n");
    }
    
    private function init_log()
    {   
        $fw = fopen($this->file_log, "a+") or die("can't create logfile");
        $this->add_dt($fw);
        fwrite($fw, $this->name."\n");
        fclose($fw);
        chmod($this->file_log, 0777);
    }
    
    public function write_log($msg)
    {   
        $fw = fopen($this->file_log, "a+");
        $this->add_dt($fw);
        fwrite($fw, $msg);
        fwrite($fw, "\n");
        fclose($fw);
    }
    public function end_log()
    {
        $fw = fopen($this->file_log, "a+");
        fwrite($fw, "\n __________________________________________________________ \n");
        fclose($fw);
    }
    
}