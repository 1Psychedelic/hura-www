<?php

namespace VCD\Admin\DevTools\UI;

use Hafo\DI\Container\DefaultContainer;
use Hafo\NetteBridge\Forms\FormFactory;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

class ClassFactoryControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {

            \Tracy\Debugger::$maxDepth = 5;
            \Tracy\Debugger::$maxLength = 1000;

            $topns = [
                'VCD2' => 'VCD2',
                'Hafo' => 'Hafo'
            ];

            /// NEW MODEL
            $f = $this->container->get(FormFactory::class)->create();
            $f->addSelect('topns', '', $topns);
            $f->addText('cls');
            $f->addTextArea('deps');
            $f->addSubmit('create', 'Vytvořit model');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['create']) {
                    $data = $f->getValues(TRUE);
                    $parts = explode('\\', $data['cls']);
                    array_unshift($parts, $data['topns']);
                    $className = array_pop($parts);
                    $interfaceName = array_pop($parts);
                    $deps = array_filter(explode("\n", $data['deps']));
                    $dependencies = [];
                    foreach($deps as $dep) {
                        list($k, $v) = explode(':', $dep);
                        $dependencies[trim($k)] = trim($v);
                    }
                    $shortDependencies = array_map(function($val) {
                        $parts = explode('\\', $val);
                        return end($parts);
                    }, $dependencies);

                    // save interface
                    $output = $this->createContents('modelInterface', [
                        'namespace' => implode('\\', $parts),
                        'interface' => $interfaceName,
                        'use' => [],
                    ]);
                    $this->save(implode('\\', $parts) . '\\' . $interfaceName, $output);

                    // save class
                    $output = $this->createContents('modelClass', [
                        'namespace' => implode('\\', $parts) . '\\' . $interfaceName,
                        'className' => $className,
                        'implements' => $parts[count($parts) - 1] . '\\' . $interfaceName,
                        'dependencies' => $shortDependencies,
                        'use' => [
                            implode('\\', $parts) => NULL
                        ] + array_combine(array_values($dependencies), array_fill(0, count($dependencies), NULL)),
                    ]);
                    $this->save(implode('\\', $parts) . '\\' . $interfaceName . '\\' . $className, $output);

                    $this->redirect('this');
                }
            };
            $this->addComponent($f, 'newModel');


            /// NEW CONTROL
            $f = $this->container->get(FormFactory::class)->create();
            $f->addSelect('topns', '', $topns);
            $f->addText('ns');
            $f->addText('className');
            $f->addText('params');
            $f->addCheckbox('factory', 'Továrna');
            $f->addCheckbox('form', 'Formulář');
            $f->addSubmit('create');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['create']) {
                    $data = $f->getValues(TRUE);
                    $params = array_filter(explode(',', $data['params']));
                    $parts = explode('\\', $data['ns']);
                    array_unshift($parts, $data['topns']);
                    array_push($parts, 'UI');
                    $className = $data['className'];

                    // save control
                    $output = $this->createContents('controlClass', [
                        'namespace' => implode('\\', $parts),
                        'className' => $className,
                        'params' => $params,
                        'form' => $data['form'],
                        'use' => [
                            'Hafo\DI\Container' => NULL,
                            'Hafo\NetteBridge\Forms\FormFactory' => NULL,
                            'Nette\Application\UI\Control' => NULL,
                            'Nette\Application\UI\Form' => NULL,
                            'Nette\Database\Context' => NULL,
                        ],
                    ]);
                    $this->save(implode('\\', $parts) . '\\' . $className . '\\' . $className . 'Control', $output);
                    $this->save(implode('\\', $parts) . '\\' . $className . '\\' . 'default', $data['form'] ? '{control form}' : '', 'latte');

                    if($data['factory']) {
                        $output = $this->createContents('controlFactory', [
                            'namespace' => implode('\\', $parts),
                            'className' => $className,
                            'params' => $params,
                            'form' => $data['form'],
                            'use' => [
                                'Hafo\DI\Container' => NULL,
                            ],
                        ]);
                        $this->save(implode('\\', $parts) . '\\' . $className . '\\' . $className . 'ControlFactory', $output);
                    }

                    $this->redirect('this');
                }
            };
            $this->addComponent($f, 'newControl');

            // NEW ENTITY
            $f = $this->container->get(FormFactory::class)->create();
            //$f->addSelect('topns', '', ['AD\Orm' => 'AD\Orm']);
            $f->addText('topns')->setDefaultValue('VCD2');
            $f->addText('name', '');
            $f->addText('tableName', '');
            $f->addSubmit('create');
            $f->onSuccess[] = function(Form $f) {
                if($f->isSubmitted() === $f['create']) {
                    $data = $f->getValues(TRUE);

                    // save entity
                    $output = $this->createContents('entityEntity', [
                        'namespace' => $data['topns'],
                        'className' => $data['name'],
                        'use' => [
                            'Hafo\Orm\Entity\Entity' => NULL
                        ]
                    ]);
                    $this->save($data['topns'] . '\\' . $data['name'], $output);

                    // save mapper
                    $output = $this->createContents('entityMapper', [
                        'namespace' => $data['topns'] . '\\Mapper',
                        'className' => $data['name'],
                        'tableName' => $data['tableName'],
                        'use' => [
                            'Hafo\Orm\Mapper\Mapper' => NULL
                        ]
                    ]);
                    $this->save($data['topns'] . '\\Mapper\\' . $data['name'] . 'Mapper', $output);

                    // save repository
                    $output = $this->createContents('entityRepository', [
                        'namespace' => $data['topns'] . '\\Repository',
                        'className' => $data['name'],
                        'use' => [
                            'Hafo\Orm\Repository\Repository' => NULL,
                            'Nextras\Orm\Collection\ICollection' => NULL
                        ]
                    ]);
                    $this->save($data['topns'] . '\\Repository\\' . $data['name'] . 'Repository', $output);

                    $this->redirect('this');
                }
            };
            $this->addComponent($f, 'newEntity');

            if($this->container instanceof DefaultContainer) {
                $refl = new \ReflectionClass($this->container);
                $factories = $refl->getProperty('factories');
                $factories->setAccessible(TRUE);
                $this->template->factories = $factories->getValue($this->container);
                $factories->setAccessible(FALSE);
                $this->template->dumps = [];
                $this->template->dumpService = function($factory) {
                    $service = $factory($this->container);
                    return \Tracy\Debugger::dump($service, TRUE);
                };
                $this->template->makeKey = function($key) {
                    return $this->makeKey($key);
                };
            }

        };
    }

    function handleDump($name) {
        $refl = new \ReflectionClass($this->container);
        $factories = $refl->getProperty('factories');
        $factories->setAccessible(TRUE);
        $factory = $factories->getValue($this->container)[$name];
        $factories->setAccessible(FALSE);
        $this->template->factories = [$name => $factory];
        $this->template->dumps = [
            $name => \Tracy\Debugger::dump($factory($this->container), TRUE)
        ];
        if($this->presenter->isAjax()) {
            $this->redrawControl('container');
            $this->presenter->payload->url = $this->link('this');
        } else {
            $this->redirect('this');
        }
    }

    private function makeKey($key) {
        return Strings::webalize($key, '0-9A-Za-z_');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }
    
    private function db() {
        return $this->container->get(Context::class);
    }

    private function save($fqn, $content, $extension = 'php') {
        $parts = explode('\\', $fqn);
        $filename = array_pop($parts);
        $dir = $this->container->get('app') . '/' . implode('/', $parts);
        $file = $dir . '/' . $filename . '.' . $extension;
        FileSystem::createDir($dir);
        if(file_exists($file)) {
            throw new \Exception('Nope!');
        }
        file_put_contents($file, $content);
    }

    private function createContents($name, $params) {
        $tpl = $this->createTemplate();
        $tpl->setFile(__DIR__ . '/@' . $name . '.latte');
        $tpl->setParameters($params);
        ob_start();
        $tpl->render();
        $output = ob_get_clean();
        return $output;
    }

}
