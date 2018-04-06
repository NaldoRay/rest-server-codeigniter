<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
abstract class APP_Model extends MY_Model
{
    protected $writeOnlyFieldMap = [
        'inupby' => 'V_INUPBY'
    ];
    protected $booleanPrefixes = ['F_'];
    protected $numberPrefixes = ['N_'];

    private static $defaultInupby = null;


    /**
     * @param string $inupby
     */
    public static function setDefaultInupby ($inupby)
    {
        self::$defaultInupby = $inupby;
    }

    /**
     * @return string|null
     */
    protected static function getDefaultInupby ()
    {
        return self::$defaultInupby;
    }

    protected function getNextId ($db, $table, $field, $padLength = 0, array $filters = null)
    {
        try
        {
            $entity = $this->getFirstEntity($db, $table, $filters,
                [$field], ['-' . $field]
            );
        }
        catch (ResourceNotFoundException $e)
        {
            $entity = null;
        }

        return $this->getNextEntityId($entity, $field, $padLength);
    }

    private function getNextEntityId ($entity, $field, $padLength = 0)
    {
        if (is_null($entity))
            $lastId = 0;
        else
            $lastId = (int) $entity->$field;

        $nextId = $lastId + 1;
        if ($padLength > 0)
            return str_pad($nextId, $padLength, '0', STR_PAD_LEFT);
        else
            return (string) $nextId;
    }

    /**
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntities ($db, $table, array $dataArr, array $allowedFields = null)
    {
        for ($i = 0; $i < count($dataArr); $i++)
        {
            if (!isset($dataArr[$i]['inupby']))
                $dataArr[$i]['inupby'] = self::$defaultInupby;
        }

        try
        {
            return parent::createEntities($db, $table, $dataArr, $allowedFields);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menambah %s, data kosong', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntity ($db, $table, array $data, array $allowedFields = null)
    {
        if (!isset($data['inupby']))
            $data['inupby'] = self::$defaultInupby;

        try
        {
            return parent::createEntity($db, $table, $data, $allowedFields);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menambah %s', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function updateEntities ($db, $table, array $dataArr, $indexField, array $allowedFields = null)
    {
        for ($i = 0; $i < count($dataArr); $i++)
        {
            if (!isset($dataArr[$i]['inupby']))
                $dataArr[$i]['inupby'] = self::$defaultInupby;
        }

        try
        {
            return parent::updateEntities($db, $table, $dataArr, $indexField, $allowedFields);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal mengubah %s, data kosong', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntity ($db, $table, array $data, array $filters, array $allowedFields = null)
    {
        if (!isset($data['inupby']))
            $data['inupby'] = self::$defaultInupby;

        try
        {
            return parent::updateEntity($db, $table, $data, $filters, $allowedFields);
        }
        catch (ResourceNotFoundException $e)
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal mengubah %s', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadFormatException
     * @throws BadValueException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntityWithCondition ($db, $table, array $data, QueryCondition $condition, array $allowedFields = null)
    {
        if (!isset($data['inupby']))
            $data['inupby'] = self::$defaultInupby;

        try
        {
            return parent::updateEntityWithCondition($db, $table, $data, $condition, $allowedFields);
        }
        catch (ResourceNotFoundException $e)
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal mengubah %s', $this->domain), $this->domain);
        }
    }

    protected function updateRow ($db, $table, array $data, array $filters)
    {
        $db->set('T_UPDATE', 'CURRENT_TIMESTAMP', false);
        $success = parent::updateRow($db, $table, $data, $filters);
        $db->reset_query();

        return $success;
    }

    /**
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function deleteEntity ($db, $table, array $filters)
    {
        try
        {
            parent::deleteEntity($db, $table, $filters);
        }
        catch (ResourceNotFoundException $e)
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menghapus %s', $this->domain), $this->domain);
        }
    }

    protected function deleteEntityWithCondition ($db, $table, QueryCondition $condition)
    {
        try
        {
            parent::deleteEntityWithCondition($db, $table, $condition);
        }
        catch (ResourceNotFoundException $e)
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menghapus %s', $this->domain), $this->domain);
        }
    }

    /**
     * @throws ResourceNotFoundException
     */
    protected function getEntity ($db, $table, array $filters, array $fields = null)
    {
        $entity = parent::getEntity($db, $table, $filters, $fields);

        if (is_null($entity))
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        else
            return $entity;
    }

    /**
     * @throws ResourceNotFoundException
     */
    protected function getFirstEntity ($db, $table, array $filters = null, array $fields = null, array $sorts = null)
    {
        $entity = parent::getFirstEntity($db, $table, $filters, $fields, $sorts);

        if (is_null($entity))
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        else
            return $entity;
    }
}