<?php

namespace Perfumer\Framework\Router\Http;

use Perfumer\Framework\Bundle\Resolver\HttpResolver;
use Perfumer\Framework\Router\RouterInterface;
use Perfumer\Helper\Arr;
use Perfumer\Framework\Proxy\Response as InternalResponse;
use Symfony\Component\HttpFoundation\Response;

class FastRouteRouter implements RouterInterface
{
    /**
     * @var FastRoute\simpleDispatcher
     */
    protected $fast_router;

    /**
     * @var HttpResolver
     */
    protected $bundle_resolver;

    /**
     * @var Response
     */
    protected $response;

    protected $input;

    protected $options = [];

    protected $http_fields = [];

    public function __construct(HttpResolver $bundle_resolver, FastRoute\simpleDispatcher $fast_router, $options = [])
    {
        $this->fast_router = $fast_router;
        $this->bundle_resolver = $bundle_resolver;

        $default_options = [
            'auto_null' => true,
            'auto_trim' => true,
            'data_type' => 'query_string',
            'allowed_actions' => ['get', 'post', 'head', 'options'],
            'not_found_attributes' => ['framework/http', 'exception/plain', 'pageNotFound']
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return array
     */
    public function getAllowedActions()
    {
        return $this->options['allowed_actions'];
    }

    /**
     * @return array
     */
    public function getNotFoundAttributes()
    {
        return $this->options['not_found_attributes'];
    }

    /**
     * @return bool
     */
    public function isHttp()
    {
        return true;
    }

    /**
     * @return array
     */
    public function dispatch()
    {
        $this->http_fields = $_GET;

        $info = $this->fast_router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['PATH_INFO']);

        switch ($info[0]) {
            case FastRoute\Dispatcher::FOUND:
                $attributes = explode('.', $info[1]);
                $resource = $attributes[0];
                $action = $attributes[1];
                $this->http_fields = array_merge($this->http_fields, $info[2]);
                break;
            default:
                $not_found_attributes = $this->getNotFoundAttributes();
                $resource = $not_found_attributes[1];
                $action = $not_found_attributes[2];
                break;
        }

        $this->input = file_get_contents("php://input");

        $data_type = $this->options['data_type'];

        // Get query parameters and args depending from type of data in the http request body
        $args = [];

        if ($data_type == 'query_string') {
            parse_str($this->getInput(), $args);;
        } else if ($data_type == 'json') {
            $args = $this->input ? json_decode($this->input, true) : [];

            if (!is_array($args)) {
                $args = [];
            }
        }

        $this->http_fields = array_merge($this->http_fields, $args);

        // Trim all fields if auto_trim setting enabled
        if ($this->options['auto_trim']) {
            $this->http_fields = Arr::trim($this->http_fields);
        }

        // Convert empty strings to null values if auto_null setting enabled
        if ($this->options['auto_null']) {
            $this->http_fields = Arr::convertValues($this->http_fields, '', null);
        }

        return [$resource, $action, []];
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if ($this->response === null) {
            $this->response = new Response();
        }

        return $this->response;
    }

    /**
     * @param InternalResponse $response
     */
    public function sendResponse(InternalResponse $response)
    {
        $this->getResponse()->setContent($response->getContent())->send();
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param string|array|null $keys
     * @param mixed $default
     * @return mixed
     */
    public function getFields($keys = null, $default = null)
    {
        if ($keys === null) {
            return $this->http_fields;
        } elseif (is_array($keys)) {
            return Arr::fetch($this->http_fields, $keys, true, $default);
        } else {
            return isset($this->http_fields[$keys]) ? $this->http_fields[$keys] : $default;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setField($key, $value)
    {
        $this->http_fields[$key] = $value;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->http_fields = array_merge($this->http_fields, $fields);
    }
}
