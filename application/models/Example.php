<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class Example extends APP_Data_Model implements Queriable
{
    protected $fieldMap = [
        'id' => 'V_ID',
        'field1' => 'V_FIELD_1',
        'field2' => 'V_FIELD_2'
    ];
    protected $defaultSorts = ['id'];
    protected $domain = 'Example';
    
    // workaround for `private const` which only available in PHP 7.1
    private static $TABLE = 'TABLE_EXAMPLE';


    public function add (array $data)
    {
        $this->validateData($data);
        $data['id'] = $this->getNextId($this->getAnyDb(), self::$TABLE, 'id', 5);

        return $this->createEntity($this->getAnyDb(), self::$TABLE, $data);
    }

    public function edit ($id, array $data)
    {
        $this->tryValidatePrimaryKey($id);
        $this->validateData($data);

        return $this->updateEntity($this->getAnyDb(), self::$TABLE,
            $data,
            ['id' => $id],
            ['field1', 'field2']
        );
    }

    private function validateData (array $data)
    {
        $this->validation->forArray($data);
        $this->validation->field('field1', 'Required Field')
            ->required()
            ->notEmpty()
            ->onlyString()
            ->lengthMax(100);
        $this->validation->field('field2', 'Optional Field')
            ->notEmpty()
            ->onlyString()
            ->lengthBetween(5, 200);
        $this->validation->validate();
    }

    public function delete ($id)
    {
        $this->tryValidatePrimaryKey($id);

        $this->deleteEntity($this->getAnyDb(), self::$TABLE,
            ['id' => $id]
        );
    }

    public function query (QueryParam $param)
    {
        return $this->getAllEntities($this->getAnyDb(), self::$TABLE, $param);
    }

    public function get ($id)
    {
        $this->tryValidatePrimaryKey($id);

        return $this->getEntity($this->getAnyDb(), self::$TABLE,
            ['id' => $id]
        );
    }

    private function tryValidatePrimaryKey ($id)
    {
        self::validatePrimaryKey(
            $this->validation->forValue($id)
        );
        $this->validation->validateOrNotFound();
    }

    static function validatePrimaryKey (ValueValidator $idValidator)
    {
        $idValidator->notEmpty()
            ->onlyString()
            ->lengthEquals(5);
    }
}