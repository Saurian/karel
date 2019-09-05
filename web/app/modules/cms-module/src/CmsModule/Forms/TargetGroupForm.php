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
 */
class TargetGroupForm extends BaseForm
{

    protected $autoButtonClass = false;


    /** @var ReachFacade @inject */
    public $reachFacade;


    /** @return TargetGroupForm */
    public function create()
    {
        $this->addText('name', 'Název cílové skupiny')
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

        $this->addMultiSelect('values', 'Parametry', $params)
//            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $this->addSubmit('send', 'Odeslat')->setAttribute('class', 'btn btn-md btn-success')
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


    public function formSuccess(SubmitButton $button)
    {
        /** @var TargetGroupEntity $entity */
        $entity = $this->getEntity();

        $values = $button->getForm()->getValues();


//        dump($entity);
//        dump($values);

//        die("ASd");

        Debugger::$maxDepth = 7;
//        dump($entity->getParams()[1]);

        $em = $this->getEntityMapper()->getEntityManager();

        /*
         * manual mapping
         * toReplicator not complete
         */

        /** @var TargetGroupEntity $entity */
//        $entity = $this->reachFacade->getTargetGroupRepository()->find($values->id);

//        $entity->name = $values->name;

        $existIds = [];

        foreach ($entity->getValues() as $value) {
            $existIds[] = $value->getId();
        }



//        dump($existIds);
//        dump((array) $values->values);

        $toRemove = array_diff($existIds, (array) $values->values);


        foreach ($toRemove as $item) {
            foreach ($entity->getValues() as $value) {
                if ($value->getId() == $item) {
                    $entity->removeValueById($item);
                }
            }
        }


//        dump($toRemove);

//        dump($entity);



        $em->persist($entity);


        $em->flush();

//        die(__METHOD__);

//        Debugger::$maxDepth = 7;
//        Debugger::barDump($entity->getParams()[1]);
//        Debugger::barDump($entity);
//        Debugger::barDump($values);

//        Debugger::$maxDepth = 3;


//        $q = $entity::getReflection()->getProperty('name');

//        Debugger::barDump($q);


//        $param = new TargetGroupParamEntity('ASASA', $entity);

//        $entity->addParam($param);

        $this->getPresenter()->redirect('this');

        $this->getPresenter()->redrawControl();

    }





}