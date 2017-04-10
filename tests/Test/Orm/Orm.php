<?php

namespace Test\Orm;

use Deimos\ORM\Entity;

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
        $helper  = new \Deimos\Helper\Helper($builder);
        $slice   = new \Deimos\Slice\Slice($helper, [
            'adapter' => 'sqlite',
            'file'    => ':memory:',
        ]);

        $this->database = new \Deimos\Database\Database($slice);

        $this->database->exec('CREATE TABLE IF NOT EXISTS events (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,num INTEGER NOT NULL,event TEXT NOT NULL)');
        $this->database->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL)');
        $this->database->exec('CREATE TABLE IF NOT EXISTS events2users (event_id INTEGER NOT NULL,user_id INTEGER NOT NULL)');
        $this->database->exec('CREATE TABLE IF NOT EXISTS events2users2 (myPrimaryKey INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,eventId INTEGER NOT NULL,userId INTEGER NOT NULL)');
        $this->database->exec('CREATE TABLE IF NOT EXISTS people (myPrimaryKey INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,name INTEGER NOT NULL)');

        $this->database->rawQuery('INSERT INTO people (name) VALUES (?)', [__LINE__]);

        $faker = \Faker\Factory::create();

        $i = 60;
        do
        {
            $this->database->rawQuery('INSERT INTO events2users (event_id, user_id) VALUES (?, ?)', [
                ($i % 10) + 1,
                ((int)($i / 3)) + 1
            ]);
            $this->database->rawQuery('INSERT INTO events2users2 (eventId, userId) VALUES (?, ?)', [
                ($i % 10) + 1,
                ((int)($i / 3)) + 1
            ]);
        }
        while ($i--);

        $this->orm = new \Deimos\ORM\ORM($helper, $this->database);

        $this->orm->setConfig([

            'event' => [
                'class' => Event::class
            ],

            'one2many' => [
                'class'     => E2U::class,
                'relations' => [
                    'user' => [
                        'type'   => 'oneToMany',
                        'leftId' => 'user_id',
                    ]
                ]
            ],

            'user' => [
                'class'     => Entity::class,
                'relations' => [
                    'event' => [
                        'table'   => 'events2users',
                        'type'    => 'manyToMany',
                        'leftId'  => 'user_id',
                        'rightId' => 'event_id',
                    ]
                ]
            ],

            'one2many2' => [
                'class'     => E2U2::class,
                'relations' => [
                    'user' => [
                        'type' => 'oneToMany'
                    ]
                ]
            ],

            'person' => [
                'class' => Person::class
            ],

        ]);

        $i = 30;
        do
        {
            $this->orm->create('event', [
                'num'   => random_int(-10000, 10000),
                'event' => $faker->name
            ]);
        }
        while ($i--);

        $i = 30;
        do
        {
            $this->orm->create('user', [
                'name' => $faker->name
            ]);
        }
        while ($i--);
    }

    public function testOne2Many()
    {
        $result = $this->orm->repository('user')
            ->findOne()
            ->one2many()
            ->find(true);

        $this->assertEquals(
            $result[0]->event_id,
            3
        );
        $this->assertEquals(
            $result[2]->event_id,
            1
        );
    }

    public function testOne2Many2()
    {

        $result = $this->orm->repository('user')
            ->findOne()
            ->one2many2()
            ->find(true);

        $this->assertEquals(
            $result[0]->eventId,
            3
        );
        $this->assertEquals(
            $result[2]->eventId,
            1
        );
    }

    public function testOrm()
    {
        $entity = $this->orm->repository(['t1' => 'event'])
            ->select(['event' => 't1.event'], 'id')
            ->where('id', '>', 3)
            ->offset(2)
            ->limit(2);

        $this->assertEquals(
            $this->orm->repository(['t1' => 'event'])
                ->select(['event' => 't1.event'])
                ->where('id', '>', 3)
                ->count(),
            28
        );

        $entityArray = $entity->findOne();
        $this->assertEquals(
            $entityArray->id,
            6
        );

        $entityArray = $entity->findOne(false);
        $this->assertEquals(
            $entityArray['id'],
            6
        );

        $cloneEntity = clone $entity;
        $entityArray = $cloneEntity->where('id', 1)->findOne(true);
        $this->assertNull(
            $entityArray
        );

        $this->assertEquals(
            $cloneEntity->find(false),
            [], '', 0.0, 10, true
        );

        $result = $entity->find();

        $this->assertEquals(
            count($result),
            2
        );

        $this->assertNotNull($this->orm->config('event'));
        $this->assertNull($this->orm->config('event5'));
        $this->assertEquals(
            $this->orm->mapClass('event5'),
            Entity::class
        );
        $this->assertEquals(
            $this->orm->mapPK('person'),
            'myPrimaryKey'
        );

        $this->assertEquals(
            $result[0]->users()->findOne()->id(),
            8
        );

        $person = $this->orm->repository('person')
            ->findOne();

        $testLine = $person->name = __LINE__;
        $this->assertTrue($person->save());

        $this->assertEquals(
            $person->name,
            $testLine
        );

        $this->assertEquals(
            (string)$person,
            json_encode($person)
        );

        $this->assertTrue(isset($person->name));

        $this->assertTrue($person->delete());

        $this->assertNull($person->id());

        $this->assertTrue($person->save());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotConfig()
    {
        $this->orm->repository('person')
            ->findOne()
            ->users();
    }

    /**
     * @expectedException \Deimos\ORM\Exceptions\ModelNotModify
     */
    public function testNotModify()
    {
        $this->orm->repository('person')
            ->findOne()
            ->save();
    }

    /**
     * @expectedException \Deimos\ORM\Exceptions\ModelNotLoad
     */
    public function testDeleteDelete()
    {
        $person = $this->orm->repository('person')->findOne();
        $person->delete();
        $person->delete();
    }

}

class Event extends Entity
{
    protected $table = 'events'; // optional
}

class E2U2 extends Entity
{
    protected $primaryKey = 'myPrimaryKey';
    protected $table      = 'events2users2';
}

class E2U extends Entity
{
    protected $table = 'events2users';
}

class Person extends Entity
{
    protected $primaryKey = 'myPrimaryKey';
}
