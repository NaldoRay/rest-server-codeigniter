<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
abstract class APP_Model extends MY_Model
{
    private $upsertOnlyFieldMap = [
        'inupby' => 'V_INUPBY'
    ];
    protected $booleanPrefixes = ['F_'];
    protected $numberPrefixes = ['N_'];


    /**
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $dataArr
     * @param array|null $allowedFields
     * @return int
     * @throws InvalidFormatException
     * @throws TransactionException
     */
    protected function createEntities ($db, $table, array $dataArr, array $allowedFields = null)
    {
        try
        {
            return parent::createEntities($db, $table, $dataArr, $allowedFields);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menambah %s', $this->domain), $this->domain);
        }
    }

    /**
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $data
     * @param array|null $allowedFields
     * @return object
     * @throws InvalidFormatException
     * @throws TransactionException
     */
    protected function createEntity ($db, $table, array $data, array $allowedFields = null)
    {
        try
        {
            return parent::createEntity($db, $table, $data, $allowedFields);
        }
        catch (TransactionException $e)
        {
            throw new TransactionException(sprintf('Gagal menambah %s, data kosong', $this->domain), $this->domain);
        }
    }

    /**
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $dataArr
     * @param string $indexField
     * @param array|null $allowedFields
     * @return int
     * @throws InvalidFormatException
     * @throws TransactionException
     */
    protected function updateEntities ($db, $table, array $dataArr, $indexField, array $allowedFields = null)
    {
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
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $data
     * @param array $filters
     * @param array|null $allowedFields
     * @return object
     * @throws InvalidFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntity ($db, $table, array $data, array $filters, array $allowedFields = null)
    {
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

    protected function updateRow ($db, $table, array $data, array $filters)
    {
        $db->set('T_UPDATE', 'CURRENT_TIMESTAMP', false);
        $success = parent::updateRow($db, $table, $data, $filters);
        $db->reset_query();

        return $success;
    }

    /**
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $filters
     * @throws ResourceNotFoundException jika objek yang ingin dihapus tidak ditemukan
     * @throws TransactionException jika objek gagal dihapus
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

    protected function deleteEntityWithConditions ($db, $table, array $conditions)
    {
        try
        {
            parent::deleteEntityWithConditions($db, $table, $conditions);
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
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $filters
     * @param array|null $fields
     * @return object
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
     * @param CI_DB_driver|CI_DB_query_builder $db
     * @param string $table
     * @param array $conditions
     * @param array|null $fields
     * @return object
     * @throws ResourceNotFoundException
     */
    protected function getEntityWithConditions ($db, $table, array $conditions, array $fields = null)
    {
        $entity = parent::getEntityWithConditions($db, $table, $conditions, $fields);

        if (is_null($entity))
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        else
            return $entity;
    }


    protected function toEntity (array $row)
    {
        unset(
            $row['T_INSERT'],
            $row['T_UPDATE'],
            $row['V_INUPBY']
        );

        return parent::toEntity($row);
    }

    protected function toWriteTableData (array $data, array $allowedFields = null)
    {
        if (!empty($allowedFields))
            $allowedFields = array_merge($allowedFields, array_keys($this->upsertOnlyFieldMap));

        return parent::toWriteTableData($data, $allowedFields);
    }

    protected function getWriteFieldMap ()
    {
        return array_merge(parent::getWriteFieldMap(), $this->upsertOnlyFieldMap);
    }

    protected function getNextId ($db, $table, $field, $padLength = 0, array $filters = null)
    {
        $entity = $this->getFirstEntity($db, $table, $filters, null,
            ['-'.$field],
            [$field]
        );
        return $this->getNextEntityId($entity, $field, $padLength);
    }

    protected function getNextEntityId ($entity, $field, $padLength = 0)
    {
        if (is_null($entity))
            $lastId = 0;
        else
            $lastId = (int) $entity->$field;

        $nextId = $lastId + 1;
        if ($padLength > 0)
            return str_pad($nextId, $padLength, '0', STR_PAD_LEFT);
        else
            return $nextId;
    }
}