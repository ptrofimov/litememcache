<?php
class TinyMemcacheClient
{
	private $_socket;
	
	public function __construct( $server )
	{
		$this->_socket = stream_socket_client( $server );
	}
	
	public function set( $key, $value, $exptime = 0, $flags = 0 )
	{
		$cmd = sprintf( 'set %s %d %d %d' . "\r\n", $key, $flags, $exptime, strlen( $value ) );
		$cmd .= $value . "\r\n";
		fwrite( $this->_socket, $cmd );
		$line = fgets( $this->_socket );
		return substr( $line, 0, strlen( $line ) - 2 );
	}
	
	public function get( $key )
	{
		$cmd = sprintf( 'get %s' . "\r\n", $key );
		fwrite( $this->_socket, $cmd );
		$line = fgets( $this->_socket );
		$line = substr( $line, 0, strlen( $line ) - 2 );
		//var_dump('line',$key,$line);
		if ( $line == 'END' )
		{
			$value = null;
		}
		else
		{
			list( $cmd, $key, $exp, $length ) = explode( ' ', $line );
			$value = fread( $this->_socket, $length + 2 );
			$value = substr( $value, 0, strlen( $value ) - 2 );
		}
		return $value;
	}
}