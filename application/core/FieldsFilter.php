<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper class to get filter fields for each level. Supports nested fields or sub-fields from arrays or objects.
 * The format of the fields request parameter value is loosely based on XPath syntax.
 *
 * References: https://developers.google.com/drive/v3/web/performance#partial-response
 *
 * @author Ray Naldo
 */
class FieldsFilter
{
    private $fieldMap;

    
    /**
     * @param string $fieldsParam
     * @return FieldsFilter
     */
    public static function createFromString ($fieldsParam)
    {
        $fields = self::parseFields($fieldsParam);
        return self::create($fields);
    }

    public static function create (array $fields)
    {
        // create FieldsFilter for current level
        $fieldMap = array();
        foreach ($fields as $field)
        {
            if (preg_match('#^(\w+)/([\w(,)/]+)$#', $field, $matches))
            {
                // e.g. a/b and a/x/y will be splitted into [a => [b, x/y]]
                $field = $matches[1];
                if (!isset($fieldMap[ $field ]))
                    $fieldMap[ $field ] = array();

                $fieldMap[ $field ][] = $matches[2];
            }
            else if (preg_match('#^(\w+)\(([\w,()/]+)\)$#', $field, $matches))
            {
                // e.g. abc(x,y,z) will be splitted into [abc => [x,y,z]]
                $field = $matches[1];
                if (!isset($fieldMap[ $field ]))
                    $fieldMap[ $field ] = array();

                $subFields = self::parseFields($matches[2]);
                $fieldMap[ $field ] = array_merge($fieldMap[ $field ], $subFields);
            }
            else
            {
                $fieldMap[ $field ] = array();
            }
        }

        return new FieldsFilter($fieldMap);
    }

    private static function parseFields ($fieldsParam)
    {
        $fields = array();

        $field = '';
        $fieldFound = false;
        $parenthesisCount = 0;

        $length = strlen($fieldsParam);
        for ($i = 0; $i < $length; $i++)
        {
            $char = $fieldsParam[$i];
            switch ($char)
            {
                case ',':
                    // ignore any invalid parenthesis until char is outside the parenthesis
                    if ($parenthesisCount == 0)
                        $fieldFound = true;
                    break;
                case '(':
                    $parenthesisCount++;
                    break;
                case ')':
                    if ($parenthesisCount > 0)
                    {
                        $parenthesisCount--;
                    }
                    else
                    {
                        $field .= substr($fieldsParam, $i);
                        $fields[] = $field;
                        return $fields;
                    }
                    break;
            }

            if ($fieldFound)
            {
                $fields[] = $field;
                $fieldFound = false;
                $field = '';
            }
            else
                $field .= $char;
        }
        if (!empty($field))
            $fields[] = $field;

        return $fields;
    }

    private function __construct (array $fieldMap)
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * Get all fields filtered for current level.
     * @return array
     */
    public function getFields ()
    {
        return array_keys($this->fieldMap);
    }

    public function fieldExists ($field)
    {
        return isset($this->fieldMap[$field]);
    }

    /**
     * Get FieldsFilter for current level field.
     * @param string $field
     * @return FieldsFilter fields filter for subfields/nested fields of the field, or null if not any
     */
    public function getFieldsFilter ($field)
    {
        return self::create($this->fieldMap[$field]);
    }
}