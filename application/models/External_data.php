<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class External_data extends APP_Data_Model
{
    protected $fieldMap = [
        'idExternal' => 'V_ID',
        'field1' => 'V_FIELD_1',
        'field2' => 'V_FIELD_2'
    ];
    protected $defaultSorts = ['idExternal'];
    protected $domain = 'External Data';
    
    // workaround for `private const` which only available in PHP 7.1
    private static $TABLE = 'TABLE_EXTERNAL';


    public function get ($id)
    {
        $this->tryValidatePrimaryKey($id);

        return $this->getEntity($this->getAnyDb(), self::$TABLE,
            ['idExternal' => $id]
        );
    }

    protected function getJoinEntity ($entity, array $fields = null)
    {
        return $this->getEntity($this->getAnyDb(), self::$TABLE,
            ['idExternal' => $entity->idExternal],
            $fields
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
        $idValidator->required()
            ->onlyString()
            ->lengthEquals(5);
    }
}