<?php

namespace MinimalOriginal\ManagerBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use MinimalOriginal\CoreBundle\Modules\{ModuleList, ManageableModuleInterface, ModuleInterface};

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

    public function createModulesMenu(array $options)
    {
        $menu = $this->factory->createItem('root')->setChildrenAttribute('class', 'menu vertical');

        // Add menu items
        foreach($this->module_list->getModules() as $module){

          if($module instanceof ManageableModuleInterface && null !== $module->getParent()) continue;

          $child = $menu->addChild(
            $module->getInformations()->get('title'),
            array(
              'route' => 'minimal_manager_list',
              'routeParameters' => array(
                'module' => $module->getInformations()->get('name')
              )
            )
          );
          if( null !== $module->getInformations()->get('icon')){
            $child->setExtra('icon', $module->getInformations()->get('icon'));
          }
          $this->addChildren($child, $module);

        }


        return $menu;
    }
    protected function addChildren(ItemInterface $menu, ModuleInterface $module){
        $modules_children = $this->module_list->getModulesChildren();
        if( null !== $modules_children->get($module->getInformations()->get('name'))){

          $children = $modules_children->get($module->getInformations()->get('name'));
          $menu->setChildrenAttribute('class', 'menu nested vertical');
          foreach($children as $module){

            $child = $menu->addChild(
              $module->getInformations()->get('title'),
              array(
                'route' => 'minimal_manager_list',
                'routeParameters' => array(
                  'module' => $module->getInformations()->get('name')
                )
              )
            );
            if( null !== $module->getInformations()->get('icon')){
              $child->setExtra('icon', $module->getInformations()->get('icon'));
            }
          }
        }
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root')->setChildrenAttribute('class', 'menu');
        $menu->addChild('Manager', array('route' => 'minimal_manager_home'));

        return $menu;
    }
}
