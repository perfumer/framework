<?php

namespace Perfumer\Component\Endpoint;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Perfumer\Component\Endpoint\Attributes\Api;
use Perfumer\Component\Endpoint\Attributes\ApiExample;
use Perfumer\Component\Endpoint\Attributes\Attribute;
use Perfumer\Component\Endpoint\Attributes\Out;
use Perfumer\Component\Endpoint\Attributes\Type;
use Symfony\Component\ClassLoader\ClassMapGenerator;

class EndpointGenerator
{
    private string $generatedPath;

    public function __construct(array $options = [])
    {
        if (isset($options['generated_path']) && is_string($options['generated_path'])) {
            $this->generatedPath = $options['generated_path'];
        } else {
            $this->generatedPath = ROOT_DIR . 'generated/endpoint/';
        }
    }

    public function generateDirectory(string $path): void
    {
        $classes = ClassMapGenerator::createMap($path);

        foreach (array_keys($classes) as $class) {
            $this->generate($class);
        }
    }

    public function generate(string $class): string
    {
        $reflection = new \ReflectionClass($class);
        $classParts = explode('\\', $class);
        array_pop($classParts);

        $classGen = new ClassGenerator();
        $classGen->setNamespaceName('Generated\\Endpoint\\' . implode('\\', $classParts));
        $classGen->setExtendedClass('\\Perfumer\\Component\\Endpoint\\AbstractEndpoint');
        $classParts = explode('\\', $class);
        $classParts = end($classParts);
        $classGen->setName($classParts);

        $constructContent = '';
        $constructorGenerator = new MethodGenerator();
        $constructorGenerator->setName('__construct');
        $classGen->addMethodFromGenerator($constructorGenerator);

        foreach ($reflection->getMethods() as $reflectionMethod) {
            $constructContent .= "\$this->in['{$reflectionMethod->getName()}'] = [];".PHP_EOL;
            $constructContent .= "\$this->out['{$reflectionMethod->getName()}'] = [];".PHP_EOL;

            $methodGenerator = new MethodGenerator();
            $methodGenerator->setName($reflectionMethod->getName());
            $classGen->addMethodFromGenerator($methodGenerator);
            $attributes = $reflectionMethod->getAttributes();
            $target = 'in';
            $docBlock = new DocBlockGenerator();
            $docBlockTags = [];
            $apiPath = null;
            $apiDesc = null;
            $apiGroup = null;
            $apiName = null;
            $apiVersion = null;

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if (!$instance instanceof Attribute) {
                    continue;
                }

                if ($instance instanceof Api) {
                    $apiPath = $instance->path;
                    $apiDesc = $instance->desc;
                    $apiGroup = $instance->group;
                    $apiName = $instance->name;
                    $apiVersion = $instance->version;
                    continue;
                }

                if ($instance instanceof Out) {
                    $target = 'out';
                    $docBlockTags[] = [
                        'name'        => 'apiUse',
                        'description' => 'SuccessBody',
                    ];
                    continue;
                }

                if ($instance instanceof Type) {
                    $args = $attribute->getArguments();

                    $constructContent .= "\$this->{$target}['{$reflectionMethod->getName()}'][] = \\".get_class($instance)."::fromArray(";
                    $constructContent .= var_export($args, true);
                    $constructContent .= ");".PHP_EOL;
                    $fieldKey = $instance->name;
                    if (!$instance->required) {
                        $fieldKey = '['.$fieldKey.']';
                    }

                    $docBlockTags[] = [
                        'name'        => $target === 'in' ? 'apiBody' : 'apiSuccess',
                        'description' => sprintf('{%s} %s %s', $instance->type, $fieldKey, $instance->desc),
                    ];
                }

                if ($instance instanceof ApiExample) {
                    $docBlockTags[] = [
                        'name'        => $instance->apidocAnnotation,
                        'description' => sprintf('{json} %s %s%s', $instance->desc, PHP_EOL, json_encode($instance->json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)),
                    ];
                }
            }

            $docBlock->setTags([
                [
                    'name'        => 'api',
                    'description' => sprintf('{%s} %s %s', $reflectionMethod->getName(), $apiPath, $apiName),
                ],
                [
                    'name'        => 'apiName',
                    'description' => $apiName
                ],
                [
                    'name'        => 'apiGroup',
                    'description' => $apiGroup
                ],
            ]);

            if ($apiDesc) {
                $docBlock->setTag([
                    'name'        => 'apiDescription',
                    'description' => $apiDesc
                ]);
            }

            if ($apiVersion) {
                $docBlock->setTag([
                    'name'        => 'apiVersion',
                    'description' => $apiVersion,
                ]);
            }

            $docBlock->setTags($docBlockTags);
            $methodGenerator->setDocBlock($docBlock);
            $constructorGenerator->setBody($constructContent);
        }

        $output_name = $this->generatedPath . '/' . str_replace('\\', '/', trim($class)) . '.php';
        $folder = explode('/', $output_name);
        array_pop($folder);
        @mkdir(implode('/', $folder), 0777, true);
        $code = '<?php' . PHP_EOL . PHP_EOL . $classGen->generate();

        file_put_contents($output_name, $code);

        return 'Generated\\Endpoint\\' . $class;
    }
}