<?php

namespace MinimalOriginal\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Filesystem\Filesystem;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

use MinimalOriginal\CoreBundle\Routing\Annotation\Route;
use MinimalOriginal\CoreBundle\Repository\QueryFilter;
use MinimalOriginal\CoreBundle\Repository\AbstractRepository;

use MinimalOriginal\CoreBundle\Entity\App;
use MinimalOriginal\CoreBundle\Entity\Routing;

/**
 * @Route("/manager")
 */
class DefaultController extends Controller
{
  /**
   * @Route("/", name="minimal_manager_home", title="Manager")
   */
    public function indexAction()
    {
        return $this->render('MinimalManagerBundle::index.html.twig');
    }

  /**
   * @Route("/{module}", name="minimal_manager_list")
   *
   * @param QueryFilter $queryFilter
   *
   */
    public function listAction(QueryFilter $queryFilter, $module)
    {
      $module_list = $this->container->get('minimal_manager.module_list');
      $module = $module_list->getModule($module);

      // Order type by default
      if( null === $queryFilter->getOrderType() ){
        $queryFilter->setOrderType('updated');
      }

      // Get exposed fields
      $exposure_manager = $this->container->get("minimal_exposure_annotation_manager");
      $exposedFields = $exposure_manager->getExposedFields($module->getEntityClass(), 'manager');

      // Get repository
      $repository = $this->getDoctrine()
        ->getRepository($module->getEntityClass())
        ;
      if( $repository instanceof AbstractRepository ){
        $repository->setQueryFilter($queryFilter);
      }

      if( $repository instanceof NestedTreeRepository ){
        $data = $repository->getRootNodes();
      }elseif( $repository instanceof AbstractRepository ){
        $data = $repository->findList();
      }else{
        $data = $repository->findAll();
      }

      return $this->render('MinimalManagerBundle:List:table.html.twig',array(
        'data' => $data,
        'module' => $module,
        'exposedFields' => $exposedFields,
      ));
    }

    /**
     * @Route("/{module}/edit/{id}", name="minimal_manager_edit", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param string  $module
     * @param int  $id
     *
     */
    public function editAction(Request $request, $module, $id)
    {
        $module_list = $this->container->get('minimal_manager.module_list');
        $module = $module_list->getModule($module);

        $repository = $this->getDoctrine()->getRepository($module->getEntityClass());
        $data = $repository->findOneBy(array('id'=>$id));
        if( null === $data ){
          throw new NotFoundHttpException("Vous essayez d'éditer quelque chose qui n'existe pas.");
        }

        $form = $this->createForm($module->getFormTypeClass(), $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

          $module->updateModel($data);

          if( $data instanceof App || $data instanceof Routing ){
            $router = $this->container->get('router');
            if( null !== $router->getOption('cache_dir') && null !== $router->getOption('matcher_cache_class') ){
              $fs = new Filesystem();
              $fs->remove($router->getOption('cache_dir') . '/' . $router->getOption('matcher_cache_class') . '.php');
            }
          }

          return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getInformations()->get('name')));

        }

        return $this->render('MinimalManagerBundle:Form:form.html.twig',array(
          'data' => $data,
          'module' => $module,
          'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{module}/move-up/{id}", name="minimal_manager_move_up", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param string  $module
     * @param int  $id
     *
     */
    public function moveUpAction(Request $request, $module, $id)
    {
        $module_list = $this->container->get('minimal_manager.module_list');
        $module = $module_list->getModule($module);

        $repository = $this->getDoctrine()->getRepository($module->getEntityClass());
        $data = $repository->findOneBy(array('id'=>$id));
        if( null === $data ){
          throw new NotFoundHttpException("Vous essayez changer la position de quelque chose qui n'existe pas.");
        }

        if( $repository instanceof NestedTreeRepository ){
          $repository->moveUp($data, 1);
        }

        return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getInformations()->get('name')));

    }

    /**
     * @Route("/{module}/move-down/{id}", name="minimal_manager_move_down", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param string  $module
     * @param int  $id
     *
     */
    public function moveDownAction(Request $request, $module, $id)
    {
        $module_list = $this->container->get('minimal_manager.module_list');
        $module = $module_list->getModule($module);

        $repository = $this->getDoctrine()->getRepository($module->getEntityClass());
        $data = $repository->findOneBy(array('id'=>$id));
        if( null === $data ){
          throw new NotFoundHttpException("Vous essayez changer la position de quelque chose qui n'existe pas.");
        }

        if( $repository instanceof NestedTreeRepository ){
          $repository->moveDown($data, 1);
        }

        return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getInformations()->get('name')));

    }

    /**
     * @Route("/{module}/delete/{id}", name="minimal_manager_delete", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param string  $module
     * @param int  $id
     *
     */
    public function deleteAction(Request $request, $module, $id)
    {
        $module_list = $this->container->get('minimal_manager.module_list');
        $module = $module_list->getModule($module);

        $repository = $this->getDoctrine()->getRepository($module->getEntityClass());
        $data = $repository->findOneBy(array('id'=>$id));
        if( null === $data ){
          throw new NotFoundHttpException("Vous essayez de supprimer quelque chose qui n'existe pas.");
        }

        $module->removeModel($data);

        $session = $this->container->get('session');
        $session->getFlashBag()->add(
          'success',
          "L'élément a bien été supprimé."
      );

        return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getInformations()->get('name')));

    }

    /**
     * @Route("/{module}/create", name="minimal_manager_create")
     *
     * @param Request $request
     * @param string  $module
     */
    public function createAction(Request $request, $module)
    {
        $module_list = $this->container->get('minimal_manager.module_list');
        $module = $module_list->getModule($module);

        $data = $module->createModel();
        $form = $this->createForm($module->getFormTypeClass(), $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

          $module->updateModel($data);

          return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getInformations()->get('name')));

        }

        return $this->render('MinimalManagerBundle:Form:form.html.twig',array(
          'data' => $data,
          'module' => $module,
          'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{module}/batch", name="minimal_manager_batch")
     *
     * @param Request $request
     * @param string  $module
     */
    public function batchAction(Request $request, $module)
    {
      $module_list = $this->container->get('minimal_manager.module_list');
      $module = $module_list->getModule($module);

      $action = $request->request->get('action');
      if( true === is_array($request->request->get('item')) && count($request->request->get('item')) > 0 ){
        $repository = $this->getDoctrine()->getRepository($module->getEntityClass());
        switch( $action ){
          case 'delete':
            foreach($request->request->get('item') as $item){
              $data = $repository->findOneBy(array('id'=>$item));
              if( null !== $data ){
                $module->removeModel($data,false);
              }
            }
            $module->flush();
          break;
        }
      }
      return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getInformations()->get('name')));

    }


}
