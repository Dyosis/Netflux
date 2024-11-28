<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/movies')]
class MovieController extends AbstractController
{
    #[Route('/', 'app_movie_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $movies = $entityManager->getRepository(Movie::class)->findAll();

        return $this->render('Page/Movie/list.html.twig', [
            'movies' => $movies
        ]);
    }

    #[Route('/{id}', 'app_movie_show', requirements: ['id' => '\d+'])]
    public function show(Movie $movie): Response
    {
        return $this->render('Page/Movie/show.html.twig', [
            'movie' => $movie
        ]);
    }

    #[Route('/create', 'app_movie_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response|RedirectResponse
    {
        if ($request->isMethod(Request::METHOD_POST)) {

            /** @var UploadedFile $file */
            $requestFile = $request->files->get('file');
            $name = uniqid() . '.' . $requestFile->guessExtension();
            $requestFile->move($parameterBag->get('upload_directory'), $name);
            [
                'title' => $title,
                'synopsis' => $synopsis,
                'director' => $director,
                'release_date' => $releaseDate,
            ] = $request->request->all();

            $movie = new Movie();
            $movie
                ->setTitle($title)
                ->setSynopsis($synopsis)
                ->setDirector($director)
                ->setReleaseDate(new \DateTime($releaseDate));

            $file = new File();
            $file
                ->setName($requestFile->getClientOriginalName())
                ->setPath($name);
            $movie->setFile($file);

            $entityManager->persist($movie);
            $entityManager->persist($file);
            $entityManager->flush();

            return $this->redirectToRoute('app_movie_list');
        }

        return $this->render('Page/Movie/create.html.twig');
    }

    #[Route('/{id}/update', 'app_movie_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, EntityManagerInterface $entityManager, Movie $movie, ParameterBagInterface $parameterBag): Response|RedirectResponse
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            [
                'title' => $title,
                'synopsis' => $synopsis,
                'director' => $director,
                'release_date' => $releaseDate,
            ] = $request->request->all();

            if ($request->files->has('file')) {
                $file = $request->files->get('file');
                $file->move($parameterBag->get('upload_directory'), $file->getClientOriginalName());
            }
            $movie
                ->setTitle($title)
                ->setSynopsis($synopsis)
                ->setDirector($director)
                ->setReleaseDate(new \DateTime($releaseDate));

            $entityManager->flush();

            return $this->redirectToRoute('app_movie_list');
        }

        return $this->render('Page/Movie/update.html.twig', [
            'movie' => $movie
        ]);
    }

    #[Route('/{id}/delete', 'app_movie_delete', requirements: ['id' => '\d+'])]
    public function delete(Movie $movie, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($movie);
        $entityManager->flush();

        return $this->redirectToRoute('app_movie_list');
    }

    #[Route('/{id}/download', 'app_movie_download', requirements: ['id' => '\d+'])]
    public function downloadFile(Movie $movie, ParameterBagInterface $parameterBag): Response
    {
        $file = $movie->getFile();
        $path = $parameterBag->get('upload_directory') . '/' . $file->getPath();

        return $this->file($path, $file->getName());
    }
}