<?php
require_once( '../configure.php' );
require_once( CONF_DIR . 'path.php' );
require_once( INCLUDE_DIR . 'keywords/KeywordsTable.class.php' );


class MakeFilter
{
	private $m_Keywords;

	function __construct( $options=null )
	{
		$this->m_Keywords = $options['Keywords'] ? $options['Keywords'] : KeywordsTable::singleton($options);
	}

	private function isIgnored( $record )
	{
		if( in_array($record['parse'], array('動詞','名詞','形容詞','形容詞','副詞')) ) return false;
		return true;
	}

	function run_idf( $path )
	{
		$f = fopen( $path, 'w' );

		$max = 1;
		$min = 1;
		foreach( $this->m_Keywords->m_Table as $word => $record )
		{
			if( $this->isIgnored($record) ) continue;
			$count = $record['count'];
			if( $max < $count ) $max = $count;
		}

		foreach( $this->m_Keywords->m_Table as $word => $record )
		{
			if( $this->isIgnored($record) ) continue;

			$index = $record['index'];
			$count = $record['count'];
			if( !$index || !$count ) continue;

			$rate = log($max/$count) / log($max/$min);
			fprintf( $f, "%d,%f,%s\n", $index, $rate, $word );
		}
		fclose( $f );
	}
}
$keywords = KeywordsTable::singleton();
$keywords->loadTable( ConfPath::keywords() );
$instance = new MakeFilter( array('Keywords'=>$keywords) );
$instance->run_idf( ConfPath::keywordsFilter() );
print "WARN: Add special person value, like hajimehoshi, shokos etc.";
?>