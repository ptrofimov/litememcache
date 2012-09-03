<?php
/**
 * TinyMemcacheClient - tiny, simple and pure-PHP alternative to Memcache and Memcached clients
 * 
 * @see https://github.com/memcached/memcached/blob/master/doc/protocol.txt
 * 
 * @link https://github.com/ptrofimov/tinymemcacheclient
 * @author Petr Trofimov <petrofimov@yandex.ru>
 */
class TinyMemcacheClient
{
	private $_socket, $_replies, $_lastReply;
	
	public function __construct( $server )
	{
		$this->_socket = stream_socket_client( $server );
		$this->replies = array( 
			'STORED' => true, 
			'NOT_STORED' => false, 
			'EXISTS' => false, 
			'OK' => true, 
			'ERROR' => false, 
			'DELETED' => true, 
			'NOT_FOUND' => false );
	}
	
	public function getLastReply()
	{
		return $this->_lastReply;
	}
	
	public function query( $query )
	{
		$query = is_array( $query ) ? implode( "\r\n", $query ) : $query;
		fwrite( $this->_socket, $query . "\r\n" );
		$line = fgets( $this->_socket );
		$this->_lastReply = $reply = substr( $line, 0, strlen( $line ) - 2 );
		return isset( $this->_replies[ $reply ] ) ? $this->_replies[ $reply ] : $reply;
	}
	
	public function set( $key, $value, $exptime = 0, $flags = 0 )
	{
		return $this->query( array( "set $key $flags $exptime " . strlen( $value ), $value ) );
	}
	
	public function append( $key, $value )
	{
		return $this->query( array( "append $key 0 0 " . strlen( $value ), $value ) );
	}
	
	public function prepend( $key, $value )
	{
		return $this->query( array( "prepend $key 0 0 " . strlen( $value ), $value ) );
	}
	
	public function add( $key, $value, $exptime = 0, $flags = 0 )
	{
		return $this->query( array( "add $key $flags $exptime " . strlen( $value ), $value ) );
	}
	
	public function replace( $key, $value, $exptime = 0, $flags = 0 )
	{
		return $this->query( array( "replace $key $flags $exptime " . strlen( $value ), $value ) );
	}
	
	public function del( $key )
	{
		return $this->query( "delete $key" );
	}
	
	public function incr( $key, $value = 1 )
	{
		return $this->query( "incr $key $value" );
	}
	
	public function decr( $key, $value = 1 )
	{
		return $this->query( "decr $key $value" );
	}
	
	public function flushAll( $exptime = 0 )
	{
		return $this->query( "flush_all $exptime" );
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
				list( $cmd, $key1, $flags, $length ) = explode( ' ', $line );
				$value = fread( $this->_socket, $length + 2 );
				$values[] = substr( $value, 0, strlen( $value ) - 2 );
			}
			else
			{
				throw new Exception( 'System error' );
			}
		}
		return is_array( $key ) ? $values : $values[ 0 ];
	}
}