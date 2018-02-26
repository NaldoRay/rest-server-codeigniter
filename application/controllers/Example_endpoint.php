<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * @author Ray Naldo
 * @property Example $example
 */
class Example_endpoint extends MY_REST_Controller
{
    public function __construct ()
    {
        parent::__construct();
        $this->load->model(Example::class, 'example');
    }

    public function examples_get ()
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Example #1',
                'desc' => 'Description #1'
            ],
            [
                'id' => 2,
                'name' => 'Example #2',
                'desc' => 'Description #2'
            ]
        ];

        $this->respondSuccess($data);
    }

    public function existingExample_get ($id)
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Example #1',
                'desc' => 'Description #1'
            ],
            [
                'id' => 2,
                'name' => 'Example #2',
                'desc' => 'Description #2'
            ]
        ];

        if ($id >= 1 && $id <= count($data))
            $this->respondSuccess($data[$id-1]);
        else
            $this->respondNotFound('Example not found', 'Example');
    }

    public function examples_post ()
    {
        // insert...
        $this->respondCreated(['id' => 3]);
    }

    public function searchExamples_post ()
    {
        $search = $this->post();

        $examples = $this->search($this->example, $search);
        $this->respondSuccess($examples);
    }
}