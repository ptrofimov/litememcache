<?php

require_once 'TinyMemcacheClient.class.php';

$client = new TinyMemcacheClient( 'localhost:11211' );
var_dump( $client->set( 'key', 'value' ) );
var_dump( $client->get( 'key' ) );