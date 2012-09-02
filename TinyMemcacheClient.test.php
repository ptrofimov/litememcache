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
	}
}