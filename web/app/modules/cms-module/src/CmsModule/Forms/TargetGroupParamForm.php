<?php


namespace CmsModule\Forms;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\TargetGroupParamEntity;
use CmsModule\Entities\TargetGroupParamValueEntity;
use CmsModule\Facades\ReachFacade;
use CmsModule\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;

interface ITargetGroupParamFormFactory
{
    /** @return TargetGroupParamForm */
    function create();
}

class TargetGroupParamForm extends BaseForm
{

    /** @var ReachFacade @inject */
    public $reachFacade;

    protected $autoButtonClass = false;


    /** @return TargetGroupParamForm */
    public function create()
    {
        $removeParamEvent = callback($this, 'removeTargetGroupParam');
        $addValueEvent = callback($this, 'addTargetGroupValue');
        $removeValueEvent = callback($this, 'removeTargetGroupValue');

        $paramIndexConfig = [
            0 => [
                'addValue' => false,
                'removeParam' => false,
                'disableName' => true,
                'values' => [
                    0 => ['remove' => false, 'disable' => true],
                    1 => ['remove' => false, 'disable' => true],
                ]
            ],
            1 => [
                'addValue' => true,
                'removeParam' => false,
                'disableName' => true,
                'values' => [
                    0 => ['remove' => false],
                ]
            ]
        ];

        /** @var \Kdyby\Replicator\Container $targetGroupParams */
        $targetGroupParams = $this->addDynamic('targetParams', function (Container $targetGroupParam) use ($paramIndexConfig, $removeParamEvent, $removeValueEvent, $addValueEvent) {

            $parentName = $targetGroupParam->getName();
            $paramsComponents = array_keys((array)$targetGroupParam->getParent()->getComponents());
            $paramIndex = array_search($parentName, $paramsComponents);

            $targetGroupName = $targetGroupParam->getName();

            $canRemove = $paramIndexConfig[$paramIndex]['removeParam'] ?? true;
            $canDisable = $paramIndexConfig[$paramIndex]['disableName'] ?? false;
            $canAddValue = $paramIndexConfig[$paramIndex]['addValue'] ?? true;

            $input = $targetGroupParam->addText('name', 'Název parametru')
                ->setAttribute('placeholder', "Název parametru")
                ->setAttribute('class', 'form-control')
                ->addRule(Form::FILLED);

            if ($canDisable) {
                $input->setAttribute('readonly', 'readonly');
            }

            $targetGroupValues = $targetGroupParam->addDynamic('values', function (Container $targetGroupValue) use ($paramIndexConfig, $removeValueEvent) {

                $parentName = $targetGroupValue->getParent()->getParent()->getName();

                $targetParamsComponents = array_keys((array)$targetGroupValue->getParent()->getParent()->getParent()->getComponents());
                $paramIndex = array_search($parentName, $targetParamsComponents);

                $valuesComponents = array_keys((array)$targetGroupValue->getParent()->getComponents());
                $valueName = $targetGroupValue->getName();
                $valueIndex = array_search($valueName, $valuesComponents);

                $targetGroupName = $targetGroupValue->getParent()->getParent()->getName();
                $valueGroupName = $targetGroupValue->getName();

                $canRemove = $paramIndexConfig[$paramIndex]['values'][$valueIndex]['remove'] ?? true;
                $canDisable = $paramIndexConfig[$paramIndex]['values'][$valueIndex]['disable'] ?? false;

                $input = $targetGroupValue->addText('name', 'Hodnota')
                    ->setAttribute('placeholder', "hodnota")
                    ->setAttribute('class', 'form-control')
                    ->addRule(Form::FILLED);

                if ($canDisable) {
                    $input->setAttribute('readonly', 'readonly');
                }

                if ($canRemove) {
                    $targetGroupValue->addSubmit('removeValue', 'Remove Value')
                        ->setAttribute('class', 'btn btn-md btn-danger')
                        ->setValidationScope(FALSE) # disables validation
                        ->onClick[] = $removeValueEvent;
                }

            }, 0);


            if ($canAddValue) {
                $targetGroupValues->addSubmit('addValue', 'ADD Value')
                    ->setAttribute('class', 'btn btn-md btn-block btn-primary')
                    ->setValidationScope(FALSE) # disables validation
                    ->onClick[] = $addValueEvent;
            }

            if ($canRemove) {
                $targetGroupParam->addSubmit('removeParam', 'Remove param')
                    ->setValidationScope(FALSE) # disables validation
                    ->onClick[] = $removeParamEvent;

            }

        }, 0 );


        $targetGroupParams->addSubmit('addParam', 'Add param')
            ->setAttribute('class', 'btn btn-md btn-block btn-primary')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addTargetGroupParam'];


        $this->addSubmit('send', 'Odeslat')->setAttribute('class', 'btn btn-block btn-md btn-success')
            ->setAttribute('data-dismiss', 'modal')
            ->onClick[] = [$this, 'formSuccess'];

//        $this->onSuccess[] = array($this, 'success');

        $this->addFormClass(['ajax']);

        return $this;
    }

    protected function attached($presenter)
    {
        parent::attached($presenter);

        if (!$this->isSubmitted()) {
            $entity = $this->getEntity();

            if ($entity->getTargetParams()->count() == 0) {

                /** @var \Kdyby\Replicator\Container $targetParams */
                $targetParams = $this['targetParams'];

                $maxNewId = $this->generateNewId($targetParams);
                $genderContainer = $targetParams->createOne("_new_$maxNewId");
                $paramName = $genderContainer->getComponent('name');
                $paramName->setDefaultValue("Pohlaví");

                $maxNewId++;
                $ageContainer = $targetParams->createOne("_new_$maxNewId");

                /** @var TextInput $paramName */
                $paramName = $ageContainer->getComponent('name');
                $paramName->setDefaultValue("Věk");

                $genderValueContainer = $genderContainer['values'];

                $maxNewId = $this->generateNewId($genderValueContainer);
                /** @var Container $genderContainer2 */
                $genderContainer2 = $genderValueContainer->createOne("_new_$maxNewId");
                $paramName = $genderContainer2->getComponent('name');
                $paramName->setDefaultValue("muži");

                $maxNewId++;
                $genderContainer2 = $genderValueContainer->createOne("_new_$maxNewId");
                $paramName = $genderContainer2->getComponent('name');
                $paramName->setDefaultValue("ženy");

                $ageValueContainer = $ageContainer['values'];

                $maxNewId = $this->generateNewId($ageValueContainer);
                $ageContainer2 = $ageValueContainer->createOne("_new_$maxNewId");
                $paramName = $ageContainer2->getComponent('name');
                $paramName->setDefaultValue("0-75 let");


            } else {
                /** @var \Kdyby\Replicator\Container $targetParams */
                $targetParams = $this['targetParams'];

                foreach ($entity->getTargetParams() as $paramEntity) {
                    $paramsContainer = $targetParams->createOne($paramEntity->getId());

                    /** @var TextInput $paramName */
                    $paramName = $paramsContainer->getComponent('name');
                    $paramName->setDefaultValue($paramEntity->getName());

                    if ($paramEntity->getValues()->count() > 0) {

                        /** @var \Kdyby\Replicator\Container $targetGroupValues */
                        $targetGroupValues = $paramsContainer['values'];

                        foreach ($paramEntity->getValues() as $valueEntity) {

                            $valueContainer = $targetGroupValues->createOne($valueEntity->getId());

                            /** @var TextInput $valueName */
                            $valueName = $valueContainer->getComponent('name');
                            $valueName->setDefaultValue($valueEntity->getName());
                        }
                    }
                }
            }
        }
    }


    public function addTargetGroupParam(SubmitButton $button)
    {
        /** @var \Kdyby\Replicator\Container $parent */
        $parent = $button->getParent();

        if ($parent->isAllFilled()) {
            $maxNewId = $this->generateNewId($parent);
            $button->getParent()->createOne("_new_$maxNewId");

            $message = "Parametr přidán";

            /** @var BasePresenter $presenter */
            $presenter = $this->getPresenter();
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa parametrů cílových skupin', FlashMessageControl::TOAST_DEVICE_EDIT_SUCCESS);
        }
    }


    public function removeTargetGroupParam(SubmitButton $button)
    {
        // first parent is container
        // second parent is it's replicator
        $param = $button->parent->parent;
        $param->remove($button->parent, TRUE);

        /*
         * if name is number > 0 then can remove element
         */
        if (($id = intval($button->parent->getName())) >0 ) {

            if ($element = $this->getEntityMapper()->getEntityManager()->getRepository(TargetGroupParamEntity::class)->find($id)) {
                $this->getEntityMapper()->getEntityManager()->remove($element)->flush();

                $message = "Parametr odebrán";

                /** @var BasePresenter $presenter */
                $presenter = $this->getPresenter();
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa parametrů cílových skupin', FlashMessageControl::TOAST_DEVICE_EDIT_SUCCESS);
            }
        }
    }


    public function addTargetGroupValue(SubmitButton $button)
    {
        /** @var \Kdyby\Replicator\Container $parent */
        $parent = $button->getParent();

        if ($parent->isAllFilled()) {
            $maxNewId = $this->generateNewId($parent);
            $button->getParent()->createOne("_new_$maxNewId");

            $message = "Hodnota přidána";

            /** @var BasePresenter $presenter */
            $presenter = $this->getPresenter();
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa parametrů cílových skupin', FlashMessageControl::TOAST_DEVICE_EDIT_SUCCESS);
        }
    }


    public function removeTargetGroupValue(SubmitButton $button)
    {
        // first parent is container
        // second parent is it's replicator
        $users = $button->parent->parent;
        $users->remove($button->parent, TRUE);

        /*
         * if name is number > 0 then can remove element
         */
        if (($id = intval($button->parent->getName())) > 0) {

            if ($element = $this->getEntityMapper()->getEntityManager()->getRepository(TargetGroupParamValueEntity::class)->find($id)) {
                $this->getEntityMapper()->getEntityManager()->remove($element)->flush();

                $message = "Hodnota odebrána";

                /** @var BasePresenter $presenter */
                $presenter = $this->getPresenter();
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa parametrů cílových skupin', FlashMessageControl::TOAST_DEVICE_EDIT_SUCCESS);
            }
        }
    }



    public function formSuccess(SubmitButton $button)
    {
        /** @var TargetGroupParamEntity $entity */
        $entity = $this->getEntity();

        $values = $button->getForm()->getValues();

        $em = $this->getEntityMapper()->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }


}