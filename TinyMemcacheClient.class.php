<?php
/**
 * TinyMemcacheClient - tiny, simple and pure-PHP alternative to Memcache and Memcached clients
 * 
 * @see https://github.com/memcached/memcached/blob/master/doc/protocol.txt
 * 
 * @author Petr Trofimov <petrofimov@yandex.ru>
 */
class TinyMemcacheClient
{
	const REPLY_STORED = 'STORED'; // Reply to storage commands: to indicate success
	const REPLY_NOT_STORED = 'NOT_STORED'; // Reply to storage commands: to indicate the data was not stored, but not because of an error
	const REPLY_EXISTS = 'EXISTS'; // Reply to storage commands: to indicate that the item you are trying to store with a "cas" command has been modified since you last fetched it
	const REPLY_NOT_FOUND = 'NOT_FOUND'; // Reply to storage commands: to indicate success
	

	private $_socket;
	
	public function __construct( $server )
	{
		$this->_socket = stream_socket_client( $server );
	}
	
	public function set( $key, $value, $exptime = 0, $flags = 0, $noreply = null )
	{
		$cmd = sprintf( 'set %s %d %d %d%s' . "\r\n", $key, $flags, $exptime, strlen( $value ), 
			isset( $noreply ) ? ' 1' : '' );
		$cmd .= $value . "\r\n";
		fwrite( $this->_socket, $cmd );
		$line = fgets( $this->_socket );
		return substr( $line, 0, strlen( $line ) - 2 );
	}
	
	public function get( $key )
	{
		$keys = is_array( $key ) ? $key : array( $key );
		
		$cmd = sprintf( 'get %s' . "\r\n", implode( ' ', $keys ) );
		fwrite( $this->_socket, $cmd );
		
		$values = array();
		
		while ( true )
		{
			$line = fgets( $this->_socket );
			$line = substr( $line, 0, strlen( $line ) - 2 );
			
			list( $cmd ) = explode( ' ', $line );
			
			if ( $cmd == 'END' )
			{
				if ( !is_array( $key ) )
				{
					$values[] = null;
				}
				break;
			}
			elseif ( $cmd == 'ERROR' )
			{
				throw new Exception( 'Error: client sent a nonexistent command name' );
			}
			elseif ( $cmd == 'CLIENT_ERROR' )
			{
				list( $cmd, $msg ) = explode( ' ', $line );
				throw new Exception( 'Error: the input doesn\'t conform to the protocol in some way: ' . $msg );
			}
			elseif ( $cmd == 'SERVER_ERROR' )
			{
				list( $cmd, $msg ) = explode( ' ', $line );
				throw new Exception( 'Error: some sort of server error prevents the server from carrying out the command: ' . $msg );
			}
			elseif ( $cmd == 'VALUE' )
			{
				list( $cmd, $key1, $exp, $length ) = explode( ' ', $line );
				$value = fread( $this->_socket, $length + 2 );
				$values[] = substr( $value, 0, strlen( $value ) - 2 );
			}
			else
			{
				throw new Exception( 'System error' );
			}
		}
		//var_dump( $values );
		return is_array( $key ) ? $values : $values[ 0 ];
	}
}