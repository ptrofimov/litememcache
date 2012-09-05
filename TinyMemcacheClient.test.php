<?php
/**
 * PHPUnit tests for TinyMemcacheClient
 * 
 * To run tests:
 * 1. Install PHPUnit: https://github.com/sebastianbergmann/phpunit/
 * 2. Run: phpunit TinyMemcacheClient.test.php
 * 
 * @link https://github.com/ptrofimov/tinymemcacheclient
 * @author Petr Trofimov
 */
require_once ( 'TinyMemcacheClient.class.php' );

class TinyMemcacheClientTest extends PHPUnit_Framework_TestCase
{
	public function __construct( $name = NULL, array $data = array(), $dataName = '' )
	{
		parent::__construct( $name, $data, $dataName );
		$this->_client = new TinyMemcacheClient( 'localhost:11211' );
	}
	
	public function testClassName()
	{
		$this->assertSame( 'TinyMemcacheClient', get_class( $this->_client ) );
	}
	
	public function providerSetDifferentDataTypes()
	{
		$data = array();
		$data[] = array( $this->_client, 'key', 'string', 'string' );
		$data[] = array( $this->_client, 'key', 1, '1' );
		$data[] = array( $this->_client, 'key', 0, '0' );
		$data[] = array( $this->_client, 'key', 3.14, '3.14' );
		$data[] = array( $this->_client, 'key', 0.0, '0' );
		$data[] = array( $this->_client, 'key', true, '1' );
		$data[] = array( $this->_client, 'key', false, '' );
		$data[] = array( $this->_client, 'key', null, '' );
		return $data;
	}
	
	/**
	 * @dataProvider providerSetDifferentDataTypes
	 */
	public function testSetDifferentDataTypes( $client, $key, $set, $get )
	{
		$this->assertSame( true, $client->set( $key, $set ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( $get, $client->get( $key ) );
	}
	
	public function testSeqSetGet()
	{
		$client = $this->_client;
		$this->assertSame( true, $client->set( 'key', 'value1' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value1', $client->get( 'key' ) );
		$this->assertSame( true, $client->set( 'key', 'value2' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value2', $client->get( 'key' ) );
	}
	
	public function testMultipleGet()
	{
		$client = $this->_client;
		$this->assertSame( true, $client->set( 'key1', 'value1' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( true, $client->set( 'key2', 'value2' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value1', $client->get( 'key1' ) );
		$this->assertSame( 'value2', $client->get( 'key2' ) );
		$this->assertSame( array( 'key2' => 'value2' ), $client->get( array( 'key2' ) ) );
		$this->assertSame( null, $client->get( 'key3' ) );
		$this->assertSame( array( 'key1' => 'value1', 'key2' => 'value2', 'key3' => null ), 
			$client->get( array( 'key1', 'key2', 'key3' ) ) );
		$this->assertSame( array( 'key1' => 'value1', 'key3' => null, 'key2' => 'value2' ), 
			$client->get( array( 'key1', 'key3', 'key2' ) ) );
		$this->assertSame( array( 'key3' => null, 'key1' => 'value1', 'key2' => 'value2' ), 
			$client->get( array( 'key3', 'key1', 'key2' ) ) );
	}
	
	public function testWrongKey()
	{
		$client = $this->_client;
		$this->assertSame( null, $client->get( 'wrong-key' ) );
	}
	
	public function testExpiredKey()
	{
		$client = $this->_client;
		$this->assertSame( true, $client->set( 'key', 'value', 1 ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value', $client->get( 'key' ) );
		usleep( 1100000 );
		$this->assertSame( null, $client->get( 'key' ) );
	}
	
	public function testClientError()
	{
		try
		{
			$this->_client->get( 'key with spaces' );
			$this->assertTrue( false );
		}
		catch ( Exception $ex )
		{
			$this->assertTrue( true );
		}
	}
	
	public function testDeleteKey()
	{
		$client = $this->_client;
		$this->assertSame( true, $client->set( 'key', 'value' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value', $client->get( 'key' ) );
		$this->assertSame( true, $client->del( 'key' ) );
		$this->assertSame( 'DELETED', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( false, $client->del( 'key' ) );
		$this->assertSame( 'NOT_FOUND', $client->getLastReply() );
	}
	
	public function testAppend()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( false, $client->append( 'key', 'value' ) );
		$this->assertSame( 'NOT_STORED', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( true, $client->set( 'key', 'hello' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'hello', $client->get( 'key' ) );
		$this->assertSame( true, $client->append( 'key', ' world' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'hello world', $client->get( 'key' ) );
	}
	
	public function testPrepend()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( false, $client->prepend( 'key', 'value' ) );
		$this->assertSame( 'NOT_STORED', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( true, $client->set( 'key', 'world' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'world', $client->get( 'key' ) );
		$this->assertSame( true, $client->prepend( 'key', 'hello ' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'hello world', $client->get( 'key' ) );
	}
	
	public function testAdd()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( true, $client->add( 'key', 'value1' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value1', $client->get( 'key' ) );
		$this->assertSame( false, $client->add( 'key', 'value2' ) );
		$this->assertSame( 'NOT_STORED', $client->getLastReply() );
		$this->assertSame( 'value1', $client->get( 'key' ) );
	}
	
	public function testReplace()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( false, $client->replace( 'key', 'value1' ) );
		$this->assertSame( 'NOT_STORED', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( true, $client->set( 'key', 'value2' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value2', $client->get( 'key' ) );
		$this->assertSame( true, $client->replace( 'key', 'value3' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value3', $client->get( 'key' ) );
	}
	
	public function testIncr()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( false, $client->incr( 'key' ) );
		$this->assertSame( 'NOT_FOUND', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( true, $client->set( 'key', 1 ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( '1', $client->get( 'key' ) );
		$this->assertSame( '2', $client->incr( 'key' ) );
		$this->assertSame( '2', $client->get( 'key' ) );
	}
	
	public function testDecr()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( false, $client->decr( 'key' ) );
		$this->assertSame( 'NOT_FOUND', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
		$this->assertSame( true, $client->set( 'key', 2 ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( '2', $client->get( 'key' ) );
		$this->assertSame( '1', $client->decr( 'key' ) );
		$this->assertSame( '1', $client->get( 'key' ) );
	}
	
	public function testFlushAll()
	{
		$client = $this->_client;
		$this->assertSame( true, $client->set( 'key1', 'value1' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( true, $client->set( 'key2', 'value2' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( 'value1', $client->get( 'key1' ) );
		$this->assertSame( 'value2', $client->get( 'key2' ) );
		$this->assertSame( true, $client->flushAll() );
		$this->assertSame( 'OK', $client->getLastReply() );
	}
	
	public function testFlags()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( true, $client->set( 'key1', 'value1', 0, 1 ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$result = $client->get( 'key1', true );
		$this->assertSame( 'value1', $result[ 'value' ] );
		$this->assertSame( '1', $result[ 'flags' ] );
		$this->assertTrue( is_numeric( $result[ 'cas' ] ) );
		$this->assertSame( true, $client->set( 'key2', 'value2', 0, 2 ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$result = $client->get( array( 'key1', 'key2' ), true );
		$this->assertSame( 'value1', $result[ 'key1' ][ 'value' ] );
		$this->assertSame( '1', $result[ 'key1' ][ 'flags' ] );
		$this->assertTrue( is_numeric( $result[ 'key1' ][ 'cas' ] ) );
		$this->assertSame( 'value2', $result[ 'key2' ][ 'value' ] );
		$this->assertSame( '2', $result[ 'key2' ][ 'flags' ] );
		$this->assertTrue( is_numeric( $result[ 'key2' ][ 'cas' ] ) );
	}
	
	public function testCas()
	{
		$client = $this->_client;
		$client->del( 'key' );
		$this->assertSame( true, $client->set( 'key', 'value1' ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$result = $client->get( 'key', true );
		$this->assertSame( 'value1', $result[ 'value' ] );
		$this->assertSame( true, $client->cas( 'key', 'value2', $result[ 'cas' ] ) );
		$this->assertSame( 'STORED', $client->getLastReply() );
		$this->assertSame( false, $client->cas( 'key', 'value3', $result[ 'cas' ] ) );
		$this->assertSame( 'EXISTS', $client->getLastReply() );
		$this->assertSame( 'value2', $client->get( 'key' ) );
		$client->del( 'key' );
		$this->assertSame( false, $client->cas( 'key', 'value4', $result[ 'cas' ] ) );
		$this->assertSame( 'NOT_FOUND', $client->getLastReply() );
		$this->assertSame( null, $client->get( 'key' ) );
	}
}