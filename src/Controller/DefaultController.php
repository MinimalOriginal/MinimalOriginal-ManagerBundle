<?php

namespace MinimalOriginal\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use MinimalOriginal\CoreBundle\Repository\QueryFilter;
use MinimalOriginal\CoreBundle\Repository\AbstractRepository;

/**
 * @Route("/manager")
 */
class DefaultController extends Controller
{
  /**
   * @Route("/", name="minimal_manager_home")
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

      if( null === $queryFilter->getOrderType() ){
        $queryFilter->setOrderType('updated');
      }
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

          $em = $this->getDoctrine()->getManager();
          $em->persist($data);
          $em->flush();

          return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getName()));

        }

        return $this->render('MinimalManagerBundle:Form:form.html.twig',array(
          'data' => $data,
          'module' => $module,
          'form' => $form->createView(),
        ));
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
        $entity_class = $module->getEntityClass();
        $data = new $entity_class();
        $form = $this->createForm($module->getFormTypeClass(), $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

          $em = $this->getDoctrine()->getManager();
          $em->persist($data);
          $em->flush();

          return $this->redirectToRoute('minimal_manager_list', array('module' => $module->getName()));

        }

        return $this->render('MinimalManagerBundle:Form:form.html.twig',array(
          'data' => $data,
          'module' => $module,
          'form' => $form->createView(),
        ));
    }

    private function getEntityConfig($entity){

      $bundles = $this->container->getParameter('minimal_manager.bundles');
      if( true === array_key_exists($entity, $bundles) ){
        return $bundles[$entity];
      }else{
        throw new NotFoundHttpException("Ce module n'existe pas.");
      }
    }
}
