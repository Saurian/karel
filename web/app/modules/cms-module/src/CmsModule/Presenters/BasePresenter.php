<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    BasePresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Controls\IFlashMessageControlFactory;
use CmsModule\Entities\UserEntity;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\IChangePasswordFormFactory;
use CmsModule\InvalidStateException;
use CmsModule\Repositories\UserRepository;

use Kdyby\Translation\Translator;
use Nette;
use Tracy\Debugger;
use Tracy\ILogger;
use WebLoader\Compiler;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\LoaderFactory;

class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

    /** @var LoaderFactory @inject */
    public $webLoader;

    /** @var IChangePasswordFormFactory @inject */
    public $changePasswordFormFactory;

    /** @var UserRepository @inject */
    public $userRepository;

    /** @var IFlashMessageControlFactory @inject */
    public $flashMessageControl;

    /** @var Nette\Caching\IStorage @inject */
    public $storage;

    /** @var UserEntity */
    protected $userEntity;

    /** @var bool */
    protected $enableAjaxLayout = true;

    public $signaled = false;


    protected function startup()
    {
        parent::startup();
        $user = $this->getUser();

        if (!$user->isAllowed($this->name, $this->action)) {
            $this->flashMessage($this->translator->translate('Please set administrator\'s account.'), 'warning');
            $this->getUser()->logout();
//            $this->redirect(':Cms:Login:', array('backlink' => $this->storeRequest('+ 10 seconds')));

            if ($this->isAjax()) {
                // hack ajax redirect!

                $args = [];
                $args[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
                $this->payload->redirect = $this->link(':Cms:Login:', $args);

                $data = [
                    'redirect' => $this->payload->redirect,
                    'backlink' => $this->storeRequest('+30 seconds'),
                ];
                header('Content-Type: application/json');
                echo json_encode($data);
                die();
            }

            $this->redirect(':Cms:Login:');
        }

        if ($this->getUser()->isLoggedIn()) {
            $this->userEntity = $this->userRepository->find($this->getUser()->getId());
        }

        $action  = ($this->action != 'default') ? ' ' . $this->action : null;
        $this->template->pageIdName = str_replace(':', '', $this->name);
        $this->template->pageClassName = $action;

        if ($this->getSignal() !== null) {
            $this->signaled = true;
        }
    }

    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace('carl');
        parent::checkRequirements($element);
    }


    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->production = Debugger::$productionMode;
        $this->ajaxLayout();
    }

    protected function afterRender()
    {
        parent::afterRender();
        if ($this->isAjax() && !$this->signaled && !$this->isControlInvalid()) {
            $this->redrawControl('navigation');
            $this->redrawControl('title');
            $this->redrawControl('content');
        }
    }


    protected function ajaxLayout()
    {
        if ($this->isAjax() && !$this->enableAjaxLayout) $this->setLayout(false);
    }


    /**
     * ajax redirect
     *
     * @param string $uri
     * @param null   $controlsToRedraw
     * @param bool   $redrawAll
     */
    public function ajaxRedirect($uri = 'this', $controlsToRedraw = null, $redrawControls = true)
    {
        if ($this->isAjax()) {
            if ($controlsToRedraw) {

                if (is_array($controlsToRedraw)) {
                    foreach ($controlsToRedraw as $item) {

                        if (isset($this[$item])) {
                            /** @var Nette\Application\UI\IRenderable $control */
                            $control = $this[$item];
                            if ($control instanceof Nette\Application\UI\IRenderable) {
                                $control->redrawControl();
                            }
                        }
                    }

                } else {
                    if (isset($this[$controlsToRedraw])) {
                        /** @var Nette\Application\UI\IRenderable $control */
                        $control = $this[$controlsToRedraw];
                        if ($control instanceof Nette\Application\UI\IRenderable) {
                            $control->redrawControl();
                        }
                    }
                }
            }

            if ($redrawControls) {
                if (is_array($redrawControls)) {
                    foreach ($redrawControls as $redrawControl) {
                        $this->redrawControl($redrawControl);
                    }

                } elseif (is_string($redrawControls)) {
                    $this->redrawControl($redrawControls);

                } else {
                    $this->redrawControl();
                }
            }

        } else {
            $this->redirect($uri);
        }
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

    /**
     * must overwritten
     *
     * @param $nestedData
     * @param $elementId
     */
    public function handleItemsNested($nestedData, $elementId)
    {
        $this->payload->_nested_success = true;
        $this->ajaxRedirect('this', null, ['items', 'flash']);
    }


    public function actionLogout()
    {
        $this->getUser()->logout();
        $this->flashMessage($this->translator->translate('you have been logged out of the system'), 'info');
//        $this->redirect(':Cms:Login:', array('backlink' => $this->storeRequest()));

        $this->redirect(':Cms:Login:');
    }

    public function translateMessage($domain = 'messages')
    {
        return $this->translator->domain($domain);
    }


    protected function createComponentUserChangePasswordForm($name)
    {
        $form = $this->changePasswordFormFactory->create();

        $form->addFormClass(['ajax']);
        $form->create();

        /** @var Nette\Forms\Controls\SubmitButton $send */
        $send = $form['send'];
        $send->setAttribute('data-dismiss', 'modal');

        $form->setTranslator($this->translator->domain('messages.forms.changePasswordForm'));
        $form->bootstrap3Render();
        $form->bindEntity($this->userEntity);

        $form->onSuccess[] = function (BaseForm $form, $values) {

            /** @var UserEntity $entity */
            $entity = $form->getEntity();
            $entity->setPassword($values->password)->resetNewPassword();

            $em = $form->getEntityMapper()->getEntityManager();
            $em->persist($entity)->flush();
            $form->setValues([], true);
            $form->bindEntity($this->userEntity);

            $title   = $this->translator->translate('messages.forms.changePasswordForm.flash.title');
            $message = $this->translator->translate('messages.forms.changePasswordForm.flash.text');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

            $this->ajaxRedirect('this', null, [ 'flash', 'userChangePasswordForm']);
        };

        return $form;
    }


    /**
     * @param $name
     *
     * @return \CmsModule\Controls\FlashMessageControl
     */
    protected function createComponentFlashMessageControl($name)
    {
        $control = $this->flashMessageControl->create();
        return $control;
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