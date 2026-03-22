<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\ApiDefaults;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiBalancerControllerTest extends WebTestCase
{
    private function resetDb(): void
    {
        $conn = static::getContainer()->get('doctrine')->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $conn->executeStatement('TRUNCATE TABLE process');
        $conn->executeStatement('TRUNCATE TABLE machine');
        $conn->executeStatement('TRUNCATE TABLE messenger_messages');
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function requestJson($client, string $method, string $uri, ?array $payload = null): void
    {
        $client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload !== null ? json_encode($payload, JSON_THROW_ON_ERROR) : null
        );
    }

    // система пуста
    public function testStatusEmpty(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $client->request('GET', '/api/status');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), 'Сводка статуса должна отдавать 200');

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data, 'Ответ должен быть JSON-массивом');
        $this->assertArrayHasKey('summary', $data);
        $this->assertSame(0, $data['summary']['total_machines'], 'После сброса БД машин быть не должно');
        $this->assertSame(0, $data['summary']['total_processes']);
        $this->assertSame([], $data['machines']);
        $this->assertSame([], $data['waiting_queue']);
    }

    // добавление машины
    public function testMachinePost(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $payload = [
            'totalMemory' => ApiDefaults::MACHINE_MEMORY,
            'totalCpu' => ApiDefaults::MACHINE_CPU,
        ];
        $this->requestJson($client, 'POST', '/api/machines', $payload);

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), 'Создание машины должно возвращать 201');

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success'] ?? false, 'В ответе success = true');
        $this->assertArrayHasKey('machine', $data, 'В ответе должен быть объект machine');
        $this->assertArrayHasKey('id', $data['machine'], 'У машины должен быть id');
        $this->assertSame(ApiDefaults::MACHINE_MEMORY, $data['machine']['totalMemory']);
        $this->assertSame(ApiDefaults::MACHINE_CPU, $data['machine']['totalCpu']);
    }

    // нераспределенный процесс (некуда добавить, в очередь)
    public function testProcessQueue(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $payload = [
            'requiredMemory' => ApiDefaults::PROCESS_MEMORY,
            'requiredCpu' => ApiDefaults::PROCESS_CPU,
        ];
        $this->requestJson($client, 'POST', '/api/processes', $payload);

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), 'Процесс создаётся даже без машин (очередь)');

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success'] ?? false);
        $this->assertNull($data['process']['machine'] ?? null, 'Без машин процесс не назначен');

        $client->request('GET', '/api/processes/unallocated');
        $un = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1, $un['count'], 'Процесс должен быть в нераспределённых');
    }

    // Процесс садится на машину, если место есть.
    public function testProcessAllocated(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/machines', [
            'totalMemory' => ApiDefaults::MACHINE_MEMORY,
            'totalCpu' => ApiDefaults::MACHINE_CPU,
        ]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', [
            'requiredMemory' => ApiDefaults::PROCESS_MEMORY,
            'requiredCpu' => ApiDefaults::PROCESS_CPU,
        ]);
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), 'Процесс с машиной в наличии — 201');

        $data = json_decode($response->getContent(), true);
        $this->assertNotNull($data['process']['machine'] ?? null, 'Процесс должен быть назначен на машину');
        $this->assertArrayHasKey('id', $data['process']['machine']);
    }

    // Появилась ещё одна машина — очередь разгребается.
    public function testRebalanceSecondMachine(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => 4, 'totalCpu' => 4]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', ['requiredMemory' => 2, 'requiredCpu' => 2]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', ['requiredMemory' => 3, 'requiredCpu' => 3]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/processes/unallocated');
        $un = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1, $un['count'], 'Один процесс не помещается на одну машину 4/4');

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => 4, 'totalCpu' => 4]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/processes/unallocated');
        $un2 = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(0, $un2['count'], 'После второй машины ребаланс должен разместить всех');
    }

    // удаление процесса
    public function testProcessDelete(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/machines', [
            'totalMemory' => ApiDefaults::MACHINE_MEMORY,
            'totalCpu' => ApiDefaults::MACHINE_CPU,
        ]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', [
            'requiredMemory' => ApiDefaults::PROCESS_MEMORY,
            'requiredCpu' => ApiDefaults::PROCESS_CPU,
        ]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $processId = json_decode($client->getResponse()->getContent(), true)['process']['id'];

        $client->request('DELETE', '/api/processes/' . $processId);
        $deleteResponse = $client->getResponse();
        $this->assertEquals(200, $deleteResponse->getStatusCode(), 'Удаление процесса — 200');

        $deleteData = json_decode($deleteResponse->getContent(), true);
        $this->assertTrue($deleteData['success'] ?? false);
    }

    // удаление машины, загруженной процессами 
    public function testMachineDelete(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => 4, 'totalCpu' => 4]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $machineId1 = json_decode($client->getResponse()->getContent(), true)['machine']['id'];

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => 4, 'totalCpu' => 4]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', ['requiredMemory' => 3, 'requiredCpu' => 3]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', ['requiredMemory' => 3, 'requiredCpu' => 3]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/machines/' . $machineId1);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Удаление машины — 200');

        $client->request('GET', '/api/processes/unallocated');
        $un = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1, $un['count'], 'После удаления машины часть процессов в очереди');
    }

    // очереди в статусе и списком совпадают
    public function testStatusQueueMatch(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/processes', ['requiredMemory' => 1, 'requiredCpu' => 1]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/processes/unallocated');
        $u = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/api/status');
        $s = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($u['count'], $s['summary']['unallocated_processes'], 'Счётчик в статусе совпадает с unallocated');
        $this->assertSame($u['count'], count($s['waiting_queue']));
    }

    // тест статуса с добавлением машины 
    public function testStatusMachines(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $client->request('GET', '/api/status/machines');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true));

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => 8, 'totalCpu' => 4]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/status/machines');
        $list = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $list);
        $this->assertSame(8, $list[0]['totalMemory']);
        $this->assertSame(4, $list[0]['totalCpu']);
        $this->assertArrayHasKey('processNum', $list[0]);
        $this->assertSame(0, $list[0]['processNum']);
    }

    // ссишком жирный процесс никуда не влезает
    public function testProcessTooBig(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => 2, 'totalCpu' => 2]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $this->requestJson($client, 'POST', '/api/processes', ['requiredMemory' => 10, 'requiredCpu' => 1]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/processes/unallocated');
        $un = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1, $un['count'], 'Слишком тяжёлый процесс остаётся без машины');
    }

    // невалидные данные при добавлении машины 
    public function testMachineBadJson(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->resetDb();

        $this->requestJson($client, 'POST', '/api/machines', ['totalMemory' => '8', 'totalCpu' => 4]);
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), 'Строка вместо int — 400');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($data['success'] ?? true);
        $this->assertArrayHasKey('error', $data);
    }
}
