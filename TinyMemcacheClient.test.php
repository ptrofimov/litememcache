<?php
/**
 * @author Petr Trofimov
 */
require_once ( 'include/config.php' );

class ValueTest extends PHPUnit_Framework_TestCase
{
	public function dataProvider()
	{
		$data = array();
		foreach ( $GLOBALS[ 'clients' ] as $client )
		{
			$scheme = new TestScheme( $client );
			$data[] = array( $scheme, 'value' );
			$data[] = array( $scheme->struct, 'value' );
			$data[] = array( $scheme->struct[ 'key' ], 'value' );
		}
		return $data;
	}
	
	/**
	 * @dataProvider dataProvider
	 */
	public function testMain( NScheme_Structure_Base $base, $key )
	{
		$base->{$key} = 'value1';
		$this->assertSame( 'value1', $base->{$key} );
		
		$base->{$key} = 'value2';
		$this->assertSame( 'value2', $base->{$key} );
	}
	
	/**
	 * @dataProvider dataProvider
	 */
	public function testException( NScheme_Structure_Base $base, $key )
	{
		$this->setExpectedException( 'NScheme_Exception' );
		$base->invalid_key = 'value';
	}
}