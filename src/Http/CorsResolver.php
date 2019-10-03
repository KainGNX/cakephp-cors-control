<?php
namespace CorsControl\Http;

use Cake\Http\Client\Response;
use Cake\Http\CorsBuilder;
use Cake\Http\ServerRequest;

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
        'exposeHeaders' => [],
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
            $this->config[$key] = ($config[$key] ?? $val);
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
