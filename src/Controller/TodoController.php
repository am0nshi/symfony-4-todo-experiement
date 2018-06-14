<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Services\DateTimeService;
use App\Services\ToDoService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations;

//use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class TodoController extends Controller
{
    /**
     * @var DateTimeService
     */
    protected $dateTimeService;
    /**
     * @var ToDoService
     */
    protected $todo;

    function __construct(
        DateTimeService $dateTimeService,
        ToDoService $todo
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->todo = $todo;
    }

    /**
     * @Rest\Get("/api/todo")
     */
    public function index(Security $security)
    {
        $user = $security->getUser();

        $list = $this->todo->getTaskList($user->getId());

        return $this->json($list);
    }

    /**
     * @Rest\Post("/api/todo")
     */
    public function create(Security $security, Request $request)
    {
        $user = $security->getUser();

        $em = $this->getDoctrine()->getManager();
        $user = $em->find(User::class, $user->getId());

        $task = $this->todo->createTask($request->request->get('content'), $user);

        return $this->json([
            'id'         => $task->getId(),
            'content'    => $task->getContent(),
            'completed'  => $task->getCompleted(),
            'created_at' => $task->getCreatedAt(),
        ], 201);
    }

    /**
     * @Rest\Put("/api/todo/{id}")
     */
    public function update($id, Security $security, Request $request)
    {
        $user = $security->getUser();

        try {
            $task = $this->todo->updateTask(
                $id,
                $user->getId(),
                $request->request->get('content', null),
                $request->request->get('completed', null)
            );
        } catch (\Exception $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json([
            'id'         => $task->getId(),
            'content'    => $task->getContent(),
            'completed'  => $task->getCompleted(),
            'created_at' => $task->getCreatedAt(),
        ]);
    }

    /**
     * @Rest\Delete("/api/todo/{id}")
     */
    public function delete($id, Security $security, Request $request)
    {
        $user = $security->getUser();

        try {
            $this->todo->deleteTask($id, $user->getId());
        } catch (\Exception $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json([], 204);
    }
}
