<?php

namespace Perfumer\Framework\Router\Http;

use FastRoute\Dispatcher;
use Perfumer\Framework\Gateway\HttpGateway;
use Perfumer\Framework\Router\RouterInterface;
use Perfumer\Helper\Arr;
use Symfony\Component\HttpFoundation\Request;

class FastRouteRouter implements RouterInterface
{
    /**
     * @var Dispatcher
     */
    protected $fast_router;

    /**
     * @var HttpGateway
     */
    protected $gateway;

    protected $input;

    protected $options = [];

    protected $http_fields = [];

    /**
     * @param HttpGateway $gateway
     * @param Dispatcher $fast_router
     * @param array $options
     */
    public function __construct(HttpGateway $gateway, Dispatcher $fast_router, $options = [])
    {
        $this->fast_router = $fast_router;
        $this->gateway = $gateway;

        $default_options = [
            'auto_null' => true,
            'auto_trim' => true,
            'data_type' => 'query_string',
            'allowed_actions' => ['get', 'post', 'head', 'options'],
            'not_found_attributes' => ['framework', 'exception/plain', 'pageNotFound']
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return array
     */
    public function getAllowedActions(): array
    {
        return $this->options['allowed_actions'];
    }

    /**
     * @return array
     */
    public function getNotFoundAttributes(): array
    {
        return $this->options['not_found_attributes'];
    }

    /**
     * @return bool
     * @deprecated Use $this->getApplication()->getEnv() instead
     */
    public function isHttp(): bool
    {
        return true;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function dispatch($request): array
    {
        $path_info = $request->getPathInfo();

        if ($this->gateway->getPrefix()) {
            $path_info = substr($request->getPathInfo(), strlen($this->gateway->getPrefix()));
        }

        $this->http_fields = $request->query->all();

        $info = $this->fast_router->dispatch($request->getMethod(), $path_info);

        switch ($info[0]) {
            case Dispatcher::FOUND:
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

        $this->input = $request->getContent();

        $data_type = $this->options['data_type'];

        // Get query parameters and args depending from type of data in the http request body
        $args = [];

        if ($data_type == 'query_string') {
            parse_str($this->input, $args);
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
