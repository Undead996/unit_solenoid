<?php
class DBConnect
{
    
    private $db;
    
    public function __construct($host, $user, $pass, $dbName, $loger = Null)
    {           
        $this->db = new Mysqli($host, $user, $pass, $dbName);
        if (mysqli_connect_errno()) {
            $this->return_sql_error(mysqli_connect_errno());
        }
		$this->loger = $loger;
    }

    private function return_sql_error($err)
    {
        echo "MySQL ERROR: $err";
        return "MySQL ERROR: $err";
    }
    
    public function db_query($query)
    {
        $res = $this->db->query($query);
        if (mysqli_connect_errno()) {
            return $this->return_sql_error(mysqli_connect_errno());
        } else {
            return $res;
        }
    }
	
	private function write_log($str)
	{
		if ($this->loger)
			$this->loger->write_log($str);
	}
	
	
	
    public function insert_multiprocessing($protocol, $how_point, $name)
    {
        $query = "
            INSERT INTO `multiprocessing`
				(`code`, `name`, `protocol`, `active`, `removed`)
			VALUES
				('$how_point', '$name', '$protocol', 1, 0);";
		$this->write_log("SQL query: $query");
		$res = $this->db->query($query);
        if (mysqli_connect_errno()) {
            $this->return_sql_error(mysqli_connect_errno());
        }
    }
    
    public function insert_multiprocessing_data($how_point, $arr)
    {
        foreach($arr as $sub){
            $query = "
				INSERT INTO `multiprocessing_data`
					(`multiprocessing`, `ident`, `value`, `removed`)
				VALUES($how_point, '" . implode("','", $sub) . "', 0);";
			$this->write_log("SQL query: $query");
            $res = $this->db->query($query);
            if (mysqli_connect_errno()) {
                $this->return_sql_error(mysqli_connect_errno());
            }
        }
    }
	
	
	
    public function rotor_update($how, $how_point) 
    {
		
		$this->insertExchange($how, $how_point, 'config_update', '{}');
        sleep(3);
    }
    
	public function insertExchange($how, $how_point, $next_operation, $fields) 
    {
		
		$transact = rand(1000000000, 9999999999);
		$this->write_log("transact: $transact");
        $query = "
			INSERT INTO exchange 
				(transact, how, how_point, next_operation, fields, next_try_date) 
			VALUES 
				('$transact', $how, $how_point, '$next_operation', '$fields', NOW())";
        $this->write_log("SQL query: $query");
        $q = $this->db->query($query);
		
		return $transact;
	}
	
	public function get_next_operation($transact) 
    {
		$query = "SELECT next_operation FROM exchange WHERE transact='$transact';";
		$res = $this->db->query($query);
		$result = $res->fetch_array();
		$this->write_log("next_operation: '$result[0]'");
		return $result['0'];
	}
    
    public function get_merchant_next_operation($transact) 
    {
		$query = "SELECT next_operation FROM merchant_orders_archive WHERE merchant_ext_transact='$transact';";
		$res = $this->db->query($query);
		$result = $res->fetch_array();
		$this->write_log("next_operation: '$result[0]'");
		return $result['0'];
	}
    
    public function get_result_fields($transact) 
    {
        $query = "SELECT result_fields FROM exchange WHERE transact='$transact';";
		$res = $this->db->query($query);
		$result = $res->fetch_array();
		$this->write_log("result_fields: $result[0]");
		return json_decode($result['0'], true);
    }
	
	public function generateHowPoint($protocol) 
	{
		return $protocol . random_int(1, 9999);
    }
	
	
	private function deleteGate($how_point) 
	{
		$this->db->db_query("DELETE FROM multiprocessing WHERE code = $how_point");
		$this->db->db_query("DELETE FROM multiprocessing_data WHERE multiprocessing = $how_point");
	}
	
	# PROTOCOL HELPERS
	
	public function createHashconnectGate($test_name, $rows) 
	{
		$how_point = $this->generateHowPoint(PROTOCOL_HASHCONNECT);
		$this->insert_multiprocessing(PROTOCOL_HASHCONNECT, $how_point, 'HashConnect ' . $test_name);
        $this->insert_multiprocessing_data($how_point, $rows);
		
		// номер магазина поменялся - обязательно нужно обновлять
		$this->rotor_update(PROTOCOL_HASHCONNECT, $how_point);
		
		return $how_point;
	}
	
    public function createGuavaGate($test_name) 
	{
		$how_point = $this->generateHowPoint(PROTOCOL_GUAVA);
        $this->insert_multiprocessing(PROTOCOL_GUAVA, $how_point, 'Guava ' . $test_name);
        $this->insert_multiprocessing_data($how_point,
			[
				['guava_login_pay',        '******'],
				['guava_password_pay',     '******'],
				['url_referrer',           'https://gate-dev.paypoint.pro/referrer.php'],
				['callback_url',           'https://gate-dev.paypoint.pro/systems/guava/callback.php'],
				['term_url',               'https://gate-dev.paypoint.pro/systems/guava/from_bank_to_cabinet.php'],
				['success_url',            'https://gate-dev.paypoint.pro/systems/guava/from_bank_to_cabinet.php'],
				['decline_url',            'https://gate-dev.paypoint.pro/systems/guava/from_bank_to_cabinet.php'],
				['service_login',          'zachislyator'],
				['service_password',       'zachislyator'],
				['service_crt',            'Q2VydGlmaWNhdGU6CiAgICBEYXRhOgogICAgICAgIFZlcnNpb246IDEgKDB4MCkKICAgICAgICBTZXJpYWwgTnVtYmVyOiAzMDgzMDEgKDB4NGI0NGQpCiAgICBTaWduYXR1cmUgQWxnb3JpdGhtOiBzaGEyNTZXaXRoUlNBRW5jcnlwdGlvbgogICAgICAgIElzc3VlcjogQz1SVSwgU1Q9TW9zY293LCBMPU1vc2NvdywgTz1EZW1vIFBheXBvaW50IFBybywgT1U9RGVtbyBQYXlwb2ludCBQcm8sIENOPXBheXBvaW50LnByby9lbWFpbEFkZHJlc3M9Y3J0QHBheXBvaW50LnBybwogICAgICAgIFZhbGlkaXR5CiAgICAgICAgICAgIE5vdCBCZWZvcmU6IE1hciAgNiAwNjo1MDo1NSAyMDIwIEdNVAogICAgICAgICAgICBOb3QgQWZ0ZXIgOiBPY3QgIDUgMDY6NTA6NTUgMjAyOSBHTVQKICAgICAgICBTdWJqZWN0OiBDPVJVLCBTVD02NDMsIE89emFjaGlzbHlhdG9yLCBDTj1hZHY6NTUyNTYxL2VtYWlsQWRkcmVzcz1hbmRyZXlAcGF5cG9pbnQucHJvCiAgICAgICAgU3ViamVjdCBQdWJsaWMgS2V5IEluZm86CiAgICAgICAgICAgIFB1YmxpYyBLZXkgQWxnb3JpdGhtOiByc2FFbmNyeXB0aW9uCiAgICAgICAgICAgICAgICBQdWJsaWMtS2V5OiAoMTAyNCBiaXQpCiAgICAgICAgICAgICAgICBNb2R1bHVzOgogICAgICAgICAgICAgICAgICAgIDAwOmI2OjYzOjJiOjA0OjdkOmUyOmM4OjkzOmMzOjdkOjg3OjhhOjU2OjNkOgogICAgICAgICAgICAgICAgICAgIGMxOjQxOjc0OjdkOmI2OjM1OjEzOjRkOjc2OjdmOmUwOjE5OjNhOjYxOjRmOgogICAgICAgICAgICAgICAgICAgIDBmOmYyOjBiOjU2OjFlOjUwOjMxOjY0OjYzOjEyOjAxOjc4OjliOjk0OjYwOgogICAgICAgICAgICAgICAgICAgIDk4Ojk1OjA0OmU0OjBjOjdlOjQ0OmE5OjBiOmQ4OmYwOmZhOmYxOjI1OjdiOgogICAgICAgICAgICAgICAgICAgIGVmOjQzOjczOmYyOmI1OjRiOmU4OjkwOjA5OjZjOjEzOmJmOjE4OmY1OmEwOgogICAgICAgICAgICAgICAgICAgIGZkOjdjOjI0OjM5OjhjOmRjOjg3Ojg2OmRkOjg4OmJiOmU5OjNlOmUxOjEyOgogICAgICAgICAgICAgICAgICAgIGVmOjcwOmI1OjAwOmM1Ojg1OmVkOjAxOmY2OjczOjVkOmRkOmU5OjE4OmYwOgogICAgICAgICAgICAgICAgICAgIDNmOjUyOjdiOjU5OjU3OjE3OjkxOjU5Ojk2OjVlOjZkOmMyOmEwOjZhOmM4OgogICAgICAgICAgICAgICAgICAgIDAwOjNjOmFlOmNlOmIxOjdlOjkwOjFlOmRmCiAgICAgICAgICAgICAgICBFeHBvbmVudDogNjU1MzcgKDB4MTAwMDEpCiAgICBTaWduYXR1cmUgQWxnb3JpdGhtOiBzaGEyNTZXaXRoUlNBRW5jcnlwdGlvbgogICAgICAgICA3MzoyMTo0OTpiZjo0MDpiMjo3ZTo3NzphNzpjODo3MTo3Yzo1ODo3NDpjMTplYzpkNzo0NDoKICAgICAgICAgNTE6YzM6ZGM6NDI6Yzg6Mzg6ZjI6YzI6NjA6MDU6ZDY6NzY6YTc6NTM6MWQ6ZGY6ZWY6ZDM6CiAgICAgICAgIDQ4OjU4OjFmOjNlOmU4OmJiOjY0OmJjOjZhOmE5OjhkOjkwOjMxOjIwOjExOjAwOjVhOjIwOgogICAgICAgICBmMzo2NjpmYzowNjo0ODoxODpjNDplZTplOTplZTplZTo2NDoyMTpmMTpmZDo0YjplZjo0ZDoKICAgICAgICAgOWE6YTA6NjE6YWY6NjI6ZDE6Mzc6OTI6ZDE6ZmY6MzY6NDM6Yjg6Y2Y6YTE6ZDk6ZDA6NDY6CiAgICAgICAgIDk1Ojk2OmNiOjUyOmE3Ojk5OmQyOmMwOmZhOjkwOjZjOjczOjg0OmI1OmQ0OmQ5OjY0OjlhOgogICAgICAgICBlNzpkYzo3Nzo2NDphNzozNTpjMDoyNTo3MDo3MzozYTo0MTo3NDo0ZjpkOTpmMjo5ZjoyMToKICAgICAgICAgOWQ6ZWM6ZDk6ZDA6NzQ6ZTU6ODQ6YTc6YjQ6MDI6ZjA6YTU6ZTY6MzQ6NDY6ZmE6ZDM6ODc6CiAgICAgICAgIDM1OjFiOmE2OmZhOjUzOjAwOmQ2OjFiOmY4OmY1OmJlOjAwOmM1OmE1OjdkOjYyOmQ2OjRkOgogICAgICAgICBiZTpjZjphZDo1MDphZjpkYjo3YTpkNzpjNDo1MDo0NTo4YzpiNTozNDo0YzoxNjplNDoyYToKICAgICAgICAgMDA6ZTU6NjM6OTQ6YjA6MTQ6YjI6YTM6MTM6ZjE6MDI6MGQ6MWM6MWY6MmM6NGQ6ODg6OWM6CiAgICAgICAgIDk1OmZhOmZmOjM3OmRkOjdjOmY2OmM2OjMxOjMyOjc5OmVkOmQ1OjQyOmI3OmZhOjE3OjM1OgogICAgICAgICAzOTowMDowNToxOTphZDoxZDo0ZTpiYjpkMTo1Yjo3YzoyMzpiNzo4ZDozNzo4ODpmOTpjMDoKICAgICAgICAgNGY6YTE6OTM6YTg6NjI6OGI6ODE6Mjc6OWQ6OWE6ZGQ6MzQ6ODY6MGE6YmU6OTg6NTY6YmM6CiAgICAgICAgIDQzOjM3OjliOjQ3OjQwOmJjOjFlOjI2OjU2OjZmOjBhOmNlOjZjOjNjOjljOjBhOjU3OmZhOgogICAgICAgICA3ZjowMjoxYzo0NjozYjozYjpkYzo0MzpmMTo4Mzo5NDpiOToxNTozZDpkNDo0MDo1NjpmMjoKICAgICAgICAgZDU6MWM6MzM6YTE6MjM6ODY6NWU6NzY6NmY6Mjg6Nzg6ZGM6YmU6MTk6NGM6OGE6YTA6ZWY6CiAgICAgICAgIGIxOmM0Ojg5OjU5OmE0OmY5OmMwOjY2OmMxOmM0OmYyOjlhOmZlOjgyOjNmOjIwOjQ2OmJkOgogICAgICAgICBkZDoyMDo4Yzo1ZDo4MzphZDo1ZDplZDo4NTo0YTowZTo2NjpmNzpkMDpmZTpiZjo3MjowZjoKICAgICAgICAgZjE6NWU6ZGI6OWI6MWI6OGE6MjM6M2Q6YWE6ZDc6NTU6ZGY6MTc6NWM6MGU6NTk6NDk6YjA6CiAgICAgICAgIGIwOmY3OjUzOmZiOmNmOjVkOmI1OjEyOjU5OmUyOjFmOjViOjM4OjZhOjc1OmY1OmFiOjRjOgogICAgICAgICAzOTozOTo1NDo3OToyMTo2ZDpjYzoxMzozNjo5MzphZjplNjoyNjozODo4NzoyZjowOTo3ZDoKICAgICAgICAgZDY6ODY6NWQ6NzE6ZTk6Y2I6NzM6NWE6YjY6Y2E6MTA6MDg6NWM6NDU6ZWI6ODc6ZmU6NTc6CiAgICAgICAgIGJmOmZhOjRkOjkzOjM5OmIyOjAzOmFhOmRiOjc1OjFhOjRjOmQwOmVhOjQ5OjI2OjI1OmEyOgogICAgICAgICBlOTowZToyYzplYTphNzpkNDowYTpmZjpmNzpjNjplYzpiMjplODowMTozNzo4ZjpiYjo0ZDoKICAgICAgICAgNmY6MTM6N2Q6YzE6MWE6N2Q6MmI6ZTA6MWI6YmE6OTI6OTk6ZTM6YmM6NGI6OWI6ZWQ6ODc6CiAgICAgICAgIDZhOjNiOjUwOjgxOjE1OjdmOjk3OjFiOmFiOmM2OmQwOjA1OmY2OmQ2OmQwOjliOmE5Ojc0OgogICAgICAgICAxNzpkMzowYzpiMDo2YzphZTo3ODo3ODoxODoyMTpmYTplMDo0ZjoyZTo4ZTphNTo0OTpmNToKICAgICAgICAgOTU6YmE6MTk6NGI6ZjQ6YTQ6Njg6ZTkKLS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUQvVENDQWVVQ0F3UzBUVEFOQmdrcWhraUc5dzBCQVFzRkFEQ0JuekVMTUFrR0ExVUVCaE1DVWxVeER6QU4KQmdOVkJBZ01CazF2YzJOdmR6RVBNQTBHQTFVRUJ3d0dUVzl6WTI5M01Sb3dHQVlEVlFRS0RCRkVaVzF2SUZCaAplWEJ2YVc1MElGQnliekVhTUJnR0ExVUVDd3dSUkdWdGJ5QlFZWGx3YjJsdWRDQlFjbTh4RlRBVEJnTlZCQU1NCkRIQmhlWEJ2YVc1MExuQnliekVmTUIwR0NTcUdTSWIzRFFFSkFSWVFZM0owUUhCaGVYQnZhVzUwTG5CeWJ6QWUKRncweU1EQXpNRFl3TmpVd05UVmFGdzB5T1RFd01EVXdOalV3TlRWYU1Hc3hDekFKQmdOVkJBWVRBbEpWTVF3dwpDZ1lEVlFRSURBTTJORE14RlRBVEJnTlZCQW9NREhwaFkyaHBjMng1WVhSdmNqRVRNQkVHQTFVRUF3d0tZV1IyCk9qVTFNalUyTVRFaU1DQUdDU3FHU0liM0RRRUpBUllUWVc1a2NtVjVRSEJoZVhCdmFXNTBMbkJ5YnpDQm56QU4KQmdrcWhraUc5dzBCQVFFRkFBT0JqUUF3Z1lrQ2dZRUF0bU1yQkgzaXlKUERmWWVLVmozQlFYUjl0alVUVFhaLwo0Qms2WVU4UDhndFdIbEF4WkdNU0FYaWJsR0NZbFFUa0RINUVxUXZZOFByeEpYdnZRM1B5dFV2b2tBbHNFNzhZCjlhRDlmQ1E1ak55SGh0Mkl1K2srNFJMdmNMVUF4WVh0QWZaelhkM3BHUEEvVW50WlZ4ZVJXWlplYmNLZ2FzZ0EKUEs3T3NYNlFIdDhDQXdFQUFUQU5CZ2txaGtpRzl3MEJBUXNGQUFPQ0FnRUFjeUZKdjBDeWZuZW55SEY4V0hUQgo3TmRFVWNQY1FzZzQ4c0pnQmRaMnAxTWQzKy9UU0ZnZlB1aTdaTHhxcVkyUU1TQVJBRm9nODJiOEJrZ1l4TzdwCjd1NWtJZkg5Uys5Tm1xQmhyMkxSTjVMUi96WkR1TStoMmRCR2xaYkxVcWVaMHNENmtHeHpoTFhVMldTYTU5eDMKWktjMXdDVndjenBCZEUvWjhwOGhuZXpaMEhUbGhLZTBBdkNsNWpSRyt0T0hOUnVtK2xNQTFodjQ5YjRBeGFWOQpZdFpOdnMrdFVLL2JldGZFVUVXTXRUUk1GdVFxQU9WamxMQVVzcU1UOFFJTkhCOHNUWWljbGZyL045MTg5c1l4Ck1ubnQxVUszK2hjMU9RQUZHYTBkVHJ2Ulczd2p0NDAzaVBuQVQ2R1RxR0tMZ1NlZG10MDBoZ3ErbUZhOFF6ZWIKUjBDOEhpWldid3JPYkR5Y0NsZjZmd0ljUmpzNzNFUHhnNVM1RlQzVVFGYnkxUnd6b1NPR1huWnZLSGpjdmhsTQppcUR2c2NTSldhVDV3R2JCeFBLYS9vSS9JRWE5M1NDTVhZT3RYZTJGU2c1bTk5RCt2M0lQOFY3Ym14dUtJejJxCjExWGZGMXdPV1Vtd3NQZFQrODlkdFJKWjRoOWJPR3AxOWF0TU9UbFVlU0Z0ekJNMms2L21KamlITHdsOTFvWmQKY2VuTGMxcTJ5aEFJWEVYcmgvNVh2L3BOa3pteUE2cmJkUnBNME9wSkppV2k2UTRzNnFmVUN2LzN4dXl5NkFFMwpqN3ROYnhOOXdScDlLK0FidXBLWjQ3eExtKzJIYWp0UWdSVi9seHVyeHRBRjl0YlFtNmwwRjlNTXNHeXVlSGdZCklmcmdUeTZPcFVuMWxib1pTL1NrYU9rPQotLS0tLUVORCBDRVJUSUZJQ0FURS0tLS0tCg=='],
				['service_key',            'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUNkd0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQW1Fd2dnSmRBZ0VBQW9HQkFMWmpLd1I5NHNpVHczMkgKaWxZOXdVRjBmYlkxRTAxMmYrQVpPbUZQRC9JTFZoNVFNV1JqRWdGNG01UmdtSlVFNUF4K1JLa0wyUEQ2OFNWNwo3ME56OHJWTDZKQUpiQk8vR1BXZy9Yd2tPWXpjaDRiZGlMdnBQdUVTNzNDMUFNV0Y3UUgyYzEzZDZSandQMUo3CldWY1hrVm1XWG0zQ29HcklBRHl1enJGK2tCN2ZBZ01CQUFFQ2dZRUFsRHpScjlycFFnRG5PTlc3R0JFbFM1LzAKdE8wNmZSRTlLZFVYWUJPMGNCUEtzT1NZNEhDdEo4anhHbzNRTmY0OW8vSFV1RmpLd0VJVlVWUUR5WjBwdTlRZQo2b2ZkWFVDN3R2WFVjdjhNd0pGZ1lidEdmZjNRQ3R0L1pOQXVrQ0U0cXBzOHIxU1MwdHI4SkJKZDIvQ0pWa2J3CnFyRlFwNEhoODJtSjZrcFpNOUVDUVFEcm9DUVNCQ0kvTDNTNUpEeGthQnJ3QnRsRVFRRUVwYmllUEVkRjk1TGoKcEJ2TGdNVk8remR2alVXdk9XQmdwQjFLTDIwTUU4cnU5VlNZVG9xTzk4Y2pBa0VBeGlpSkFEMTJReEtBTWxFUgo1dDBsRXY2ank4Z0I3TmhuRk1VQUgzRGFnc3FrLzNteEY1Q2k0K05SRkZ4Q1diMlRIUEh0MWI1cndkQUxsb1NyCnJhRWpGUUpBVmt4UnliTnY0NXA2OHJBOTJqeHkyVVI0NE5HNkVMeXRrRzdkWDlmY0dibnFZQzlxbEpIWDdPaGUKQkY1TVdUamliV0JQWFNRR3FGeDhQa2hONFMwSTV3SkJBSk1URHV3d3NoQUNNVWduUjhRMEt3bzRHVlpzc3BFWgo1UmhUUjA0T3N3QVVhL1o2V2VpRm40REkvU3JCZHpXb01RSnd4ZmU1Qjcyb0xwR2ZFdFVpSGlrQ1FCMUR0OXBJClRtd1lqRkN0Mzg0WDNYdkh5b2o5WFFqL3RTRXZFOEErWXYxTzhSRWsvMlRydWt1TkhPSlRoMGd1NWFjb3dVSlkKWlZ5dHI2M01oeXprR2lVPQotLS0tLUVORCBQUklWQVRFIEtFWS0tLS0tCg=='],
				['guava_login_payout',     'GPW_PAYOUT'],
				['guava_password_payout',  'GP22556699'],
				['guava_login_balance',    'GPW_PAYOUT'],
				['guava_password_balance', 'GPW_PAYOUT123'],
				['guava_mcc',              '6536'],
				['guava_sid',              '84661']
			]
        );
		
		// https://bit.paypoint.pro/company/personal/user/6/tasks/task/view/45536/
		// $this->rotor_update(PROTOCOL_GUAVA, $how_point);
		
		return $how_point;
	}
    
	
	public function createQiwiGate($test_name, $curr) 
	{
		$how_point = $this->generateHowPoint(PROTOCOL_GUAVA);
        $this->insert_multiprocessing(PROTOCOL_GUAVA, $how_point, 'Qiwi ' . $test_name);
        $this->insert_multiprocessing_data($how_point,
			[
				['qiwi_token', '2f94c429ec937b4c2e400c6afd2f71d1'],
				['personId', $curr]
			]
        );
		
		// https://bit.paypoint.pro/company/personal/user/6/tasks/task/view/45536/
		// $this->rotor_update(PROTOCOL_GUAVA, $how_point);
		
		return $how_point;
	}
	
	public function createPaymegaGate($test_name, $with_config_update = True) 
	{
		$how_point = $this->generateHowPoint(PROTOCOL_PAYMEGA);
		$this->insert_multiprocessing(PROTOCOL_PAYMEGA, $how_point, 'Paymega ' . $test_name);
        $this->insert_multiprocessing_data($how_point, 
			[
				['paymega_login',    'coma_BHyPiHfmAvATQdEr'],
				['paymega_password', 'M_Oc4MYpUwi1AzWPwTQNLIlW_Gkp3R9pdumTn0X608s'],
				['url_referrer',     'https://gate-dev.paypoint.pro/referrer.php'],
				['callback_url',     'https://gate-dev.paypoint.pro/systems/paymega/callback.php'],
				['term_url',         'https://gate-dev.paypoint.pro/systems/paymega/from_bank_to_cabinet.php'],
				['success_url',      'https://gate-dev.paypoint.pro/systems/paymega/from_bank_to_cabinet.php'],
				['decline_url',      'https://gate-dev.paypoint.pro/systems/paymega/from_bank_to_cabinet.php'],
				['service_login',    'zachislyator'],
				['service_password', 'zachislyator'],
				['service_crt',      'Q2VydGlmaWNhdGU6CiAgICBEYXRhOgogICAgICAgIFZlcnNpb246IDEgKDB4MCkKICAgICAgICBTZXJpYWwgTnVtYmVyOiAzMDgzMDEgKDB4NGI0NGQpCiAgICBTaWduYXR1cmUgQWxnb3JpdGhtOiBzaGEyNTZXaXRoUlNBRW5jcnlwdGlvbgogICAgICAgIElzc3VlcjogQz1SVSwgU1Q9TW9zY293LCBMPU1vc2NvdywgTz1EZW1vIFBheXBvaW50IFBybywgT1U9RGVtbyBQYXlwb2ludCBQcm8sIENOPXBheXBvaW50LnByby9lbWFpbEFkZHJlc3M9Y3J0QHBheXBvaW50LnBybwogICAgICAgIFZhbGlkaXR5CiAgICAgICAgICAgIE5vdCBCZWZvcmU6IE1hciAgNiAwNjo1MDo1NSAyMDIwIEdNVAogICAgICAgICAgICBOb3QgQWZ0ZXIgOiBPY3QgIDUgMDY6NTA6NTUgMjAyOSBHTVQKICAgICAgICBTdWJqZWN0OiBDPVJVLCBTVD02NDMsIE89emFjaGlzbHlhdG9yLCBDTj1hZHY6NTUyNTYxL2VtYWlsQWRkcmVzcz1hbmRyZXlAcGF5cG9pbnQucHJvCiAgICAgICAgU3ViamVjdCBQdWJsaWMgS2V5IEluZm86CiAgICAgICAgICAgIFB1YmxpYyBLZXkgQWxnb3JpdGhtOiByc2FFbmNyeXB0aW9uCiAgICAgICAgICAgICAgICBQdWJsaWMtS2V5OiAoMTAyNCBiaXQpCiAgICAgICAgICAgICAgICBNb2R1bHVzOgogICAgICAgICAgICAgICAgICAgIDAwOmI2OjYzOjJiOjA0OjdkOmUyOmM4OjkzOmMzOjdkOjg3OjhhOjU2OjNkOgogICAgICAgICAgICAgICAgICAgIGMxOjQxOjc0OjdkOmI2OjM1OjEzOjRkOjc2OjdmOmUwOjE5OjNhOjYxOjRmOgogICAgICAgICAgICAgICAgICAgIDBmOmYyOjBiOjU2OjFlOjUwOjMxOjY0OjYzOjEyOjAxOjc4OjliOjk0OjYwOgogICAgICAgICAgICAgICAgICAgIDk4Ojk1OjA0OmU0OjBjOjdlOjQ0OmE5OjBiOmQ4OmYwOmZhOmYxOjI1OjdiOgogICAgICAgICAgICAgICAgICAgIGVmOjQzOjczOmYyOmI1OjRiOmU4OjkwOjA5OjZjOjEzOmJmOjE4OmY1OmEwOgogICAgICAgICAgICAgICAgICAgIGZkOjdjOjI0OjM5OjhjOmRjOjg3Ojg2OmRkOjg4OmJiOmU5OjNlOmUxOjEyOgogICAgICAgICAgICAgICAgICAgIGVmOjcwOmI1OjAwOmM1Ojg1OmVkOjAxOmY2OjczOjVkOmRkOmU5OjE4OmYwOgogICAgICAgICAgICAgICAgICAgIDNmOjUyOjdiOjU5OjU3OjE3OjkxOjU5Ojk2OjVlOjZkOmMyOmEwOjZhOmM4OgogICAgICAgICAgICAgICAgICAgIDAwOjNjOmFlOmNlOmIxOjdlOjkwOjFlOmRmCiAgICAgICAgICAgICAgICBFeHBvbmVudDogNjU1MzcgKDB4MTAwMDEpCiAgICBTaWduYXR1cmUgQWxnb3JpdGhtOiBzaGEyNTZXaXRoUlNBRW5jcnlwdGlvbgogICAgICAgICA3MzoyMTo0OTpiZjo0MDpiMjo3ZTo3NzphNzpjODo3MTo3Yzo1ODo3NDpjMTplYzpkNzo0NDoKICAgICAgICAgNTE6YzM6ZGM6NDI6Yzg6Mzg6ZjI6YzI6NjA6MDU6ZDY6NzY6YTc6NTM6MWQ6ZGY6ZWY6ZDM6CiAgICAgICAgIDQ4OjU4OjFmOjNlOmU4OmJiOjY0OmJjOjZhOmE5OjhkOjkwOjMxOjIwOjExOjAwOjVhOjIwOgogICAgICAgICBmMzo2NjpmYzowNjo0ODoxODpjNDplZTplOTplZTplZTo2NDoyMTpmMTpmZDo0YjplZjo0ZDoKICAgICAgICAgOWE6YTA6NjE6YWY6NjI6ZDE6Mzc6OTI6ZDE6ZmY6MzY6NDM6Yjg6Y2Y6YTE6ZDk6ZDA6NDY6CiAgICAgICAgIDk1Ojk2OmNiOjUyOmE3Ojk5OmQyOmMwOmZhOjkwOjZjOjczOjg0OmI1OmQ0OmQ5OjY0OjlhOgogICAgICAgICBlNzpkYzo3Nzo2NDphNzozNTpjMDoyNTo3MDo3MzozYTo0MTo3NDo0ZjpkOTpmMjo5ZjoyMToKICAgICAgICAgOWQ6ZWM6ZDk6ZDA6NzQ6ZTU6ODQ6YTc6YjQ6MDI6ZjA6YTU6ZTY6MzQ6NDY6ZmE6ZDM6ODc6CiAgICAgICAgIDM1OjFiOmE2OmZhOjUzOjAwOmQ2OjFiOmY4OmY1OmJlOjAwOmM1OmE1OjdkOjYyOmQ2OjRkOgogICAgICAgICBiZTpjZjphZDo1MDphZjpkYjo3YTpkNzpjNDo1MDo0NTo4YzpiNTozNDo0YzoxNjplNDoyYToKICAgICAgICAgMDA6ZTU6NjM6OTQ6YjA6MTQ6YjI6YTM6MTM6ZjE6MDI6MGQ6MWM6MWY6MmM6NGQ6ODg6OWM6CiAgICAgICAgIDk1OmZhOmZmOjM3OmRkOjdjOmY2OmM2OjMxOjMyOjc5OmVkOmQ1OjQyOmI3OmZhOjE3OjM1OgogICAgICAgICAzOTowMDowNToxOTphZDoxZDo0ZTpiYjpkMTo1Yjo3YzoyMzpiNzo4ZDozNzo4ODpmOTpjMDoKICAgICAgICAgNGY6YTE6OTM6YTg6NjI6OGI6ODE6Mjc6OWQ6OWE6ZGQ6MzQ6ODY6MGE6YmU6OTg6NTY6YmM6CiAgICAgICAgIDQzOjM3OjliOjQ3OjQwOmJjOjFlOjI2OjU2OjZmOjBhOmNlOjZjOjNjOjljOjBhOjU3OmZhOgogICAgICAgICA3ZjowMjoxYzo0NjozYjozYjpkYzo0MzpmMTo4Mzo5NDpiOToxNTozZDpkNDo0MDo1NjpmMjoKICAgICAgICAgZDU6MWM6MzM6YTE6MjM6ODY6NWU6NzY6NmY6Mjg6Nzg6ZGM6YmU6MTk6NGM6OGE6YTA6ZWY6CiAgICAgICAgIGIxOmM0Ojg5OjU5OmE0OmY5OmMwOjY2OmMxOmM0OmYyOjlhOmZlOjgyOjNmOjIwOjQ2OmJkOgogICAgICAgICBkZDoyMDo4Yzo1ZDo4MzphZDo1ZDplZDo4NTo0YTowZTo2NjpmNzpkMDpmZTpiZjo3MjowZjoKICAgICAgICAgZjE6NWU6ZGI6OWI6MWI6OGE6MjM6M2Q6YWE6ZDc6NTU6ZGY6MTc6NWM6MGU6NTk6NDk6YjA6CiAgICAgICAgIGIwOmY3OjUzOmZiOmNmOjVkOmI1OjEyOjU5OmUyOjFmOjViOjM4OjZhOjc1OmY1OmFiOjRjOgogICAgICAgICAzOTozOTo1NDo3OToyMTo2ZDpjYzoxMzozNjo5MzphZjplNjoyNjozODo4NzoyZjowOTo3ZDoKICAgICAgICAgZDY6ODY6NWQ6NzE6ZTk6Y2I6NzM6NWE6YjY6Y2E6MTA6MDg6NWM6NDU6ZWI6ODc6ZmU6NTc6CiAgICAgICAgIGJmOmZhOjRkOjkzOjM5OmIyOjAzOmFhOmRiOjc1OjFhOjRjOmQwOmVhOjQ5OjI2OjI1OmEyOgogICAgICAgICBlOTowZToyYzplYTphNzpkNDowYTpmZjpmNzpjNjplYzpiMjplODowMTozNzo4ZjpiYjo0ZDoKICAgICAgICAgNmY6MTM6N2Q6YzE6MWE6N2Q6MmI6ZTA6MWI6YmE6OTI6OTk6ZTM6YmM6NGI6OWI6ZWQ6ODc6CiAgICAgICAgIDZhOjNiOjUwOjgxOjE1OjdmOjk3OjFiOmFiOmM2OmQwOjA1OmY2OmQ2OmQwOjliOmE5Ojc0OgogICAgICAgICAxNzpkMzowYzpiMDo2YzphZTo3ODo3ODoxODoyMTpmYTplMDo0ZjoyZTo4ZTphNTo0OTpmNToKICAgICAgICAgOTU6YmE6MTk6NGI6ZjQ6YTQ6Njg6ZTkKLS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUQvVENDQWVVQ0F3UzBUVEFOQmdrcWhraUc5dzBCQVFzRkFEQ0JuekVMTUFrR0ExVUVCaE1DVWxVeER6QU4KQmdOVkJBZ01CazF2YzJOdmR6RVBNQTBHQTFVRUJ3d0dUVzl6WTI5M01Sb3dHQVlEVlFRS0RCRkVaVzF2SUZCaAplWEJ2YVc1MElGQnliekVhTUJnR0ExVUVDd3dSUkdWdGJ5QlFZWGx3YjJsdWRDQlFjbTh4RlRBVEJnTlZCQU1NCkRIQmhlWEJ2YVc1MExuQnliekVmTUIwR0NTcUdTSWIzRFFFSkFSWVFZM0owUUhCaGVYQnZhVzUwTG5CeWJ6QWUKRncweU1EQXpNRFl3TmpVd05UVmFGdzB5T1RFd01EVXdOalV3TlRWYU1Hc3hDekFKQmdOVkJBWVRBbEpWTVF3dwpDZ1lEVlFRSURBTTJORE14RlRBVEJnTlZCQW9NREhwaFkyaHBjMng1WVhSdmNqRVRNQkVHQTFVRUF3d0tZV1IyCk9qVTFNalUyTVRFaU1DQUdDU3FHU0liM0RRRUpBUllUWVc1a2NtVjVRSEJoZVhCdmFXNTBMbkJ5YnpDQm56QU4KQmdrcWhraUc5dzBCQVFFRkFBT0JqUUF3Z1lrQ2dZRUF0bU1yQkgzaXlKUERmWWVLVmozQlFYUjl0alVUVFhaLwo0Qms2WVU4UDhndFdIbEF4WkdNU0FYaWJsR0NZbFFUa0RINUVxUXZZOFByeEpYdnZRM1B5dFV2b2tBbHNFNzhZCjlhRDlmQ1E1ak55SGh0Mkl1K2srNFJMdmNMVUF4WVh0QWZaelhkM3BHUEEvVW50WlZ4ZVJXWlplYmNLZ2FzZ0EKUEs3T3NYNlFIdDhDQXdFQUFUQU5CZ2txaGtpRzl3MEJBUXNGQUFPQ0FnRUFjeUZKdjBDeWZuZW55SEY4V0hUQgo3TmRFVWNQY1FzZzQ4c0pnQmRaMnAxTWQzKy9UU0ZnZlB1aTdaTHhxcVkyUU1TQVJBRm9nODJiOEJrZ1l4TzdwCjd1NWtJZkg5Uys5Tm1xQmhyMkxSTjVMUi96WkR1TStoMmRCR2xaYkxVcWVaMHNENmtHeHpoTFhVMldTYTU5eDMKWktjMXdDVndjenBCZEUvWjhwOGhuZXpaMEhUbGhLZTBBdkNsNWpSRyt0T0hOUnVtK2xNQTFodjQ5YjRBeGFWOQpZdFpOdnMrdFVLL2JldGZFVUVXTXRUUk1GdVFxQU9WamxMQVVzcU1UOFFJTkhCOHNUWWljbGZyL045MTg5c1l4Ck1ubnQxVUszK2hjMU9RQUZHYTBkVHJ2Ulczd2p0NDAzaVBuQVQ2R1RxR0tMZ1NlZG10MDBoZ3ErbUZhOFF6ZWIKUjBDOEhpWldid3JPYkR5Y0NsZjZmd0ljUmpzNzNFUHhnNVM1RlQzVVFGYnkxUnd6b1NPR1huWnZLSGpjdmhsTQppcUR2c2NTSldhVDV3R2JCeFBLYS9vSS9JRWE5M1NDTVhZT3RYZTJGU2c1bTk5RCt2M0lQOFY3Ym14dUtJejJxCjExWGZGMXdPV1Vtd3NQZFQrODlkdFJKWjRoOWJPR3AxOWF0TU9UbFVlU0Z0ekJNMms2L21KamlITHdsOTFvWmQKY2VuTGMxcTJ5aEFJWEVYcmgvNVh2L3BOa3pteUE2cmJkUnBNME9wSkppV2k2UTRzNnFmVUN2LzN4dXl5NkFFMwpqN3ROYnhOOXdScDlLK0FidXBLWjQ3eExtKzJIYWp0UWdSVi9seHVyeHRBRjl0YlFtNmwwRjlNTXNHeXVlSGdZCklmcmdUeTZPcFVuMWxib1pTL1NrYU9rPQotLS0tLUVORCBDRVJUSUZJQ0FURS0tLS0tCg=='],
				['service_key',      'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUNkd0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQW1Fd2dnSmRBZ0VBQW9HQkFMWmpLd1I5NHNpVHczMkgKaWxZOXdVRjBmYlkxRTAxMmYrQVpPbUZQRC9JTFZoNVFNV1JqRWdGNG01UmdtSlVFNUF4K1JLa0wyUEQ2OFNWNwo3ME56OHJWTDZKQUpiQk8vR1BXZy9Yd2tPWXpjaDRiZGlMdnBQdUVTNzNDMUFNV0Y3UUgyYzEzZDZSandQMUo3CldWY1hrVm1XWG0zQ29HcklBRHl1enJGK2tCN2ZBZ01CQUFFQ2dZRUFsRHpScjlycFFnRG5PTlc3R0JFbFM1LzAKdE8wNmZSRTlLZFVYWUJPMGNCUEtzT1NZNEhDdEo4anhHbzNRTmY0OW8vSFV1RmpLd0VJVlVWUUR5WjBwdTlRZQo2b2ZkWFVDN3R2WFVjdjhNd0pGZ1lidEdmZjNRQ3R0L1pOQXVrQ0U0cXBzOHIxU1MwdHI4SkJKZDIvQ0pWa2J3CnFyRlFwNEhoODJtSjZrcFpNOUVDUVFEcm9DUVNCQ0kvTDNTNUpEeGthQnJ3QnRsRVFRRUVwYmllUEVkRjk1TGoKcEJ2TGdNVk8remR2alVXdk9XQmdwQjFLTDIwTUU4cnU5VlNZVG9xTzk4Y2pBa0VBeGlpSkFEMTJReEtBTWxFUgo1dDBsRXY2ank4Z0I3TmhuRk1VQUgzRGFnc3FrLzNteEY1Q2k0K05SRkZ4Q1diMlRIUEh0MWI1cndkQUxsb1NyCnJhRWpGUUpBVmt4UnliTnY0NXA2OHJBOTJqeHkyVVI0NE5HNkVMeXRrRzdkWDlmY0dibnFZQzlxbEpIWDdPaGUKQkY1TVdUamliV0JQWFNRR3FGeDhQa2hONFMwSTV3SkJBSk1URHV3d3NoQUNNVWduUjhRMEt3bzRHVlpzc3BFWgo1UmhUUjA0T3N3QVVhL1o2V2VpRm40REkvU3JCZHpXb01RSnd4ZmU1Qjcyb0xwR2ZFdFVpSGlrQ1FCMUR0OXBJClRtd1lqRkN0Mzg0WDNYdkh5b2o5WFFqL3RTRXZFOEErWXYxTzhSRWsvMlRydWt1TkhPSlRoMGd1NWFjb3dVSlkKWlZ5dHI2M01oeXprR2lVPQotLS0tLUVORCBQUklWQVRFIEtFWS0tLS0tCg=='],
				['keyt',             'RUB'],
				['secret_key',       'sk_live_6IAo6sh1pqwvGHlwMTt-lGQHPuefVzLUUauNFTUgkRk'],
				['mode',             'test'],
			]
        );
		
		// необходимость обновления вообще уберем https://bit.paypoint.pro/company/personal/user/6/tasks/task/view/45536/, а еще эта команда используется пока только в config_update, поэтому не нужно, но если что - можно добавить параметр временно
		// $this->db->rotor_update(PROTOCOL_PAYMEGA, $how_point);
		
		return $how_point;
	}
}