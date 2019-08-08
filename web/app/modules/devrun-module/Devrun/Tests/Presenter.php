<?php
/**
 * Copyright (c) 2014 Pavel Paulík (http://www.pavelpaulik.cz)
 * Presenter
 *
 * @created 14.7.14
 * @package ${MODULE_NAME}Module
 * @author  Saurian
 */

namespace Devrun\Tests;

use Devrun\CmsModule\Presenters\LoginPresenter;
use Nette\Application\PresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Security\User;


class Presenter extends BaseTestCase
{

    const PATTERN_ERROR = '%<ul class="errors"[^>]*>(.*?)</ul>%si';
    const PATTERN_FLASH = '%<div role="alert[^>]*>(.*?)</div>%si';


    /** @var \Nette\Application\IPresenter */
    protected $presenter;

    protected $presenterName;


    /**
     * @param $presenterName string Fully qualified presenter name.
     */
    public function init($presenterName)
    {
        /** @var PresenterFactory $presenterFactory */
        $presenterFactory                  = $this->getContainer()->getByType('Nette\Application\IPresenterFactory');
        $this->presenter                   = $presenterFactory->createPresenter($presenterName);
        $this->presenterName               = $presenterName;
        $this->presenter->autoCanonicalize = FALSE;
    }


    public function createPresenter($name)
    {
        $presenterFactory            = $this->getContainer()->getByType('Nette\Application\IPresenterFactory');
        $presenter                   = $presenterFactory->createPresenter($name);
        $presenter->autoCanonicalize = FALSE;
        return $presenter;
    }


    /**
     * @param $name
     *
     * @return \Nette\Application\UI\Presenter
     */
    public function getPresenter($name = '')
    {
        if ($this->presenter != NULL && $name == '') {
            $presenter = $this->presenter;

        } else {
            $presenter = $this->getContainer()
                ->getByType('Nette\Application\IPresenterFactory')
                ->createPresenter($name);

            $presenter->autoCanonicalize = FALSE;
            $this->presenter             = $presenter;

        }

        return $presenter;
    }


    /**
     * @param array  $params
     * @param array  $post
     * @param string $presenterName
     * @param string $method
     * @param array  $files
     *
     * @return Request
     */
    public function getRequest(array $params = array(), array $post = array(), $presenterName = '', $method = 'POST', array $files = array())
    {
        $pName = $presenterName ? $presenterName : $this->presenterName;
        return new Request($pName, $method, $params, $post, $files);
    }


    /**
     * response create from Request
     *
     * @param Request $request
     * @param null    $presenter
     *
     * @return \Nette\Application\IResponse
     */
    public function getResponse(Request $request, $presenter = NULL)
    {
        $presenter = $presenter === NULL
            ? $this->getPresenter($this->presenterName)
            : $presenter;

        return $presenter->run($request);
    }


    public function _test($action, $method = 'GET', $params = array(), $post = array())
    {
        $params['action'] = $action;
        $request          = new \Nette\Application\Request($this->presenterName, $method, $params, $post);
        $response         = $this->presenter->run($request);
        return $response;
    }


    public function _testAction($action, $method = 'GET', $params = array(), $post = array())
    {
        $response = $this->_test($action, $method, $params, $post);

        \Tester\Assert::true($response instanceof \Nette\Application\Responses\TextResponse);
        \Tester\Assert::true($response->getSource() instanceof \Nette\Templating\ITemplate);

        $html = (string)$response->getSource();
        $dom  = \Tester\DomQuery::fromHtml($html);
        \Tester\Assert::true($dom->has('title'));

        return $response;
    }


    /**
     * generate html from response
     *
     * @param \Nette\Application\Responses\TextResponse
     * @param bool $saveToFile
     *
     * @return string
     */
    protected function createHtmlFromResponse($response, $saveToFile = true)
    {
        $html = '';
        if ($response instanceof TextResponse) {
            $html = (string)$response->getSource();
            if ($saveToFile) {
                $out = __DIR__ . '/htmlOutput.html';
                file_put_contents($out, $html);
            }
        }

        return $html;
    }


    /**
     * vrátí errory z formuláře
     *
     * @param TextResponse $response
     * @param bool         $generateHtml
     *
     * @return string
     */
    protected function getErrors($response, $generateHtml = false)
    {
        $err_out = '';
        if ($response instanceof TextResponse) {
            $html = $this->createHtmlFromResponse($response, false);

            if (preg_match(self::PATTERN_ERROR, $html, $matches)) {
                $err_out = $matches[1];
            }

            if ($generateHtml) {
                $out = __DIR__ . '/htmlOutput.html';
                file_put_contents($out, $html);
            }

        }
        return $err_out;
    }


    protected function getFlashes($response)
    {
        $err_out = '';
        if ($response instanceof TextResponse) {
            $html = $this->createHtmlFromResponse($response, false);

            if (preg_match(self::PATTERN_FLASH, $html, $matches)) {
                $err_out = $matches[1];
            }

        }
        return $err_out;
    }


    /**
     * přihlásí se pomocí login formuláře
     *
     * @param null $username
     * @param null $password
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @return User
     */
    public function sendLoginForm($username = null, $password = null)
    {
        $this->getContainer()->user->logout();

        $testUser = $this->getContainer()->getParameters()['testUser'];

        $userLoginAction = 'Cms:Login';

        $request = new Request($userLoginAction, 'POST', array(
            'action' => 'default',
            'do'     => 'loginForm-submit',
        ), array(
            "username"    => $username ? $username : $testUser['login'],
            "password" => $password ? $password : $testUser['password'],
            "test"     => true,
            "send"     => "Login",
        ));

        /** @var LoginPresenter $presenter */
        $presenter = $this->createPresenter($userLoginAction);
        $response  = $this->getResponse($request, $presenter);

        $err = '';

        if ($response instanceof TextResponse) {
            $html = (string)$response->getSource();
            $err  = $this->getErrors($response);

            $out = __DIR__ . '/htmlOutput.html';
            file_put_contents($out, $html);

            throw new \PHPUnit_Framework_AssertionFailedError('není redirect, vygenerován html, ' . $err);
        }

        $this->assertEmpty($err, 'Chyba formuláře ' . $err);
        return $presenter->getUser();
    }


}