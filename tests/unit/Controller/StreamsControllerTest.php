<?php
namespace Ibanawx\Bundle\Prooph\EventStore\RestApiBundle\Tests\Unit\Controller;

use Ibanawx\Bundle\Prooph\EventStore\RestApiBundle\Controller\StreamsController;
use Ibanawx\Bundle\Prooph\EventStore\RestApiBundle\Formatter\StreamFormatter;
use Ibanawx\Bundle\Prooph\EventStore\RestApiBundle\Tests\Unit\UnitTest;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\StreamNotFoundException;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StreamsControllerTest extends UnitTest
{

    /**
     * @var StreamFormatter | \PHPUnit_Framework_MockObject_MockObject
     */
    private $formatter;

    /**
     * @var EventStore | \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventStore;

    /**
     * @var StreamsController
     */
    private $SUT;

    /**
     * @var Request
     */
    private $request;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->formatter = $this->mock(StreamFormatter::class);
        $this->eventStore = $this->mock(EventStore::class);
        $this->SUT = new StreamsController($this->formatter, $this->eventStore);
    }

    /**
     * @test
     */
    public function it_should_return_an_empty_415_unsupported_media_type_response_when_the_accept_type_is_not_outputted_by_the_formatter()
    {
        $this->formatter
            ->method('getOutputContentType')
            ->willReturn($this->faker->mimeType);
        $request = new Request();
        $request->headers->set('Accept', 'unsupported/type');
        $expectedHeaders = ['Content-Type' => $this->formatter->getOutputContentType()];
        $expectedResponse = new Response('', Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $expectedHeaders);

        $this->assertEquals($expectedResponse, $this->SUT->getAction($request, $this->faker->word, $this->faker->numberBetween()));
    }

    /**
     * @test
     */
    public function it_should_return_an_empty_404_not_found_response_when_the_stream_was_not_found()
    {
        $this->given_there_is_a_supported_accept_content_type();

        $streamName = $this->faker->word;
        $minVersion = $this->faker->numberBetween();
        $this->eventStore
            ->method('load')
            ->with(new StreamName($streamName), $minVersion)
            ->willThrowException(new StreamNotFoundException());
        $expectedHeaders = ['Content-Type' => $this->formatter->getOutputContentType()];
        $expectedResponse = new Response('', Response::HTTP_NOT_FOUND, $expectedHeaders);

        $this->assertEquals($expectedResponse, $this->SUT->getAction($this->request, $streamName, $minVersion));
    }

    /**
     * @test
     */
    public function it_should_return_the_formatted_stream_with_a_200_ok_response()
    {
        $this->given_there_is_a_supported_accept_content_type();

        $streamName = $this->faker->word;
        $minVersion = $this->faker->numberBetween();
        $stream = $this->mock(Stream::class);
        $this->eventStore
            ->method('load')
            ->with(new StreamName($streamName), $minVersion)
            ->willReturn($stream);
        $expectedHeaders = ['Content-Type' => $this->formatter->getOutputContentType()];
        $expectedResponseBody = '{}';
        $this->formatter
            ->method('format')
            ->with($stream)
            ->willReturn($expectedResponseBody);
        $expectedResponse = new Response($expectedResponseBody, Response::HTTP_OK, $expectedHeaders);

        $this->assertEquals($expectedResponse, $this->SUT->getAction($this->request, $streamName, $minVersion));
    }

    private function given_there_is_a_supported_accept_content_type()
    {
        $formatterOutputContentType = $this->faker->mimeType;
        $this->formatter
            ->method('getOutputContentType')
            ->willReturn($formatterOutputContentType);

        $this->request = new Request();
        $this->request->headers->set('Accept', $formatterOutputContentType);
    }

}