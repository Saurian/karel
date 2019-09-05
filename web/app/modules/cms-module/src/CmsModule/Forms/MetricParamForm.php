<?php


namespace CmsModule\Forms;

use CmsModule\Entities\MetricParamEntity;
use CmsModule\Facades\ReachFacade;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Tracy\Debugger;

interface IMetricParamFormFactory
{
    /** @return MetricParamForm */
    function create();
}

/**
 * Class MetricParamForm
 * @package CmsModule\Forms
 * @method addDynamic($name, $factory, $createDefault = 0, $forceDefault = FALSE): \Kdyby\Replicator\Container
 */
class MetricParamForm extends BaseForm
{

    protected $autoButtonClass = false;


    /** @var ReachFacade @inject */
    public $reachFacade;


    /** @return MetricParamForm */
    public function create()
    {

        $paramsConfig = [
            1 => [
                'addValue' => false,
                'removeParam' => false,
                'disableName' => true,
            ],
        ];

        $paramsConfig = [
            1 => [
                'addValue' => false,
                'removeParam' => false,
                'disableName' => true,
            ],
        ];

        $paramIndex = 0;
        $removeParamEvent = [$this, 'removeParam'];

        $dynamicParams = $this->addDynamic('metricParams', function (Container $container) use (&$paramIndex, &$paramsConfig, $removeParamEvent) {
            $paramIndex++;
            $canRemove = $paramsConfig[$paramIndex]['removeParam'] ?? true;
            $canDisable = $paramsConfig[$paramIndex]['disableName'] ?? false;

            $input = $container->addText('name', 'param')
//                ->setDisabled($canDisable)
                ->setAttribute('placeholder', "Název parametru")
                ->setAttribute('class', 'form-control')
                ->addRule(Form::FILLED);

            if ($canDisable) {
                $input->setAttribute('readonly', 'readonly');
            }

            if ($canRemove) {
                $container->addSubmit('remove', 'Remove param')
                    ->setAttribute('class', 'btn btn-md btn-danger')
                    ->setValidationScope(FALSE) # disables validation
                    ->onClick[] = $removeParamEvent;
            }

        }, 0);

        $dynamicParams->addSubmit('add', 'Add param')
            ->setAttribute('class', 'btn btn-md btn-block btn-primary')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addParam'];


        $this->addSubmit('send', 'Odeslat')->setAttribute('class', 'btn btn-md btn-block btn-success')
            ->setAttribute('data-dismiss', 'modal')
            ->onClick[] = [$this, 'formSuccess'];

        $this->addFormClass(['ajax']);

        return $this;
    }

    protected function attached($presenter)
    {
        parent::attached($presenter);

        if (!$this->isSubmitted()) {

            $entity = $this->getEntity();

            if ($entity->getMetricParams()->count() == 0) {

                /** @var \Kdyby\Replicator\Container $metricParamContainer */
                $metricParamContainer = $this['metricParams'];

                $maxNewId = $this->generateNewId($metricParamContainer);
                $newContainer = $metricParamContainer->createOne("_new_$maxNewId");

                $paramName = $newContainer->getComponent('name');
                $paramName->setDefaultValue("Návštěvnost");

            } else {
                /** @var \Kdyby\Replicator\Container $metricParamContainer */
                $metricParamContainer = $this['metricParams'];

                foreach ($entity->getMetricParams() as $paramEntity) {
                    $paramsContainer = $metricParamContainer->createOne($paramEntity->getId());

                    /** @var TextInput $paramName */
                    $paramName = $paramsContainer->getComponent('name');
                    $paramName->setDefaultValue($paramEntity->getName());
                }
            }
        }
    }




    public function addParam(SubmitButton $button)
    {
        /** @var \Kdyby\Replicator\Container $parent */
        $parent = $button->getParent();

        if ($parent->isAllFilled()) {
            $maxNewId = $this->generateNewId($parent);
            $button->getParent()->createOne("_new_$maxNewId");
        }
    }

    public function removeParam(SubmitButton $button)
    {
        // first parent is container
        // second parent is it's replicator
        $param = $button->getParent()->getParent();
        $param->remove($button->getParent(), TRUE);

        /*
         * if name is number > 0 then can remove element
         */
        if (($id = intval($button->parent->getName())) > 0) {
            if ($element = $this->getEntityMapper()->getEntityManager()->getRepository(MetricParamEntity::class)->find($id)) {
                $this->getEntityMapper()->getEntityManager()->remove($element)->flush();
                $this->getPresenter()->flashMessage('smazáno');
            }
        }
    }



    public function formSuccess(SubmitButton $button)
    {
        /** @var MetricParamEntity $entity */
        $entity = $this->getEntity();

        $values = $button->getForm()->getValues();

        $em = $this->getEntityMapper()->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

}