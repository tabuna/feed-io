<?php
namespace FeedIo;

use FeedIo\Rule\DateTimeBuilder;
use FeedIo\Standard\Atom;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-02-23 at 20:31:12.
 */
use \PHPUnit\Framework\TestCase;

class FeedIoTest extends TestCase
{
    /**
     * @var FeedIo
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $client = $this->getMockForAbstractClass('\FeedIo\Adapter\ClientInterface');
        $response = $this->createMock('FeedIo\Adapter\ResponseInterface');
        $response->expects($this->any())->method('isModified')->will($this->returnValue(true));
        $response->expects($this->any())->method('getBody')->will($this->returnValue(
            file_get_contents(dirname(__FILE__)."/../samples/expected-atom.xml")
        ));
        $response->expects($this->any())->method('getLastModified')->will($this->returnValue(new \DateTime()));
        $client->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $this->object = new FeedIo($client, new \Psr\Log\NullLogger());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers FeedIo\FeedIo::__construct
     * @covers FeedIo\FeedIo::loadCommonStandards
     */
    public function testConstruct()
    {
        $client = $this->getMockForAbstractClass('\FeedIo\Adapter\ClientInterface');
        $feedIo = new FeedIo($client, new \Psr\Log\NullLogger());
        $this->assertInstanceOf('\FeedIo\Reader', $feedIo->getReader());
    }

    /**
     * @covers FeedIo\FeedIo::getCommonStandards
     */
    public function testGetCommonStandards()
    {
        $standards = $this->object->getCommonStandards();
        $this->assertInternalType('array', $standards);
        foreach ($standards as $standard) {
            $this->assertInstanceOf('\FeedIo\StandardAbstract', $standard);
        }
    }

    public function testAddDateFormats()
    {
        $this->object->addDateFormats(['YYYY/M/Y']);

        $this->assertAttributeContains('YYYY/M/Y', 'dateFormats', $this->object->getDateTimeBuilder());
    }

    public function testFixerSet()
    {
        $this->assertInstanceOf('\FeedIo\Reader\FixerSet', $this->object->getFixerSet());
    }

    public function testGetBaseFixers()
    {
        $fixers = $this->object->getBaseFixers();

        foreach ($fixers as $fixer) {
            $this->assertInstanceOf('\FeedIo\Reader\FixerAbstract', $fixer);
        }
    }

    public function testAddFilter()
    {
        $filter = $this->getMockForAbstractClass('FeedIo\FilterInterface');

        $this->object->addFilter($filter);
        $this->assertInstanceOf('\FeedIo\FeedIo', $this->object);
    }

    public function testResetFilters()
    {
        $this->object->resetFilters();
        $this->assertInstanceOf('\FeedIo\FeedIo', $this->object);
    }

    public function testWithModifiedSince()
    {
        $result = $this->object->read('http://localhost', new Feed(), new \DateTime());

        $this->assertInstanceOf('\FeedIo\Reader\Result', $result);
    }

    /**
     * @covers FeedIo\FeedIo::addStandard
     */
    public function testAddStandard()
    {
        $this->object->addStandard('atom2', new Atom(new DateTimeBuilder()));
        $this->assertInstanceOf('\FeedIo\Standard\Atom', $this->object->getStandard('atom2'));
    }

    /**
     * @covers FeedIo\FeedIo::getDateTimeBuilder
     */
    public function testGetDateTimeBuilder()
    {
        $this->assertInstanceOf('\FeedIo\Rule\DateTimeBuilder', $this->object->getDateTimeBuilder());
    }

    /**
     * @covers FeedIo\FeedIo::getReader
     */
    public function testGetReader()
    {
        $this->assertInstanceOf('\FeedIo\Reader', $this->object->getReader());
    }

    /**
     * @covers FeedIo\FeedIo::setReader
     */
    public function testSetReader()
    {
        $logger = new \Psr\Log\NullLogger();
        $reader = new Reader(
            new Adapter\Guzzle\Client(
                new \GuzzleHttp\Client()
            ),
            $logger
        );

        $this->object->setReader($reader);
        $this->assertEquals($reader, $this->object->getReader());
    }

    /**
     * @covers FeedIo\FeedIo::read
     */
    public function testRead()
    {
        $result = $this->object->read('http://whatever.com');
        $this->assertInstanceOf('\FeedIo\Reader\Result', $result);
        $this->assertEquals('sample title', $result->getFeed()->getTitle());
    }

    /**
     * @covers FeedIo\FeedIo::readSince
     */
    public function testReadSince()
    {
        $result = $this->object->readSince('http://whatever.com', new \DateTime());
        $this->assertInstanceOf('\FeedIo\Reader\Result', $result);
        $this->assertEquals('sample title', $result->getFeed()->getTitle());
    }

    /**
     * @covers FeedIo\FeedIo::format
     * @covers FeedIo\FeedIo::logAction
     */
    public function testFormat()
    {
        $feed = new Feed();
        $feed->setLastModified(new \DateTime());
        $document = $this->object->format($feed, 'atom');

        $this->assertInternalType('string', $document);
    }

    /**
     * @covers FeedIo\FeedIo::toRss
     */
    public function testToRss()
    {
        $feed = new Feed();
        $feed->setLastModified(new \DateTime());
        $document = $this->object->toRss($feed);
        $this->assertInternalType('string', $document);
    }

    /**
     * @covers FeedIo\FeedIo::toAtom
     */
    public function testToAtom()
    {
        $feed = new Feed();
        $feed->setLastModified(new \DateTime());
        $document = $this->object->toAtom($feed);
        $this->assertInternalType('string', $document);
    }

    /**
     * @covers FeedIo\FeedIo::toJson
     */
    public function testToJson()
    {
        $feed = new Feed();
        $feed->setLastModified(new \DateTime());
        $document = $this->object->toJson($feed);
        $this->assertInternalType('string', $document);
    }

    public function testPsrResponse()
    {
        $feed = new Feed();
        $feed->setUrl('http://localhost');
        $feed->setLastModified(new \DateTime);
        $feed->setTitle('test feed');

        $item = new Feed\Item();
        $item->setLink('http://localhost/item/1');
        $item->setTitle('an item');
        $item->setLastModified(new \DateTime());
        $item->setDescription('lorem ipsum');

        $feed->add($item);

        $response = $this->object->getPsrResponse($feed, 'atom');
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
    }

    /**
     * @covers FeedIo\FeedIo::getStandard
     */
    public function testGetStandard()
    {
        $this->assertInstanceOf('\FeedIo\Standard\Atom', $this->object->getStandard('atom'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testWrongStandard()
    {
        $this->object->getStandard('fake');
    }
}
