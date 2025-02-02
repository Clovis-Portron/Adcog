<?php

namespace Adcog\ValidatorBundle\Controller;

use Adcog\DefaultBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ValidatorCommentController
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 * @Route("/comment")
 */
class ValidatorCommentController extends Controller
{
    /**
     * Index
     *
     * @param Request $request
     *
     * @return array
     * @Route("/{page}", requirements={"page":"\d+"}, defaults={"page":1})
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        // Filter results
        $filter = $this->get('form.factory')->createNamed(null, 'adcog_validator_comment_filter', ['not_validated' => true], ['method' => 'GET', 'csrf_protection' => false])->handleRequest($request);
        $filterData = !$filter->isSubmitted() || $filter->isValid() ? $filter->getData() : [];

        // Find filtered results
        $paginatorHelper = $this->get('eb_paginator_helper');
        $paginator = $this->get('doctrine.orm.default_entity_manager')->getRepository('AdcogDefaultBundle:Comment')->getPaginator($paginatorHelper, $filterData);

        return [
            'paginator' => $paginator,
            'filter' => $filter->createView(),
        ];
    }

    /**
     * Read
     *
     * @param Request $request Request
     * @param Comment $comment Comment
     *
     * @return array
     * @Route("/{comment}/read", requirements={"comment":"\d+"})
     * @ParamConverter("comment", class="AdcogDefaultBundle:Comment")
     * @Method("GET|POST")
     * @Template()
     */
    public function readAction(Request $request, Comment $comment)
    {
        $form = $this->createForm('adcog_validator_comment', $comment);
        if ($form->handleRequest($request)->isValid()) {
            $this->get('doctrine.orm.default_entity_manager')->flush();

            return $this->redirect($this->generateUrl('validator_comment_index'));
        }

        return [
            'comment' => $comment,
            'form' => $form->createView(),
        ];
    }
}
