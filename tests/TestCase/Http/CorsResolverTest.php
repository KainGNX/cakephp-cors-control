<?php

namespace CorsControl\Test\TestCase;

use Cake\Http\Client\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CorsControl\Http\CorsResolver;
use ReflectionClass;

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
     * @var \CorsControl\Http\CorsResolver
     */
    protected $corsResolver;

    /**
     * @var ReflectionClass
     */
    protected $reflection;

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
        $this->reflection = new ReflectionClass($this->corsResolver);
    }

    /**
     * Test Set Config
     * 
     * @return void
     */
    public function testSetConfig()
    {
        $property = $this->reflection->getProperty('config');
        $property->setAccessible(true);
        $method = $this->reflection->getMethod('setConfig');
        $method->setAccessible(true);

        $value = $property->getValue($this->corsResolver);
        $this->assertSame('*', $value['allowOrigin'][0]);

        $testConfig = [
            'allowOrigin' => ['invoked'],
            'allowMethods' => ['TEST', 'TESTAGAIN'],
            'invalidPropShouldBeNull' => 'This should not be here after invoke'
        ];
        $method->invoke($this->corsResolver, $testConfig);
        $value = $property->getValue($this->corsResolver);
        $this->assertSame('invoked', $value['allowOrigin'][0]);
        $this->assertCount(2, $value['allowMethods']);
        $this->assertSame('TESTAGAIN', $value['allowMethods'][1]);
        $this->assertFalse(isset($value['invalidPropShouldBeNull']));
    }

    /**
     * Test Set Config Value
     * 
     * @return void
     */
    public function testSetConfigValue()
    {
        $property = $this->reflection->getProperty('config');
        $property->setAccessible(true);
        $method = $this->reflection->getMethod('setConfigValue');
        $method->setAccessible(true);


        $config = $property->getValue($this->corsResolver);
        $method->invoke($this->corsResolver, 'allowOrigin', ['test', 'testmore']);
        $method->invoke($this->corsResolver, 'allowCredentials', false);
        $config = $property->getValue($this->corsResolver);
        $this->assertSame('testmore', $config['allowOrigin'][1]);
        $this->assertFalse($config['allowCredentials']);
        $test = '';
    }

}
