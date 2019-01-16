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
        $fields = array();
        // convert `[a => [b,x/y], abc => [x,y,z]]` to `[a(b,x/y),abc(x,y,z)]`
        foreach ($this->fieldMap as $field => $subFields)
        {
            if (empty($subFields))
                $fields[] = $field;
            else
                $fields[] = sprintf('%s(%s)', $field, implode(',', $subFields));
        }
        return $fields;
    }

    public function getSubFields ($field)
    {
        if (isset($this->fieldMap[$field]))
            return $this->fieldMap[$field];
        else
            return array();
    }

    public function fieldExists ($field)
    {
        return isset($this->fieldMap[$field]);
    }

    public function isEmpty ()
    {
        return empty($this->fieldMap);
    }

    public function addFromArray (array $fields)
    {
        if (!empty($fields))
        {
            $fieldMap = self::getFieldMap($fields);
            $this->fieldMap = array_merge_recursive($this->fieldMap, $fieldMap);
        }
    }

    /**
     * Get FieldsFilter for current level field.
     * @param string $field
     * @return FieldsFilter|null fields filter for subfields/nested fields of the field if any, or null if not
     */
    public function getFieldsFilter ($field)
    {
        if (isset($this->fieldMap[$field]))
            return self::fromArray($this->fieldMap[$field]);
        else
            return null;
    }

    /**
     * @param string $fieldsParam
     * @return FieldsFilter
     */
    public static function fromString ($fieldsParam)
    {
        $fields = self::parseFields($fieldsParam);
        return self::fromArray($fields);
    }

    public static function fromArray (array $fields)
    {
        $fieldMap = self::getFieldMap($fields);
        return new FieldsFilter($fieldMap);
    }

    private static function getFieldMap (array $fields)
    {
        // create FieldsFilter for current level
        $fieldMap = array();
        foreach ($fields as $field)
        {
            if (preg_match('#^(\w+)/([\w(,)/]+)$#', $field, $matches))
            {
                // e.g. a/b and a/x/y will be splitted into [a => [b,x/y]]
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
        return $fieldMap;
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
                        $fields[] = trim($field);
                        return $fields;
                    }
                    break;
            }

            if ($fieldFound)
            {
                $fields[] = trim($field);
                $fieldFound = false;
                $field = '';
            }
            else
                $field .= $char;
        }
        if (!empty($field))
            $fields[] = trim($field);

        return $fields;
    }
}