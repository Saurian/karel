<?php // lint >= 5.4


namespace Devrun\Doctrine\DoctrineForms;

use Devrun\Doctrine\NotImplementedException;
use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls\BaseControl;


/**
 *
 * @method ToManyContainer toMany($name, $containerFactory = NULL, $entityFactory = NULL)
 * @method onSubmit(UI\Form $self)
 * @method onError(UI\Form $self)
 */
trait EntityFormTrait
{

    /**
     * @var EntityFormMapper
     */
    private $entityMapper;


    /**
     * @var BuilderFactory
     */
    private $formBuilderFactory;

    /**
     * @var Builder\EntityBuilder
     */
    private $formBuilder;

    /**
     * @var object
     */
    private $entity;

    /**
     * @var array
     */
    private $onPreSave = array();


    /**
     * @param EntityFormMapper $mapper
     *
     * @return EntityFormTrait|UI\Form|
     */
    public function injectEntityMapper(EntityFormMapper $mapper)
    {
        $this->entityMapper = $mapper;
        return $this;
    }


    /**
     * @return EntityFormMapper
     */
    public function getEntityMapper()
    {
        if ($this->entityMapper === NULL) {
            $this->entityMapper = $this->getServiceLocator()->getByType('Devrun\Doctrine\DoctrineForms\EntityFormMapper');
        }

        return $this->entityMapper;
    }


    /**
     * @param BuilderFactory $factory
     *
     * @return EntityFormTrait|UI\Form
     */
    public function _injectBuilderFactory(BuilderFactory $factory)
    {
        throw new NotImplementedException("BuilderFactory not yet");
        $this->formBuilderFactory = $factory;
        return $this;
    }


    /**
     * @return Builder\EntityBuilder
     */
    public function getBuilder()
    {
        throw new NotImplementedException("BuilderFactory not yet");
        if ($this->formBuilder === NULL) {
            if ($this->formBuilderFactory === NULL) {
                $this->formBuilderFactory = $this->getServiceLocator()->getByType('Kdyby\DoctrineForms\BuilderFactory');
            }

            /** @var EntityForm|UI\Form $this */
            $this->formBuilder = $this->formBuilderFactory->create($this);
        }

        return $this->formBuilder;
    }


    /**
     * @param object $entity
     *
     * @return EntityFormTrait|UI\Form
     */
    public function bindEntity($entity)
    {
        $this->entity = $entity;

        /** @var EntityFormTrait|UI\Form $this */
        $this->getEntityMapper()->load($entity, $this);

        return $this;
    }


    /**
     * Always returns the first created field;
     *
     * @param array|string $field
     *
     * @return BaseControl|UI\Form|EntityFormTrait
     */
    public function add($field)
    {
        /** @var EntityFormTrait|UI\Form $this */

        $fields = is_array($field) ? $field : func_get_args();
        $this->getBuilder()->buildFields($fields);

        return $this->getComponent(reset($fields));
    }


    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }



    public function fireEvents()
    {
        /** @var EntityFormTrait|UI\Form $this */

        if (!$submittedBy = $this->isSubmitted()) {
            return;
        }

        $this->validate();

        if ($this->isValid()) {
            if ($this->onPreSave) {
                foreach ($this->onPreSave as $handler) {
                    $params = Nette\Utils\Callback::toReflection($handler)->getParameters();
                    $values = isset($params[1]) ? $this->getValues($params[1]->isArray()) : NULL;
                    Nette\Utils\Callback::invoke($handler, $this, $values);
                }
            }
            if ($this->entity) $this->getEntityMapper()->save($this->entity, $this);

        }

        if ($submittedBy instanceof Nette\Forms\ISubmitterControl) {
            if ($this->isValid()) {
                $submittedBy->onClick($submittedBy);
            } else {
                $submittedBy->onInvalidClick($submittedBy);
            }
        }

        if ($this->onSuccess) {
            foreach ($this->onSuccess as $handler) {
                if (!$this->isValid()) {
                    $this->onError($this);
                    break;
                }
                $params = Nette\Utils\Callback::toReflection($handler)->getParameters();
                $values = isset($params[1]) ? $this->getValues($params[1]->isArray()) : NULL;
                Nette\Utils\Callback::invoke($handler, $this, $values);
            }
        } elseif (!$this->isValid()) {
            $this->onError($this);
        }
        $this->onSubmit($this);
    }



    /**
     * @return Nette\DI\Container|\SystemContainer
     */
    private function getServiceLocator()
    {
        /** @var EntityFormTrait|UI\Form $this */
        /** @var UI\Presenter $presenter */
        $presenter = $this->lookup('Nette\Application\UI\Presenter');

        return $presenter->getContext();
    }

}
