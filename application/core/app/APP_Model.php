<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
class APP_Model extends MY_Model
{
    protected $writeOnlyFieldMap = [
        'createdAt' => 'T_INSERT',
        'updatedAt' => 'T_UPDATE',
        'inupby' => 'V_INUPBY'
    ];
    protected $booleanPrefixes = ['F_'];
    protected $numberPrefixes = ['N_'];
    protected $dateTimePrefixes = ['T_'];

    private static $inupby = null;


    /**
     * @param string $inupby
     */
    public static function setInupby ($inupby)
    {
        self::$inupby = $inupby;
    }

    /**
     * @return string|null
     */
    protected static function getInupby ()
    {
        return self::$inupby;
    }


    /**
     * @throws BadFormatException
     * @throws BadValueException
     * @throws TransactionException
     */
    protected function createEntities ($table, array $dataArr, array $allowedFields = null)
    {
        $createdAt = $this->getCurrentDateTime();
        foreach ($dataArr as &$data)
        {
            $data['createdAt'] = $createdAt;
            $data['inupby'] = self::$inupby;
        }
        unset($data);

        try
        {
            return parent::createEntities($table, $dataArr, $allowedFields);
        }
        catch (BadValueException $e)
        {
            throw new BadValueException(sprintf('Gagal menambah daftar %s', $this->domain), $this->domain);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menambah daftar %s', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntity ($table, array $data, array $allowedFields = null)
    {
        $data['createdAt'] = $this->getCurrentDateTime();
        $data['inupby'] = self::$inupby;

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
     * @throws BadValueException
     * @throws TransactionException
     */
    protected function updateEntities ($table, array $dataArr, $indexField, array $filters = null, array $allowedFields = null)
    {
        foreach ($dataArr as &$data)
        {
            $data['inupby'] = self::$inupby;
        }
        unset($data);

        try
        {
            return $this->doTransaction(function () use ($table, $dataArr, $indexField, $filters, $allowedFields)
            {
                $count = parent::updateEntities($table, $dataArr, $indexField, $filters, $allowedFields);

                /*
                 * FIX CI_DB_query_builder::set() not working with update_batch.
                 * Update `updatedAt` on separate update query.
                 */
                $updatedAtField = 'updatedAt';
                $writeFieldMap = $this->getWriteFieldMap();
                if (isset($writeFieldMap[ $updatedAtField ]))
                {
                    $db = $this->getDb();

                    /*
                     * FIX ORA-00932: inconsistent datatypes: expected CHAR got TIMESTAMP.
                     * Because CodeIgniter use 'CASE WHEN :newTimestampInChar ELSE :oldTimestamp' when doing batch update
                     */
                    $tableUpdatedAtField = $writeFieldMap[ $updatedAtField ];
                    $tableUpdatedAt = sprintf("TO_TIMESTAMP('%s', 'YYYY-MM-DD HH24:MI:SS.ff6')", $this->getCurrentDateTime());
                    $db->set($tableUpdatedAtField, $tableUpdatedAt, false);

                    $tableFilters = $this->getTableFilters($filters);
                    foreach ($tableFilters as $field => $value)
                    {
                        if (is_array($value))
                            $db->where_in($field, $value);
                        else
                            $db->where($field, $value);
                    }

                    $db->update($table);
                }

                return $count;
            });
        }
        catch (BadValueException $e)
        {
            throw new BadValueException(sprintf('Gagal mengubah daftar %s: data kosong', $this->domain), $this->domain);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal mengubah daftar %s', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntity ($table, array $data, array $filters, array $allowedFields = null)
    {
        $data['updatedAt'] = $this->getCurrentDateTime();
        $data['inupby'] = self::$inupby;

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
        $data['updatedAt'] = $this->getCurrentDateTime();
        $data['inupby'] = self::$inupby;

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

    private function getCurrentDateTime ()
    {
        return date('Y-m-d H:i:s.u');
    }

    /**
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function deleteEntity ($table, array $filters)
    {
        // throw exception jika tidak ada entity yang dihapus
        try
        {
            $deletedCount = parent::deleteEntity($table, $filters);
            if ($deletedCount == 0)
                throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);

            return $deletedCount;
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