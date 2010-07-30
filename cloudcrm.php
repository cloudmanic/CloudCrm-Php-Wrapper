<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cloudcrm 
{
	function Cloudcrm()
	{
		$this->CI =& get_instance();
		$this->errormsg = array();	
		$this->path = "";
		$this->response = "";
		$this->cdcrmhost = $this->CI->config->item('cdcrmhost');
		$this->cdcrmnamespace = $this->CI->config->item('cdcrmnamespace');
		$this->cdcrmkey  = $this->CI->config->item('cdcrmkey');
		
		//
		// Older versions of curl do not have our
		// SSL cert installed. Download the latest 
		// from here http://curl.haxx.se/docs/caextract.html
		//
		// This should be left blank unless you are getting a ssl verify errors
		$this->CaPath = ""; 
	}
	
	/* ----------------------- Subscriptions --------------------- */
	
	//
	// Connect and add a subscription
	//
	function add_subscription($data)
	{
		$this->path = "/api/subscriptions/add";
		return $this->_send_request($data);
	}
	
	
	/* ----------------------- Customers --------------------- */
	
	//
	// Connect and get all customers.
	//
	function get_customers()
	{
		$this->path = "/api/customers/get";
		return $this->_send_request();
	}

	//
	// Connect and get a customer by id
	//
	function get_customer($id)
	{
		$this->path = "/api/customers/get/id/$id";
		return $this->_send_request();
	}

	//
	// Connect and update a customer by id.
	//
	function update_customer($data, $id)
	{
		$this->path = "/api/customers/update/id/$id";
		return $this->_send_request($data);
	}
	
	//
	// Connect and add a customer
	//
	function add_customer($data)
	{
		$this->path = "/api/customers/add";
		return $this->_send_request($data);
	}
	
	//
	// Connect and delete a customer by id.
	//
	function delete_customer($id)
	{
		$this->path = "/api/customers/delete/id/$id";
		return $this->_send_request();
	}
	
	//
	// Connect and add a tag to a customer
	//
	function add_customer_tag($id, $tag)
	{
		$this->path = "/api/customers/addtag/id/$id";
		return $this->_send_request(array('Tag' => $tag));
	}
	
	/* -------------- Helper Function for Lib ----------------- */
	
	//
	// Use curl to sent the request to the cloudcrm server.
	//
	private function _send_request($content = NULL)
	{
		$posturl = "https://" . $this->cdcrmhost . $this->path . "/format/serialize";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->cdcrmnamespace . ':' . $this->cdcrmkey);
		
		if(! is_null($content)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
			curl_setopt($ch, CURLOPT_POST, 1);
		}
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if(! empty($this->CaPath))
			curl_setopt($ch, CURLOPT_CAPATH, $this->CaPath);
			
		// Send request
		$this->response = curl_exec($ch);
		//echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $this->response;
		
		switch(curl_getinfo($ch, CURLINFO_HTTP_CODE)) 
		{
			case '200': 
				return unserialize($this->response); 
			break;
			
			case '404': 
				return 0; 
			break;
			
			default:
				return 0;
			break;
		}		
	} 				
}
?>