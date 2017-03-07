<?php


class Restify {
	

	var $curlObj;
	var $httpHeader;
	var $result;
	
	function __construct()
	{
		$this->curlObj = curl_init();
		$this->httpHeader[] = "Cache-Control: max-age=0"; 
		$this->httpHeader[] = "Connection: keep-alive"; 
		$this->httpHeader[] = "Keep-Alive: 300"; 
		curl_setopt($this->curlObj,CURLOPT_HEADER,false);
		curl_setopt($this->curlObj,CURLOPT_AUTOREFERER,true);
		curl_setopt($this->curlObj,CURLOPT_FRESH_CONNECT,true);
		curl_setopt($this->curlObj,CURLOPT_RETURNTRANSFER,true);
	}
	
	private function execute($url)
	{
		curl_setopt($this->curlObj,CURLOPT_HTTPHEADER,$this->httpHeader);
		curl_setopt($this->curlObj,CURLOPT_URL,$url);
		$this->result = curl_exec($this->curlObj);
		return $this;
	}
	
	public function get($url,$data=null)
	{
		curl_setopt($this->curlObj,CURLOPT_HTTPGET,true);
		if($data != null)
		{
			$data = $this->prepare_data($data);
			$url .= "?".$data;
		}

		return $this->execute($url);
	}

	public function post($url,$data = null)
	{
		curl_setopt($this->curlObj,CURLOPT_POST,true);
		if($data != null)
		{
			$data = $this->prepare_data($data);
			curl_setopt($this->curlObj,CURLOPT_POSTFIELDS,$data);
		}

		return $this->execute($url);
	}

	public function put($url,$data = null)
	{
		curl_setopt($this->curlObj,CURLOPT_PUT,true);
		$resource = fopen('php://temp', 'rw');
		$data = $this->prepare_data($data);
		$bytes = fwrite($resource,$data);
		rewind($resource);
		if($bytes !== false)
		{
			curl_setopt($this->curlObj,CURLOPT_INFILE,$resource);
			curl_setopt($this->curlObj,CURLOPT_INFILESIZE,$bytes);
		}
		else
		{
			throw new Exception('Could not write PUT data to php://temp');
		}

		return $this->execute($url);	
	}

	public function delete($url,$data = null)
	{
		curl_setopt($this->curlObj,CURLOPT_CUSTOMREQUEST,'DELETE');
		if($data != null)
		{
			$resource = fopen('php://temp', 'rw');
			$data = $this->prepare_data($data);
			$bytes = fwrite($resource,$data);
			rewind($resource);
			if($bytes !== false)
			{
				curl_setopt($this->curlObj,CURLOPT_INFILE,$resource);
				curl_setopt($this->curlObj,CURLOPT_INFILESIZE,$bytes);
			}
			else
			{
				throw new Exception('Could not write DELETE data to php://temp');
			}
		}

		return $this->execute($url);
	}

	public function patch($url,$data = null)
	{
		curl_setopt($this->curlObj,CURLOPT_CUSTOMREQUEST,'PATCH');
		if($data != null)
		{
			$resource = fopen('php://temp', 'rw');
			$data = json_encode($data);
			$bytes = fwrite($resource,$data);
			rewind($resource);
			if($bytes !== false)
			{
				curl_setopt($this->curlObj,CURLOPT_INFILE,$resource);
				curl_setopt($this->curlObj,CURLOPT_INFILESIZE,$bytes);
			}
			else
			{
				throw new Exception('Could not write PATCH data to php://temp');
			}
		}

		return $this->execute($url);
	}

	public function setAcceptType($type)
	{
		// xml  -> text/xml
		// html -> text/html
		// json -> application/json
		// text -> text/plain
		// Else -> whatever was there
		if(is_array($type))
		{
			foreach($type as $k => $v)
			{
				$v = strtolower($v);
				if($v == "xml")
					$type[$k] = "text/xml";
				elseif($v == "html")
					$type[$k] = "text/html";
				elseif($v == "json")
					$type[$k] = "application/json";
				elseif($v == "text")
					$type[$k] = "text/plain";
			}
			$type = implode(",",$type);
		}
		$this->httpHeader[] = "Accept: ".$type;
	}

	public function json() 
	{
		return json_decode($this->result);
	}

	public function xml() 
	{
		$xml = simplexml_load_string($this->result);
		return $xml;
	}	

	public function prepare_data($data)
	{
		if(is_array($data))
		{
			$data = http_build_query($data,'var');
		}
		else
		{
			parse_str($data,$tmp);
			$data = "";
			$first = true;
			foreach($tmp as $k => $v)
			{
				if(!$first)
				{
					$data .= "&";
				}
				$data .= $k . "=" . urlencode($v);
				$first = false;
			}
		}

		return $data;
	}

}


?>