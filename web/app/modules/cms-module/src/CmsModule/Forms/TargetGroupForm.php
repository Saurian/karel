<?php


namespace CmsModule\Forms;

use CmsModule\Entities\TargetGroupEntity;
use CmsModule\Entities\TargetGroupParamEntity;
use CmsModule\Entities\TargetGroupParamValueEntity;
use CmsModule\Facades\ReachFacade;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Strings;
use Tracy\Debugger;

interface ITargetGroupFormFactory
{
    /** @return TargetGroupForm */
    function create();
}

/**
 * Class TargetGroupForm
 * @package CmsModule\Forms
 * @method addDynamic($name, $factory, $createDefault = 0, $forceDefault = FALSE): \Kdyby\Replicator\Container
 * @method onSave(TargetGroupEntity $targetGroupEntity, TargetGroupForm $form)
 */
class TargetGroupForm extends BaseForm
{

    protected $autoButtonClass = false;

    /** @var ReachFacade @inject */
    public $reachFacade;

    /** @var array callback */
    public $onSave = [];


    /** @return TargetGroupForm */
    public function create(Container $container = null)
    {
        $form = $container ? $container : $this;

        $form->addText('name', 'Název cílové skupiny')
            ->setAttribute('placeholder', "placeholder.name")
            ->addRule(Form::FILLED, 'ruleUsername')
            ->addRule(Form::MIN_LENGTH, 'ruleMinLength', 4)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 32);

        /** @var TargetGroupParamValueEntity[] $paramResults */
        $paramResults = $this->reachFacade->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->addSelect('param')
            ->from(TargetGroupParamValueEntity::class, 'e')
            ->join('e.param', 'param')
            ->getQuery()
            ->getResult();

        $params = [];
        foreach ($paramResults as $paramResult) {
            $params[$paramResult->getParam()->getName()][$paramResult->getId()] = $paramResult->getName();
        }

        $form->addMultiSelect('values', 'Parametry', $params, 20)
//            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $form->addSubmit('send', 'Odeslat')->setAttribute('class', 'btn btn-md btn-success')
            ->onClick[] = [$this, 'formSuccess'];

//        $this->onSuccess[] = array($this, 'success');

        $this->addFormClass(['ajax']);
        return $this;
    }



    public function addTargetGroupValue(SubmitButton $button)
    {
        /** @var \Kdyby\Replicator\Container $parent */
        $parent = $button->getParent();
        $maxNewId = BaseForm::generateNewId($parent);

        $button->getParent()->createOne("_new_$maxNewId");
    }


    public function removeTargetGroupValue(SubmitButton $button)
    {
//        Debugger::barDump($button);

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
                $this->getPresenter()->flashMessage('smazáno');
            }

        }
    }

    /**
     * @param SubmitButton $button
     * @throws \Exception
     */
    public function formSuccess(SubmitButton $button)
    {
        /** @var TargetGroupEntity $entity */
        $entity = $this->getEntity();
        $values = $button->getForm()->getValues();

        $this->save($entity, $values);
    }


    /**
     * @param TargetGroupEntity $entity
     * @param $values
     * @throws \Exception
     */
    public function save(TargetGroupEntity $entity, $values)
    {
        $em = $this->getEntityMapper()->getEntityManager();

        /*
         * manual mapping
         * toReplicator not complete
         */
        $existIds = [];
        foreach ($entity->getValues() as $value) {
            $existIds[] = $value->getId();
        }

        $toRemove = array_diff($existIds, (array) $values->values);
        foreach ($toRemove as $item) {
            foreach ($entity->getValues() as $value) {
                if ($value->getId() == $item) {
                    $entity->removeValueById($item);
                }
            }
        }

        $em->persist($entity)->flush();
        $this->onSave($entity, $this);
    }




}