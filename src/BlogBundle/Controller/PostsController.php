<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Posts;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PostsController
 * @package BlogBundle\Controller
 */
class PostsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $postsRep = $em->getRepository('BlogBundle:Posts');
        $posts = $postsRep->findAll();

        return $this->render('BlogBundle:Default:blogIndex.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $postRep = $em->getRepository('BlogBundle:Posts');

        $post = $postRep->find($id);

        return $this->render('BlogBundle:Default:blogShow.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request) {
        $posts = new Posts();
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $posts);

        $formBuilder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('date', DateType::class)
            ->add('submit', SubmitType::class);
        $form = $formBuilder->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($posts);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', 'Annonce créee avec succès !');

                return $this->redirectToRoute('posts_show', [
                    'id' => $posts->getId()
                ]);
            }
        }

        return $this->render('BlogBundle:Default:blogAdd.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $postRep = $em->getRepository('BlogBundle:Posts');

        $post = $postRep->find($id);

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $post);
        $formBuilder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('date', DateType::class)
            ->add('submit', SubmitType::class);
        $form = $formBuilder->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($post);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', 'Annonce editée avec succès !');

                return $this->redirectToRoute('posts_show', [
                    'id' => $id
                ]);
            }
        }

        return $this->render('BlogBundle:Default:blogEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $postRep = $em->getRepository('BlogBundle:Posts');

        $post = $postRep->find($id);
        if (!is_null($post)) {
            $em->remove($post);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Annonce supprimée avec succès !');
        } else {
            $request->getSession()->getFlashBag()->add('success', 'Cet article n\'existe pas');
        }

        return $this->redirectToRoute('posts_index');
    }
}
