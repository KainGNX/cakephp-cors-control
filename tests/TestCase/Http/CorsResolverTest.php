<?php

namespace CorsControl\Test\TestCase;

use Cake\Http\Client\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CorsControl\Http\CorsResolver;

class CorsResolverTest extends TestCase
{
    
    /**
     * @var \Cake\Http\Client\Response
     */
    protected $response;

    /**
     * @var \Cake\Http\ServerRequest
     */
    protected $request;

    /**
     * setup
     * 
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->request = new ServerRequest();
        $this->response = new Response();
        $this->corsResolver = new CorsResolver($this->request, $this->response);
    }

    /**
     * Test Set Config
     * 
     * @return void
     */
    public function testSetConfig()
    {
        // write test
    }

}
