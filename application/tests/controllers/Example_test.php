<?php

class Example_test extends TestCase
{
	public function testExample ()
	{
		$output = $this->sendRequest('GET', 'examples');
        $this->assertResponseCode(200);

        $data1 = [
            'id' => 1,
            'name' => 'Example #1',
            'desc' => 'Description #1'
        ];
        $data2 = [
            'id' => 2,
            'name' => 'Example #2',
            'desc' => 'Description #2'
        ];

        $this->assertContains(json_encode($data1), $output);
        $this->assertContains(json_encode($data2), $output);
	}

	public function testExistingExampleShouldNotEmpty ()
	{
        $output = $this->sendRequest('GET', 'examples/2');
        $this->assertResponseCode(200);
		$this->assertContains('"id":2', $output);
	}

	public function testExampleNotFound ()
	{
		$this->sendRequest('GET', 'examples/3');
		$this->assertResponseCode(404);
	}

    public function testPostExampleShouldSuccess ()
    {
        $data = [
            'name' => 'Test Example #3',
            'description' => 'Test Description #3',
            'age' => 123
        ];
        $output = $this->sendRequest('POST', 'examples', $data);

        $this->assertResponseCode(201);
        $this->assertContains('"id":3', $output);
        $this->assertNotContains('age', $output);
    }

	private function sendRequest ($http_method, $argv, $params = [])
    {
        try
        {
            $output = $this->request($http_method, $argv, $params);
        }
        catch (CIPHPUnitTestExitException $ex)
        {
            $output = ob_get_clean();
        }
        ob_end_clean();

        return $output;
    }
}
