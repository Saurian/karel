<?php


namespace FrontModule\Forms;

use Nette\Forms\Form;

interface IPlayListFormFactory
{
    /** @return PlayListForm */
    function create();
}


class PlayListForm extends BaseForm
{

    private $devices = [];


    public function createForm()
    {
        $this->addSelect('did', 'Zařízení', $this->devices)
             ->setPrompt('-- vyberte --')
             ->addRule(Form::FILLED, "vyberte zařízení");

        $this->addText('ssid', 'Wifi aktivita')
             ->setAttribute('placeholder', "ssid pro log dotazu");

        $this->addText('p', 'heslo')
             ->setAttribute('placeholder', "heslo");

        $this->addText('realizedFrom', 'Od')
             ->setAttribute('placeholder', "datum zahájení")
             ->setAttribute('data-provide', "datepicker")
             ->setAttribute('data-date-format', "yyyy-mm-dd");


        $this->addText('realizedTo', 'Do')
             ->setAttribute('placeholder', "datum konce")
             ->setAttribute('data-provide', "datepicker")
             ->setAttribute('data-date-format', "yyyy-mm-dd");


        $this->addCheckbox('activeDevice', 'pouze aktivní zařízení')
            ->setDefaultValue(true);

        $this->addCheckbox('activeCampaigns', 'pouze aktivní kampaně')
            ->setDefaultValue(true);

        $this->addSubmit('send', 'send')->getControlPrototype()->class = 'btn btn-primary btn-md';

//        $this->onSuccess[] = array($this, 'success');

//        $this->getElementPrototype()->class = 'margin-bottom-0';

        return $this;
    }



    /**
     * @param array $devices
     * @return PlayListForm
     */
    public function setDevices(array $devices): PlayListForm
    {
        $this->devices = $devices;
        return $this;
    }





}