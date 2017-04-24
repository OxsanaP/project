<?php
namespace AppBundle\Helper\View;

use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\RecursiveCategoryIterator;

class Category extends \Twig_Extension
{
    protected $container, $notify;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function helpMe($form, $name, $skipCur = true)
    {
        $options = array();
        $options['name'] = $form->vars['name'] . "[{$name}]";
        $curCatedory = $form->vars['data'];

        $curValue = $this->_getCurCategoryId($name, $curCatedory);

        $options['id'] = $this->getId();
        $options['class'] = $this->getClass();

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $root_categories = $em->getRepository('AppBundle:ConsumptionCategory')->findBy(array('parent_category' => null));

        $collection = new \Doctrine\Common\Collections\ArrayCollection($root_categories);
        $category_iterator = new RecursiveCategoryIterator($collection);

        $recursiveIterator = new \RecursiveIteratorIterator($category_iterator, \RecursiveIteratorIterator::SELF_FIRST);

        $selected = $curValue === null ? 'selected="selected" ' : '';
        $html = '<option '. $selected . 'value="">---ROOT---</option>';
        foreach ($recursiveIterator as $index => $childCategory) {
            if ($skipCur && $curCatedory->getId() == $childCategory->getId()) {
                continue;
            }
            $selected = $childCategory->getId() == $curValue ? 'selected="selected" ' : '';
            $html .=  '<option '. $selected . 'value="' . $childCategory->getId() . '">' . str_repeat('&nbsp;&nbsp;', $recursiveIterator->getDepth()) . $childCategory->getName() . '</option>';
        }
        return $this->container->get('templating')
            ->render(
                "base/helper/category/select.html.twig",
                array('options' => $html,
                    'param' => $options
                )
            );
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->container->get('doctrine');
    }

    public function getName()
    {
        return 'app_category_extension';
    }

    protected function getId()
    {
        return 'appbundle_consumptioncategory_tree';
    }

    protected function getClass()
    {
        return 'form-control';
    }

    public function getFunctions()
    {
        return array(
            'render_category_select' => new \Twig_Function_Method($this, 'helpMe', array('is_safe' => array('html'))),
        );
    }

    /**
     * @param $name
     * @param $curCatedory
     * @return null
     */
    public function _getCurCategoryId($name, $curCatedory)
    {
        $curValue = null;
        if ($curCatedory instanceof \AppBundle\Entity\ConsumptionCategory) {
            $name = ucwords(str_replace('_', ' ', $name));
            $name = str_replace(' ', '', $name);
            $name = 'get' . $name;
            $curValue = $curCatedory->{$name}();
            $curValue = $curValue ? $curValue->getId() : null;
            return $curValue;
        }
        return $curValue;
    }
}
