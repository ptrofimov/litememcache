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
		$this->_replies = array( 
			'STORED' => true, 
			'NOT_STORED' => false, 
			'EXISTS' => false, 
			'OK' => true, 
			'ERROR' => false, 
			'DELETED' => true, 
			'NOT_FOUND' => false, 
			'ERROR' => null, 
			'CLIENT_ERROR' => null, 
			'SERVER_ERROR' => null );
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
		$line = substr( $line, 0, strlen( $line ) - 2 );
		list( $reply ) = explode( ' ', $line );
		$this->_lastReply = $reply;
		$result = isset( $this->_replies[ $reply ] ) ? $this->_replies[ $reply ] : $line;
		if ( is_null( $result ) )
		{
			throw new Exception( $line );
		}
		return $result;
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
		$values = array_fill_keys( is_array( $key ) ? $key : array( $key ), null );
		$line = $this->query( 'get ' . implode( ' ', array_keys( $values ) ) );
		
		while ( $line !== 'END' )
		{
			list( $reply ) = explode( ' ', $line );
			if ( $reply !== 'VALUE' )
			{
				throw new Exception( sprintf( 'Invalid reply "%s"', $reply ) );
			}
			list( $reply, $key, $flags, $length ) = explode( ' ', $line );
			$value = fread( $this->_socket, $length + 2 );
			$values[ $key ] = substr( $value, 0, strlen( $value ) - 2 );
			
			$line = fgets( $this->_socket );
			$line = substr( $line, 0, strlen( $line ) - 2 );
		}
		return count( $values ) == 1 ? reset( $values ) : $values;
	}
}