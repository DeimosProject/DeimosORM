<?php

namespace Test\Orm;

class BuildTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Deimos\ORM\ORM
     */
    protected $orm;

    /**
     * @var \Deimos\Database\Database
     */
    protected $database;

    public function setUp()
    {
        parent::setUp();

        $builder = new \Deimos\Builder\Builder();

        $configObject = new \Deimos\Config\ConfigObject($builder, [
            'adapter'  => 'sqlite',
            'file' => ':memory:',
        ]);

        $this->database = new \Deimos\Database\Database($configObject);

        $this->database->exec('CREATE TABLE IF NOT EXISTS events (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,num INTEGER NOT NULL,event TEXT NOT NULL)');
        $this->database->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL)');
        $this->database->exec('CREATE TABLE IF NOT EXISTS events2users (event_id INTEGER NOT NULL,user_id INTEGER NOT NULL)');

        $this->orm = new \Deimos\ORM\ORM($builder, $this->database);

        $this->orm->register('event', Event::class);

        $faker = \Faker\Factory::create();

        $i = 30;
        do {
            $this->orm->create('event', [
                'num' => random_int(-10000, 10000),
                'event' => $faker->name
            ]);
        } while($i--);
    }

    public function testOrm()
    {
        $entity = $this->orm->repository(['t1' => 'event'])
            ->select(['event' => 't1.event'])
            ->where('id', '>', 3)
            ->offset(2)
            ->limit(2);

        $this->assertEquals(
            count($entity->find()),
            2
        );
//        $this->assertEquals(
//            $entity->count(),
//            2
//        );
    }

}

class Event extends \Deimos\ORM\Entity
{
    protected $table = 'events'; // optional
}
