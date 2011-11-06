<?php
class ConfPath
{
	const VERSION = 'delta';

	static function rawStatusList()
	{
		return sprintf("%sstatus/*.log.gz", LOG_DIR);
	}
	static function keywords()
	{
		return sprintf("%skeywords.%s.csv", DATA_DIR, self::VERSION);
	}
	static function keywordsFilter()
	{
		return sprintf("%skeywords_filter.idf.%s.csv", DATA_DIR, self::VERSION);
	}
	static function statusList()
	{
		return sprintf("%sstatus_list.%s.csv", DATA_DIR, self::VERSION);
	}
	static function statusListMarked()
	{
		return sprintf("%sstatus_list.mark.%s.csv", DATA_DIR, self::VERSION);
	}
	static function reflectUser( $id )
	{
		return sprintf("%sreflect.%02d.%s.csv", DATA_DIR, $id, self::VERSION);
	}
	static function scoreUser( $id )
	{
		return sprintf("%sscore.%02d.%s.csv", DATA_DIR, $id, self::VERSION);
	}
	
	static function stateMatrix( $name )
	{
		return sprintf("%sstate_matrix.%s.csv", DATA_DIR, $name);
	}
	static function stateTexts( $name )
	{
		return sprintf("%sstate_texts.%s.csv", DATA_DIR, $name);
	}
	
	static function ipcaBin()
	{
		return ROOT_DIR . 'data/ipcaMain.bin';
	}
}
?>