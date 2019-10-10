<?php
/**
 * Cors Control: A CakePHP plugin for adding CORS headers
 * 
 * @author      Jason Horvah <jason.horvath@greaterdevelopment.com>
 * @link        https://greaterdevelopment.com
 * @since       1.0.0
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CorsControl\Test\TestCase;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CorsControl\Http\CorsResolver;
use VisibleUnit\Reflections\BaseReflection;

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
     * @var \VisibleUnit\Reflections\BaseReflection
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
        $this->reflection = new BaseReflection($this->corsResolver);
    }

    /**
     * Test Set Config
     * 
     * @return void
     */
    public function testSetConfig()
    {
        $value = $this->reflection->getPropValue('config');
        $this->assertSame('*', $value['allowOrigin'][0]);

        $testConfig = [
            'allowOrigin' => ['invoked'],
            'allowMethods' => ['TEST', 'TESTAGAIN'],
            'invalidPropShouldBeNull' => 'This should not be here after invoke'
        ];
        $this->reflection->invokeMethod('setConfig', $testConfig);
        $value = $this->reflection->getPropValue('config');
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
        $config = $this->reflection->getPropValue('config');
        $this->reflection->invokeMethod('setConfigValue', 'allowOrigin', ['test', 'testmore']);
        $this->reflection->invokeMethod('setConfigValue', 'allowCredentials', false);
        $config = $this->reflection->getPropValue('config');
        $this->assertSame('testmore', $config['allowOrigin'][1]);
        $this->assertFalse($config['allowCredentials']);
        $test = '';
    }

    /**
     * Test Get Builder Response
     * 
     * @return void
     */
    public function testGetBuilderResponse()
    {
        $response = $this->reflection->invokeMethod('getBuilderResponse');
        $this->assertInstanceOf('Cake\Http\Response', $response);
    }
    
    /**
     * Test Get Builder
     * 
     * @return void
     */
    public function testGetBuilder()
    {
        $builder = $this->reflection->invokeMethod('getBuilder');
        $this->assertInstanceOf('Cake\Http\CorsBuilder', $builder);
    }

    /**
     * Test With Builder Methods
     * 
     * @return void
     */
    public function testWithBuilderMethods()
    {
        // with default headers
        $builderReflection = $this->getNewTestBuilderReflection();
        $corsConfig = $this->reflection->getPropValue('config');
        $builderHeaders = $builderReflection->getPropValue('_headers');
        $this->corsToHeaderAssertions($corsConfig, $builderHeaders);

        // with altered headers
        $corsConfigPassHeaders = [
            'allowOrigin' => ['example.com'],
            'allowMethods' => [   
                'OPTIONS',
                'PATCH',
            ],
            'allowHeaders' => ['TEST-HEADER', 'TEST-HEADER2'],
            'exposeHeaders' => ['CHANGED'],
            'allowCredentials' => false,
            'maxAge' => 500
        ];

        $this->reflection->invokeMethod('setConfig', $corsConfigPassHeaders);
        $builderReflection = $this->getNewTestBuilderReflection();
        $corsConfig = $this->reflection->getPropValue('config');
        $builderHeaders = $builderReflection->getPropValue('_headers');
        $this->corsToHeaderAssertions($corsConfig, $builderHeaders);
    }

    /**
     * Get Test Builder
     * 
     * @return void
     */
    protected function getTestBuilder()
    {
        $builder = $this->reflection->invokeMethod('getBuilder');
        return $this->reflection->invokeMethod('withBuilderMethods', $builder);
    }

    /**
     * Get new Test Builder Reflection
     * 
     * @return void
     */
    protected function getNewTestBuilderReflection()
    {
        $testBuilder = $this->getTestBuilder();
        return new BaseReflection($testBuilder);
    }
    /**
     * Cors To Header Assertions
     * 
     * @param array $corsConfig
     * @param array $builderHeaders
     * @return void
     */
    protected function corsToHeaderAssertions(array $corsConfig, array $builderHeaders)
    {
        $this->assertSame(implode(', ', $corsConfig['allowMethods']), $builderHeaders['Access-Control-Allow-Methods']);
        $this->assertSame(implode(', ', $corsConfig['allowHeaders']), $builderHeaders['Access-Control-Allow-Headers']);
        $this->assertSame(implode(', ', $corsConfig['exposeHeaders']), $builderHeaders['Access-Control-Expose-Headers']);
        $this->assertSame($corsConfig['maxAge'], $builderHeaders['Access-Control-Max-Age']);

        // 'Access-Control-Allow-Credentials' header should not be present when config 'allowCredentials' set to false
        if ($corsConfig['allowCredentials']) {
            $this->assertSame('true', $builderHeaders['Access-Control-Allow-Credentials']);
        } else {
            $this->assertFalse(isset($builderHeaders['Access-Control-Allow-Credentials']));
        }
    }
}
