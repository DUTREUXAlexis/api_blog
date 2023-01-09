<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{
    private PostRepository $postRepository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;


    /**
     * @param PostRepository $postRepository
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param array $erreur
     */
    public function __construct(PostRepository $postRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, )
    {
        $this->postRepository = $postRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;

    }

    private function errorFound($message,$status){
            // tablea ude l'eereure
            $erreur = [
                'status' => $status,
                'message' => $message
            ];
            //Transformation du tableau en JSON
            return new Response(json_encode($erreur), $status,
                ["content-type" => "application/json"]);
    }


    #[Route('/api/posts', name: 'api_getPosts',methods: ['GET'])]
    public function getPosts(): Response
    {
        //rechercher les posts dans la base de donnée :
        $posts = $this->postRepository->findAll();
        //transormation de $posts => normalizer
        //$postsArray = $normalizer->normalize($posts);
        //$postsJson = json_encode($postsArray);

        //------------------------
        // On serialise $posts en json
        $postsJson = $this->serializer->serialize($posts, 'json' );
        /*$response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('content-type', 'application/json');
        $response->setContent($postsJson);
        return $response;*/

        return new Response($postsJson,Response::HTTP_OK,
        ["content-type" => "application/json"]);
    }

    #[Route('/api/post/{id}',name: 'api_getPostById',methods: ['GET'])]
    public function getPostById( int $id):Response{
        $post = $this->postRepository->findOneBy(['id' => $id]);
        // erreur si le post n'esxiste pas
        $message = "Ce post est introuvable";
        $status = Response::HTTP_NOT_FOUND;
        if(!$post)
        {
            return $this->errorFound($message,$status);
        }


        $postJson = $this->serializer->serialize($post,'json');
        return new Response($postJson,Response::HTTP_OK,
            ["content-type" => "application/json"]);
    }






    #[Route('/api/posts',name: 'api_createPost',methods: ['POST'])]
    public function createPost(Request $request):Response{
        // Récuperer dans la requète le body contenant le json du nouveau post
        $bodyRequest = $request->getContent();
        // DESERIALISER LE JSON EN OBJET
        $post = $this->serializer->deserialize($bodyRequest,Post::class,'json');
        //Inserer le nouveau post dans la base de données

        $post->setCreatedAt(new \DateTime());
        $this->entityManager->persist($post);
        // Créer le insert
        $this->entityManager->flush();


        //Serialiser $post en json
        $postJson = $this->serializer->serialize($post,'json');
        return new Response($postJson, Response::HTTP_CREATED,
        ["content-type" => "application/json"]);
    }

    #[Route('/api/posts/{id}',name: 'api_deletePost',methods: ['POST'])]
    public function deletePost(int $id):Response{
        $post = $this->postRepository->findOneBy(['id' => $id]);

        $message = "Ce post est introuvable";
        $status = Response::HTTP_NOT_FOUND;
        if(!$post)
        {
            return $this->errorFound($message,$status);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();


        return new Response(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/posts/{id}',name: 'api_updatePost',methods: ['PUT'])]
    public function updatePost(int $id,Request $request):Response{

        // recuperer le body
        $bodyrequest = $request->getContent();

        //recuperer le post que l'on souhaite modifié
        $post = $this->postRepository->findOneBy(['id' => $id]);
        $message = "Ce post est introuvable";
        $status = Response::HTTP_NOT_FOUND;
        if(!$post)
        {
            return $this->errorFound($message,$status);
        }
        //modifier le post avec les données du body
        $this->serializer->deserialize($bodyrequest, Post::class,'json',
        ['object_to_populate' => $post]);
        // Le modifié en bdd
        $this->entityManager->flush();

        return new Response(null,204);
    }


}
