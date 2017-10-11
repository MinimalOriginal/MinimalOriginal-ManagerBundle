<?php

namespace MinimalOriginal\ManagerBundle\Twig;

use Symfony\Component\Routing\Router;

use MinimalOriginal\CoreBundle\Modules\ModuleList;

class ManagerExtension extends \Twig_Extension
{

    private $moduleList;
    private $router;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('modulesRoutes', array($this, 'getModulesRoutes'), array('is_safe' => array('html'))),
        );
    }

    /**
    * @param ModuleList $moduleList
    */
    public function setModuleList(ModuleList $moduleList){
      $this->moduleList = $moduleList;
    }

    /**
    * @param Router $router
    */
    public function setRouter(Router $router){
      $this->router = $router;
    }

    /**
     * Returns modules routes
     *
     * @return array
     */
    public function getModulesRoutes()
    {
        $routes = array(
          'list' => array(),
          'selecter' => array(),
        );
        foreach( $this->moduleList->getModules() as $module=>$data){
          $routes['list'][$module] = $this->router->generate('minimal_manager_list',array('module'=>$module));
          $routes['selecter'][$module] = $this->router->generate('minimal_manager_selecter',array('module'=>$module));
        }
        return json_encode($routes);
    }
}
