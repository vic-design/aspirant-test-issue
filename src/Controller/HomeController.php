<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reflection;
use ReflectionClass;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

class HomeController
{
    public function __construct(
        private RouteCollectorInterface $routeCollector,
        private Environment $twig,
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws HttpBadRequestException
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->getMovieRepository()->findAll(),
                'datetime' => (new DateTime())->format('Y-m-d H:i'),
                'controller' => (new ReflectionClass($this))->getShortName(),
                'method' => __FUNCTION__
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @return MovieRepository
     */
    protected function getMovieRepository(): MovieRepository
    {
        return $this->em->getRepository(Movie::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws HttpBadRequestException
     */
    public function trailer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $trailer = $this->getMovieRepository()->find($args['id']);
            $data = $this->twig->render('home/trailer.html.twig', [
                'trailer' => $trailer,
                'home' => $this->routeCollector->getNamedRoute('main')->getPattern()
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);
        return $response;
    }
}
