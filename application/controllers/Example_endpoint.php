<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Author: RN
 * Date: 8/14/2017
 * Time: 15:01
 */
class Example_endpoint extends MY_REST_Controller
{
    protected $modelToResponseFields = [
        'N_ID' => 'id',
        'V_NAME' => 'name',
        'V_DESC' => 'desc',
        'N_AGE' => 'age'
    ];

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
        $filterMap = [
            'name' => 'V_NAME',
            'desc' => 'V_DESC'
        ];
        $modelData = $this->getModelData($this->post(), $filterMap);

        // insert...
        $data = $modelData;
        $data['N_ID'] = 3;

        $this->respondCreated($data);
    }
}