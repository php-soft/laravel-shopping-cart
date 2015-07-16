<?php

class ExceptionHandlerTest extends TestCase
{
    public function testHTTPException404()
    {
        $response = $this->call('GET', '/fake-request');
        $this->assertEquals(404, $response->status());
        $this->assertEquals('{}', $response->getContent());
    }
}
