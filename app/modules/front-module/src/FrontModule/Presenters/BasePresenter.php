<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    BasePresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Presenters;

use CmsModule\InvalidStateException;
use Nette;
use Flame\Application\UI\Presenter;
use Tracy\Debugger;
use Tracy\ILogger;
use WebLoader\Compiler;
use WebLoader\Nette\CssLoader;

class BasePresenter extends Presenter
{

    /** @var integer @persistent */
    public $dev;

    /** @var Nette\Caching\IStorage @inject */
    public $storage;


    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->production = Debugger::$productionMode;
    }


    public function flashMessage($message, $type = 'info', $title = '', array $options = array())
    {
        $id = $this->getParameterId('flash');
        $messages = $this->getPresenter()->getFlashSession()->$id;
        $messages[] = $flash = (object)array(
            'message' => $message,
            'title'   => $title,
            'type'    => $type,
            'options' => $options,
        );
        $this->getTemplate()->flashes = $messages;
        $this->getPresenter()->getFlashSession()->$id = $messages;
        return $flash;
    }


    protected function createComponentCss($name)
    {
        $wwwDir = $this->context->parameters['wwwDir'];

        if (!$layoutFile = $this->getLayout()) {
            $layoutFile = $this->findLayoutTemplateFile();
        }

        $key          = $layoutFile . ".$name";
        $cache        = new Nette\Caching\Cache($this->storage, 'resources');
        $buildCssName = 'Default';
        $files   = new \WebLoader\FileCollection($wwwDir);

        if (!$data = $cache->load($key)) {

            $content = file_get_contents($layoutFile);

            if (preg_match_all('#<!--\s*build:css\s*(?P<css>.*?)\s*-->(?P<content>.*?)<!--\s*endbuild\s*-->#is', $content, $output_array)) {

                /*
                 * var $buildCssName
                 * extract service name, [screen for example]
                 * {$basePath}/css/screen.min.css
                 */

                $buildFilename = explode('.', basename($output_array['css'][0]));
                $buildCssName  = ucfirst($buildFilename[0]);

                if (preg_match_all('#<link.*?href=\"([^htp].*?)\"#is', $output_array['content'][0], $matches)) {
                    $styles = preg_replace('/{\$basePath}/', $wwwDir, $matches[1]);
                    $files->addFiles($styles);
                }

                if (preg_match_all('#<link.*?href=\"(?P<remotes>https?.*?)\"#is', $output_array['content'][0], $matches)) {
                    $files->addRemoteFiles($matches['remotes']);
                }

                $fileKeys = $files->getFiles();
                $fileKeys[] = $layoutFile;

                $cache->save($key, [$files, $buildCssName], array(
                    Nette\Caching\Cache::FILES => $fileKeys,
                ));
            }

        } else {
            list($files, $buildCssName) = $data;
        }

        if ($this->context->hasService($serviceName = "webloader.css{$buildCssName}Compiler")) {

            /** @var Compiler $compiler*/
            $compiler = $this->context->getService($serviceName);
            if (count($files->getFiles()) > 0 || count($files->getRemoteFiles()) > 0) {
                $compiler->setFileCollection($files);
            }

        } else {

            if ($buildCssName == "Default") {

                if (!$this->context->hasService($serviceName = "webloader.css{$buildCssName}Compiler")) {
                    throw new InvalidStateException("Not any build in layout, we have must $buildCssName service [$serviceName]");
                }

                /** @var Compiler $compiler */
                $compiler = $this->context->getService($serviceName);

            } else {
                Debugger::log(__METHOD__ . " service $serviceName not found.", ILogger::WARNING);

                $compiler = \WebLoader\Compiler::createCssCompiler($files, $wwwDir . '/webtemp');

                /*
                $compiler->addFilter(new \WebLoader\Filter\VariablesFilter(array('foo' => 'bar')));
                $compiler->addFilter(function ($code) {
                    return \cssmin::minify($code, "remove-last-semicolon");
                });
                */
                $compiler->setJoinFiles(true);
            }
        }

        return new CssLoader($compiler, $this->getHttpRequest()->getUrl()->basePath . 'webtemp');
    }


}