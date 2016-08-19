<?php

namespace Perfumer\Framework\Router\Http;

use Perfumer\Framework\BundleResolver\HttpResolver;
use Perfumer\Framework\Router\RouterInterface;
use Perfumer\Helper\Arr;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Symfony\Component\HttpFoundation\Response;

class DefaultRouter implements RouterInterface
{
    /**
     * @var HttpResolver
     */
    protected $bundle_resolver;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string
     */
    protected $input;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $http_prefixes = [];

    /**
     * @var mixed
     */
    protected $http_id;

    /**
     * @var array
     */
    protected $http_id_array;

    /**
     * @var array
     */
    protected $http_fields = [];

    /**
     * @param HttpResolver $bundle_resolver
     * @param array $options
     */
    public function __construct(HttpResolver $bundle_resolver, $options = [])
    {
        $this->bundle_resolver = $bundle_resolver;

        $default_options = [
            'auto_null' => true,
            'auto_trim' => true,
            'data_type' => 'query_string',
            'default_url' => 'home',
            'prefixes' => [],
            'prefix_options' => [],
            'allowed_actions' => ['get', 'post', 'head', 'options'],
            'not_found_attributes' => ['framework', 'exception/plain', 'pageNotFound']
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
        $action = strtolower($_SERVER['REQUEST_METHOD']);

        if ($prefixes = $this->options['prefixes']) {
            $this->http_prefixes = array_fill_keys($prefixes, null);

            $prefix_options = $this->options['prefix_options'];

            if (is_array($prefix_options)) {
                foreach ($prefixes as $prefix) {
                    if (isset($prefix_options[$prefix]['default_value'])) {
                        $this->http_prefixes[$prefix] = $prefix_options[$prefix]['default_value'];
                    }
                }
            }
        }

        if ($_SERVER['PATH_INFO'] == '/') {
            $url = $this->options['default_url'];
        } else {
            $url = trim($_SERVER['PATH_INFO'], '/');

            // Try to define prefixes
            if ($prefixes = $this->options['prefixes']) {
                $url = explode('/', $url);

                $prefix_options = $this->options['prefix_options'];

                while ($prefixes && $url) {
                    $one_prefix = array_shift($prefixes);
                    $one_url = $url[0];

                    if ($this->validateUrlPartForPrefix($one_prefix, $one_url, $prefix_options)) {
                        $this->http_prefixes[$one_prefix] = $one_url;

                        array_shift($url);
                    }
                }

                $url = count($url) > 0 ? implode('/', $url) : $this->options['default_url'];
            }

            // Try to define id
            if ($url) {
                if ((int) $url > 0) {
                    $this->http_id = $url;
                } else {
                    preg_match('/\/[0-9]+/', $url, $matches, PREG_OFFSET_CAPTURE);

                    if (isset($matches[0])) {
                        $slash_position = $matches[0][1];

                        $this->http_id = substr($url, $slash_position + 1);

                        if ($slash_position > 0) {
                            $url = substr($url, 0, $slash_position);
                        }
                    }
                }
            }
        }

        $this->input = file_get_contents("php://input");

        $args = [];

        $data_type = $this->options['data_type'];

        // Get query parameters and args depending from type of data in the http request body
        if ($data_type == 'query_string') {
            parse_str($this->input, $args);
        } elseif ($data_type == 'json') {
            $args = $this->input ? json_decode($this->input, true) : [];

            if (!is_array($args)) {
                $args = [];
            }
        }

        $this->http_fields = array_merge($_GET, $args);

        // Trim all args if auto_trim setting enabled
        if ($this->options['auto_trim']) {
            $this->http_fields = Arr::trim($this->http_fields);
        }

        // Convert empty strings to null values if auto_null setting enabled
        if ($this->options['auto_null']) {
            $this->http_fields = Arr::convertValues($this->http_fields, '', null);
        }

        return [$url, $action, []];
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
     * @param string $content
     */
    public function sendResponse($content)
    {
        $this->getResponse()->setContent($content)->send();
    }

    /**
     * @param string $url
     * @param mixed $id
     * @param array $query
     * @param array $prefixes
     * @return string
     */
    public function generateUrl($url, $id = null, $query = [], $prefixes = [])
    {
        $generated_url = trim($url, '/');

        if ($generated_url) {
            $generated_url = '/' . $generated_url;
        }

        if ($this->options['prefixes']) {
            if ($prefixes) {
                $prefixes = Arr::fetch($prefixes, $this->options['prefixes']);
                $prefixes = array_merge($this->getPrefix(), $prefixes);
            } else {
                $prefixes = $this->getPrefix();
            }

            $generated_url = '/' . implode('/', $prefixes) . $generated_url;
        }

        if ($id) {
            $generated_url .= is_array($id) ? '/' . implode('/', $id) : '/' . $id;
        }

        if ($query) {
            $query_string = http_build_query($query, '', '&');

            if ($query_string) {
                $generated_url .= '?' . $query_string;
            }
        }

        $generated_url = $this->bundle_resolver->getPrefix() . $generated_url;

        if (!$generated_url) {
            $generated_url = '/';
        }

        return $generated_url;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPrefix($name = null, $default = null)
    {
        if ($name === null) {
            return $this->http_prefixes;
        }

        return isset($this->http_prefixes[$name]) ? $this->http_prefixes[$name] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     * @throws ProxyException
     */
    public function setPrefix($name, $value)
    {
        if (!in_array($name, $this->options['prefixes'])) {
            throw new ProxyException('Prefix "' . $name . '" is not registered in configuration');
        }

        $this->http_prefixes[$name] = $value;

        return $this;
    }

    /**
     * @param int|null $index
     * @return mixed
     */
    public function getId($index = null)
    {
        if ($index === null) {
            return $this->http_id;
        }

        if ($this->http_id_array === null) {
            $this->http_id_array = explode('/', $this->http_id);
        }

        return isset($this->http_id_array[$index]) ? $this->http_id_array[$index] : null;
    }

    /**
     * @param mixed $id
     * @param int|null $index
     * @return $this
     */
    public function setId($id, $index = null)
    {
        if ($index === null) {
            $this->http_id = $id;
        } else {
            if ($this->http_id_array === null) {
                $this->http_id_array = explode('/', $this->http_id);
            }

            $this->http_id_array[$index] = $id;
        }

        return $this;
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

    /**
     * @param string $prefix
     * @param string $part
     * @param array $options
     * @return bool
     */
    protected function validateUrlPartForPrefix($prefix, $part, array $options)
    {
        if ($options === null || !isset($options[$prefix])) {
            return true;
        }

        $options = $options[$prefix];

        if (isset($options['white_list'])) {
            return in_array($part, $options['white_list']);
        }

        if (isset($options['black_list'])) {
            return !in_array($part, $options['black_list']);
        }

        if (isset($options['regex'])) {
            return preg_match($options['regex'], $part);
        }

        return true;
    }
}
