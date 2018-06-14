<?php
namespace App\Tests\Api;

use App\Entity\Task;
use App\Entity\User;
use App\Services\DateTimeService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @todo:: add transactional wrapper for kernel
 * Class ToDoEndpointTest
 * @package App\Tests\Api
 */
class ToDoEndpointTest extends WebTestCase
{
    protected $token;
    protected $user;
    protected $em;
    protected $client;

    public function setUp()
    {
        parent::setUp();
        static::bootKernel();

        $this->user = new User();
        $this->user->setUsername('test');
        $this->user->setPassword('test');

        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->em->beginTransaction();
        $this->em->getConnection()->setAutoCommit(false);

        $this->em->persist($this->user);
        $this->em->flush();

        $jwtManager = $this->client->getContainer()->get('lexik_jwt_authentication.jwt_manager');

        $this->token = $jwtManager->create($this->user);

        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s',$this->token));

    }

    public function testIndex_givenTwoTasksWithDifferentOwner_shouldReturnUser1Task()
    {
        $task = new Task();
        $task->setContent('task 1');
        $task->setUser($this->user);
        $task->setCompleted(false);
        $task->setCreatedAt(new \DateTime('2017-01-01 00:00:00'));

        $this->em->persist($task);

        $user2 = new User();
        $user2->setUsername('test');
        $user2->setPassword('test');
        $this->em->persist($user2);

        $task2 = new Task();
        $task2->setContent('task 2');
        $task2->setUser($user2);
        $task2->setCompleted(false);
        $task2->setCreatedAt(new \DateTime('2017-01-01 00:00:00'));
        $this->em->persist($task2);

        $this->em->flush();

        $this->client->request('GET', '/api/todo');
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $equals = [
            [
                'id'         => $task->getId(),
                'content'    => $task->getContent(),
                'completed'  => $task->getCompleted(),
                'created_at' => '2017-01-01T00:00:00+00:00',

            ],
        ];

        $this->assertEquals($equals, $data);
    }

    public function testCreate_givenTaskData_shouldCreateToDoObject()
    {
        $dateTimeMock = $this->getMockBuilder(DateTimeService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTimeMock->expects($this->any())
            ->method('getNow')
            ->will($this->returnValue(new \DateTime('2017-01-01 00:00:00')));

        $this->client->getContainer()->set(DateTimeService::class, $dateTimeMock);

        $this->client->request('POST', '/api/todo', [
            'content' => 'someTestContent'
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);


        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $task = $this->em->getRepository(Task::class)
            ->findOneBy([
                'id' => $data['id']
            ]);

        $equals = [
            'id'         => $task->getId(),
            'content'    => $task->getContent(),
            'completed'  => $task->getCompleted(),
            'created_at' => '2017-01-01T00:00:00+00:00',
        ];

        $this->assertEquals($equals, $data);
    }

    public function testUpdate_givenTaskData_shouldUpdateToDoObject()
    {
        $task = new Task();
        $task->setContent('task 1');
        $task->setUser($this->user);
        $task->setCompleted(false);
        $task->setCreatedAt(new \DateTime('2017-01-01 00:00:00'));

        $this->em->persist($task);
        $this->em->flush();

        $this->client->request('PUT', '/api/todo/' . $task->getId(), [
            'content' => 'updated task content'
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $task = $this->em->getRepository(Task::class)
            ->findOneBy([
                'id' => $data['id']
            ]);

        $equals = [
            'id'         => $task->getId(),
            'content'    => $task->getContent(),
            'completed'  => $task->getCompleted(),
            'created_at' => '2017-01-01T00:00:00+00:00',
        ];

        $this->assertEquals($equals, $data);
    }

    public function testUpdate_givenUser2Object_shouldNotUpdateAnything()
    {
        $user2 = new User();
        $user2->setUsername('test');
        $user2->setPassword('test');
        $this->em->persist($user2);

        $task = new Task();
        $task->setContent('task 1');
        $task->setUser($user2);
        $task->setCompleted(false);
        $task->setCreatedAt(new \DateTime('2017-01-01 00:00:00'));

        $this->em->persist($task);
        $this->em->flush();

        $this->client->request('PUT', '/api/todo/' . $task->getId(), [
            'content' => 'updated task content'
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertEquals([
            'message' => 'Object not found'
        ], $data);
    }

    public function testDelete_givenExistingObject_shouldBeDeleted()
    {
        $task = new Task();
        $task->setContent('task 1');
        $task->setUser($this->user);
        $task->setCompleted(false);
        $task->setCreatedAt(new \DateTime('2017-01-01 00:00:00'));

        $this->em->persist($task);
        $this->em->flush();

        $this->client->request('DELETE', '/api/todo/' . $task->getId(), [
            'content' => 'updated task content'
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(204, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('', $data);
    }

    public function testDelete_givenExistingUser2Object_shouldNotBeDeleted()
    {
        $user2 = new User();
        $user2->setUsername('test');
        $user2->setPassword('test');
        $this->em->persist($user2);

        $task = new Task();
        $task->setContent('task 1');
        $task->setUser($user2);
        $task->setCompleted(false);
        $task->setCreatedAt(new \DateTime('2017-01-01 00:00:00'));

        $this->em->persist($task);
        $this->em->flush();

        $this->client->request('DELETE', '/api/todo/' . $task->getId(), [
            'content' => 'updated task content'
        ]);
        $response = $this->client->getResponse();

        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertEquals([
            'message' => 'Object not found'
        ], $data);
    }

    public function tearDown()
    {
        parent::tearDown();

        if($this->em->getConnection()->isTransactionActive()) {
            $this->em->rollback();
        }
    }
}