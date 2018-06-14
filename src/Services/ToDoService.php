<?php
namespace App\Services;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Container\NotFoundExceptionInterface;

class ToDoService
{
    /**
     * @var DateTimeService
     */
    protected $dateTimeService;

    /**
     * @var EntityManager
     */
    protected $em;
    public function __construct(
        DateTimeService $dateTimeService,
        EntityManager $em
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->em = $em;
    }

    public function getTaskList($userId)
    {
        $list = $this->em->getRepository(Task::class)
            ->getTodoList($userId);

        $list = array_map(function (Task $row) {
            $response = [
                'id'         => $row->getId(),
                'content'    => $row->getContent(),
                'completed'  => $row->getCompleted(),
                'created_at' => $row->getCreatedAt(),
            ];
            return $response;
        }, $list);

        return $list;
    }

    public function deleteTask($taskId, $userId)
    {
        $task = $this->em->getRepository(Task::class)
            ->findOneBy([
                'user' => $userId,
                'id' => $taskId
            ]);

        if (!$task) {
            throw new \Exception('Object not found', 404);
        }
        $this->em->remove($task);
        $this->em->flush();
    }

    public function updateTask($taskId, $userId, $content = null, $completed = null): Task
    {
        $task = $this->em->getRepository(Task::class)
            ->findOneBy([
                'user' => $userId,
                'id' => $taskId
            ]);

        if (!$task) {
            throw new \Exception('Object not found', 404);
        }

        $task->setContent($content ?? $task->getContent());
        $task->setCompleted($completed ?? $task->getCompleted());

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    public function createTask($content, User $user): Task
    {
        $task = new Task();
        $task->setContent($content);
        $task->setUser($user);
        $task->setCompleted(false);
        $task->setCreatedAt($this->dateTimeService->getNow());

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }
}