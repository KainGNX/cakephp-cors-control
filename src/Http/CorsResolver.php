<?php
/**
 * Cors Control: A CakePHP plugin for adding CORS headers
 * 
 * @author      Jason Horvah <jason.horvath@greaterdevelopment.com>
 * @link        https://greaterdevelopment.com
 * @since       1.0.0
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CorsControl\Http;

use Cake\Http\Response;
use Cake\Http\CorsBuilder;
use Cake\Http\ServerRequest;
use Cake\Error\FatalErrorException;

class CorsResolver
{

    /**
     * @var array
     */
    const NO_PARAM_METHODS = [
        'allowCredentials'
    ];

    /**
     * @var array
     */
    protected $defaultConfig = [
        'allowOrigin' => ['*'],
        'allowMethods' => [
            'DELETE',
            'GET',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT',
        ],
        'allowHeaders' => ['*'],
        'exposeHeaders' => ['Link'],
        'allowCredentials' => true,
        'maxAge' => 300
    ];

    /**
     * @var \Cake\Http\ServerRequest
     */
    protected $request;

    /**
     * @var \Cake\Http\Client\Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Construct
     *
     * @param \Cake\Http\ServerRequest $request
     * @param \Cake\Http\Client\Response $response
     * @param array $_config
     * @return void
     */
    public function __construct(
        ServerRequest $request,
        Response $response,
        $config = []
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->setConfig($config);
    }

    /**
     * Set Config
     *
     * @param array $config
     * @return void
     */
    protected function setConfig(array $config)
    {
        foreach ($this->defaultConfig as $key => $val) {
            $value = ($config[$key] ?? $val);
            $this->setConfigValue($key, $value);
        }
    }

    /**
     * Set Config Value
     * 
     * @param string $key
     * @param mixed $value
     * @throws FatalErrorException
     * @return void
     */
    protected function setConfigValue(string $key, $value)
    {
        try {
            $configValueType = gettype($this->defaultConfig[$key]);
            $valuePassedType = gettype($value);
            if ($configValueType !== $valuePassedType) {
                throw new FatalErrorException('Invalid value type passed to the config. Config Value Type: ' .  $configValueType . ' - ' . 'Value Passed Type: ' . $valuePassedType);
            }
            $this->config[$key] = $value;
        } catch (FatalErrorException $e) {
            echo $e->getmessage();
        }
    }

    /**
     * Get Builder Response
     *
     * @return \Cake\Http\Client\Response
     */
    public function getBuilderResponse()
    {
        $builder = $this->getBuilder();
        return $this->withBuilderMethods($builder)->build();
    }

    /**
     * Get Builder
     *
     * @return \Cake\Http\CorsBuilder through \Cake\Http\Client\Response::cors()
     */
    protected function getBuilder()
    {
        return $this->response->cors($this->request);
    }

    /**
     * With Builder Methods
     *
     * @param \Cake\Http\CorsBuilder $builder
     * @return \Cake\Http\CorsBuilder $builder
     */
    protected function withBuilderMethods(CorsBuilder $builder)
    {
        foreach ($this->config as $key => $value) {
            if (!in_array($key, self::NO_PARAM_METHODS)) {
                $builder->{$key}($value);
            } elseif ($value === true) {
                $builder->{$key}();
            }
        }

        return $builder;
    }
}
