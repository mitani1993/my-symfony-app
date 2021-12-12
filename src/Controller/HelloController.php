<?php

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello", name="hello")
     */
    public function index(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Person::class);
        $data = $repository->findAll();

        return $this->render('hello/index.html.twig', [
            'title' => 'Hello',
            'data' => $data,
        ]);
    }

    /**
     * @Route("/find", name="find")
     */
    public function find(Request $request)
    {
        $formObj = new FindForm();
        $form = $this->createFormBuilder($formObj)
            ->add('find', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Click'))
            ->getForm();
        $repository = $this->getDoctrine()
            ->getRepository(Person::class);
        $manager = $this->getDoctrine()->getManager();
        $mapping = new ResultSetMappingBuilder($manager);
        $mapping->addRootEntityFromClassMetadata('App\Entity\Person', 'p');

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $findStr = $form->getData()->getFind();
            $arr = explode(',', $findStr);
            $query = $manager->createNativeQuery(
                'SELECT * FROM person WHERE age BETWEEN ?1 AND ?2',
                $mapping
            )
                ->setParameters(array(1 => $arr[0], 2 => $arr[1]));
            $result = $query->getResult();
        } else {
            $result = $repository->findAllWithSort();
        }

        return $this->render('hello/find.html.twig', [
            'title' => 'Hello',
            'form' => $form->createView(),
            'data' => $result,
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, ValidatorInterface $validator)
    {
        $form = $this->createFormBuilder()
            ->add(
                'name',
                TextType::class,
                array(
                    'required' => true,
                    'constraints' => [
                        new Assert\Length(array(
                            'min' => 3, 'max' => 10,
                            'minMessage' => '3文字以上必要です。',
                            'maxMessage' => '10文字以内にしてください。'
                        ))
                    ]
                )
            )
            ->add('save', SubmitType::class, array('label' => 'Click'))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $msg = 'Hello, ' . $form->get('name')->getData() . '!';
            } else {
                $msg = 'ERROR!';
            }
        } else {
            $msg = 'Send Form';
        }

        return $this->render('hello/create.html.twig', [
            'title' => 'Hello',
            'message' => $msg,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="update")
     */
    public function update(Request $request, Person $person)
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            $person = $form->getData();
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($person);
            $manager->flush();
            return $this->redirect('/hello');
        } else {
            return $this->render('hello/create.html.twig', [
                'title' => 'Hello',
                'message' => 'Update Entity id=' . $person->getId(),
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, Person $person)
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            $person = $form->getData();
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($person);
            $manager->flush();
            return $this->redirect('/hello');
        } else {
            return $this->render('hello/create.html.twig', [
                'title' => 'Hello',
                'message' => 'Delete Entity id=' . $person->getId(),
                'form' => $form->createView(),
            ]);
        }
    }
}

class FindForm
{
    private $find;

    public function getFind()
    {
        return $this->find;
    }

    public function setFind($find)
    {
        return $this->find = $find;
    }
}
