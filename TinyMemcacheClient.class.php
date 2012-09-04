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
	
	private function _readLine()
	{
		$line = fgets( $this->_socket );
		$this->_lastReply = substr( $line, 0, strlen( $line ) - 2 );
		$words = explode( ' ', $this->_lastReply );
		$result = isset( $this->_replies[ $words[ 0 ] ] ) ? $this->_replies[ $words[ 0 ] ] : $words;
		if ( is_null( $result ) )
		{
			throw new Exception( $this->_lastReply );
		}
		return ( is_array( $result ) && count( $result ) == 1 ) ? reset( $result ) : $result;
	}
	
	public function query( $query )
	{
		$query = is_array( $query ) ? implode( "\r\n", $query ) : $query;
		fwrite( $this->_socket, $query . "\r\n" );
		return $this->_readLine();
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
		$words = $this->query( 'get ' . implode( ' ', array_keys( $values ) ) );
		while ( $words !== 'END' )
		{
			if ( $words[ 0 ] !== 'VALUE' )
			{
				throw new Exception( sprintf( 'Invalid reply "%s"', $words[ 0 ] ) );
			}
			$value = fread( $this->_socket, $words[ 3 ] + 2 );
			$values[ $words[ 1 ] ] = substr( $value, 0, strlen( $value ) - 2 );
			$words = $this->_readLine();
		}
		return count( $values ) == 1 ? reset( $values ) : $values;
	}
}