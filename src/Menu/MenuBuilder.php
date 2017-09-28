<?php

namespace MinimalOriginal\ManagerBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MinimalOriginal\CoreBundle\Modules\ModuleList;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $factory;
    private $module_list;

    /**
      * @param FactoryInterface $factory
      * @param ModuleList       $module_list
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory, ModuleList $module_list)
    {
      $this->factory = $factory;
      $this->module_list = $module_list;
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root')->setChildrenAttribute('class', 'menu vertical');
        $menu->addChild('Mon site', array('route' => 'minimal_front_home'));
        $menu->addChild('Résumé', array('route' => 'minimal_manager_home'));
        //$menu->addChild('Paramètres', array('route' => 'minimal_manager_settings'));

        foreach($this->module_list->getModules() as $module){
          $menu->addChild($module->getTitle(), array('route' => 'minimal_manager_list', 'routeParameters' => array('module' => $module->getName())));
        }


        return $menu;
    }
}
