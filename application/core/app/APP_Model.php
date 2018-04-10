<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
class APP_Model extends MY_Model
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

    protected function getNextId ($table, $field, $padLength = 0, array $filters = null)
    {
        try
        {
            $entity = $this->getFirstEntity($table, $filters, [$field], ['-' . $field]
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
    protected function createEntities ($table, array $dataArr, array $allowedFields = null)
    {
        for ($i = 0; $i < count($dataArr); $i++)
        {
            if (!isset($dataArr[$i]['inupby']))
                $dataArr[$i]['inupby'] = self::$defaultInupby;
        }

        try
        {
            return parent::createEntities($table, $dataArr, $allowedFields);
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
    protected function createEntity ($table, array $data, array $allowedFields = null)
    {
        if (!isset($data['inupby']))
            $data['inupby'] = self::$defaultInupby;

        try
        {
            return parent::createEntity($table, $data, $allowedFields);
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
    protected function updateEntities ($table, array $dataArr, $indexField, array $allowedFields = null)
    {
        for ($i = 0; $i < count($dataArr); $i++)
        {
            if (!isset($dataArr[$i]['inupby']))
                $dataArr[$i]['inupby'] = self::$defaultInupby;
        }

        try
        {
            return parent::updateEntities($table, $dataArr, $indexField, $allowedFields);
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
    protected function updateEntity ($table, array $data, array $filters, array $allowedFields = null)
    {
        if (!isset($data['inupby']))
            $data['inupby'] = self::$defaultInupby;

        try
        {
            return parent::updateEntity($table, $data, $filters, $allowedFields);
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
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntityWithCondition ($table, array $data, QueryCondition $condition, array $allowedFields = null)
    {
        if (!isset($data['inupby']))
            $data['inupby'] = self::$defaultInupby;

        try
        {
            return parent::updateEntityWithCondition($table, $data, $condition, $allowedFields);
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

    protected function updateRow ($table, array $data, array $filters)
    {
        $db = $this->getDb();
        $db->set('T_UPDATE', 'CURRENT_TIMESTAMP', false);
        $success = parent::updateRow($table, $data, $filters);
        $db->reset_query();

        return $success;
    }

    /**
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function deleteEntity ($table, array $filters)
    {
        try
        {
            parent::deleteEntity($table, $filters);
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

    protected function deleteEntityWithCondition ($table, QueryCondition $condition)
    {
        try
        {
            parent::deleteEntityWithCondition($table, $condition);
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
    protected function getEntity ($table, array $filters, array $fields = null)
    {
        $entity = parent::getEntity($table, $filters, $fields);

        if (is_null($entity))
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        else
            return $entity;
    }

    /**
     * @throws ResourceNotFoundException
     */
    protected function getFirstEntity ($table, array $filters = null, array $fields = null, array $sorts = null)
    {
        $entity = parent::getFirstEntity($table, $filters, $fields, $sorts);

        if (is_null($entity))
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        else
            return $entity;
    }
}