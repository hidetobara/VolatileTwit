<?php
require_once( 'Log.php' );


class BaseApi
{
	protected $class = 'base';
	protected $array;
	protected $format;
	
	function __construct( $opt=null )
	{
		$this->array = array( 'status' => 'undefined' );
		$this->format = "xml";
	}
	
	function assign( $name, $value )
	{
		$this->array[ $name ] = $value;
	}
	
	function run()
	{
		try
		{
			$this->checkFormat();
			$this->initialize();
			$this->handle();
		}
		catch(Exception $ex)
		{
			$path = LOG_DIR . 'web/' . $this->class . date('Ymd') . '.log';
			Log::singleton('file', $path, 'ERR', array('mode'=>0777))
				->err( $ex->getMessage() . " @" . $ex->getFile() . "#" . $ex->getLine() );
			$this->array = array(
				'status' => 'fail',
				'error' => $ex->getMessage() );
		}
		$this->display();
		$this->finalize();
	}

	protected function checkFormat()
	{
		$validFormats = array( 'xml', 'json', 'txt' );
		if( in_array( $_REQUEST['format'], $validFormats ) ) $this->format = $_REQUEST['format'];
	}
	
	protected function initialize()
	{
	}
	
	protected function handle()
	{
	}
	
	protected function display()
	{
		switch($this->format)
		{
			case 'xml':
				$context = $this->toXml( $this->array, 'root' );				
				header( "Content-type: text/xml" );
				header( "Content-Length: " . count($context) );
				print $context;
				break;
				
			case 'json':
				$context = json_encode( $this->array );
				header( "Content-type: application/json" );
				header( "Content-Length: " . count($context) );
				print $context;
				
			case 'txt':
				var_dump( $this->array );
		}
	}
	
	protected function finalize()
	{
	}
	
	/**
	 * The main function for converting to an XML document.
	 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
	 * @param array $data
	 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
	 * @param SimpleXMLElement $xml - should only be used recursively
	 * @return string XML
	 */
	protected function toXml( $data, $rootNodeName = 'data', $xml=null )
	{
		if ($xml == null) $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
 
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "unknownNode_". (string) $key;
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_]/i', '', $key);
			 
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				$this->toXml($value, $rootNodeName, $node);
			}
			else 
			{
				// add single node.
				$value = htmlspecialchars($value);
				$xml->addChild($key,$value);
			} 
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}
}