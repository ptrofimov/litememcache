<?php
/**
 * PHPUnit tests for TinyMemcacheClient
 * 
 * 1. Install PHPUnit: https://github.com/sebastianbergmann/phpunit/
 * 2. Run: phpunit TinyMemcacheClient.test.php
 * 
 * @author Petr Trofimov
 */
require_once ( 'TinyMemcacheClient.class.php' );

class TinyMemcacheClientTest extends PHPUnit_Framework_TestCase
{
	public function testMain()
	{
		$client = new TinyMemcacheClient( 'localhost:11211' );
		
		$this->assertSame( 'TinyMemcacheClient', get_class( $client ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', 'string' ) );
		$this->assertSame( 'string', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', 1 ) );
		$this->assertSame( '1', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', 0 ) );
		$this->assertSame( '0', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', 3.14 ) );
		$this->assertSame( '3.14', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', 0.0 ) );
		$this->assertSame( '0', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', true ) );
		$this->assertSame( '1', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', false ) );
		$this->assertSame( '', $client->get( 'key' ) );
		
		$this->assertSame( $client::REPLY_STORED, $client->set( 'key', null ) );
		$this->assertSame( '', $client->get( 'key' ) );
		
		$this->assertSame( null, $client->get( 'wrong-key' ) );
	}
}