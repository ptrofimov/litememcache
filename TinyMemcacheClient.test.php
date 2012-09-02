<?php
/**
 * @author Petr Trofimov
 */
require_once ( 'TinyMemcacheClient.class.php' );

class TinyMemcacheClientTest extends PHPUnit_Framework_TestCase
{
	public function testMain()
	{
		$client = new TinyMemcacheClient( 'localhost:11211' );
		
		$this->assertSame( 'TinyMemcacheClient', get_class( $client ) );
		
		$this->assertSame( 'STORED', $client->set( 'key', 'value' ) );
		$this->assertSame( 'value', $client->get( 'key' ) );
		$this->assertSame( null, $client->get( 'wrong-key' ) );
	}
}