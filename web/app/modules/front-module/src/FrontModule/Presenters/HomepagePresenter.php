<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    HomepagePresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Repositories\CampaignRepository;
use Kdyby\Translation\Translator;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Strings;
use Ublaboo\ImageStorage\ImageStoragePresenterTrait;

class HomepagePresenter extends BasePresenter
{

    use ImageStoragePresenterTrait;

    /** @var IMailer @inject */
    public $mailer;

    /** @var Translator @inject */
    public $translator;

    /** @var CampaignRepository @inject */
    public $campaignRepository;



    public function renderTestMail($id)
    {
        $latte  = new \Latte\Engine;
        $params = [
            'url'      => $this->link("//:Cms:Login:forgottenPassword"),
        ];

        $url = $this->link("//:Cms:Login:forgottenPassword");


        $tmp = <<<EON
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Registrace účtu</title>
	<style>
		body {
			background-color: #f2fbff;
		}
	</style>
</head>
<body>
<p>Dobrý den,</p>
<p>Na stránce {$url} Vám byly zřízeny přístupové údaje.</p>
<p>Pro aktivaci Vašeho účtu prosím klikněte <a href="{$url}">sem</a>.</p>
<p>&nbsp;</p>
</body>
</html>
EON;


        $message = new Message();
        $message->setFrom("Karl von Bahnhof <info@cms.pixatori.com>")
            ->addTo($id)
            ->setHtmlBody($tmp);

//        dump($message);
//        die();

        $this->mailer->send($message);

        die("odesláno");


    }


    public function actionDefault()
    {
        if (!$this->dev) {
            $title = $this->translator->translate('messages.homePage.redirect.title');
            $message = $this->translator->translate('messages.homePage.redirect.text');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
            $this->redirect(':Cms:Campaign:');
        }
    }


    public function renderDefault()
    {
        /** @var CampaignEntity[] $campaignResults */
        $campaignResults = $this->campaignRepository->createQueryBuilder('e')
            ->addSelect('d')
            ->addSelect('dev')
            ->join('e.mediaData', 'd')
            ->join('e.devices', 'dev')
            ->where('d.identifier IS NOT NULL')
            ->getQuery()
            ->getResult();

        $devices = [];
        $campaigns = [];
        foreach ($campaignResults as $campaignResult) {
            $name = $campaignResult->getDevices()->first()->name;
            $devices[$webName = Strings::webalize($name)] = $name;
            $campaigns[] = $campaignResult;
        }

        $devices = array_unique($devices);

        $this->template->devices = $devices;
        $this->template->campaigns = $campaigns;
    }


    public function getWebalizeFirstDevice(CampaignEntity $campaign)
    {
        $name = $campaign->getDevices()->first()->name;
        return Strings::webalize($name);
    }



}