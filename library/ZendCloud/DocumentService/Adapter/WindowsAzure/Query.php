<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Cloud
 */

namespace ZendCloud\DocumentService\Adapter\WindowsAzure;

use ZendCloud\DocumentService\Adapter\Exception;
use ZendCloud\DocumentService\QueryAdapter\QueryAdapterInterface;
use ZendService\WindowsAzure\Storage\TableEntityQuery;

/**
 * Class implementing Query adapter for working with Azure queries in a
 * structured way
 *
 * @todo       Look into preventing a query injection attack.
 * @category   Zend
 * @package    Zend_Cloud_DocumentService_Adapter
 * @subpackage WindowsAzure
 */
class Query implements QueryAdapterInterface
{
    /**
     * Azure concrete query
     *
     * @var \ZendService\WindowsAzure\Storage\TableEntityQuery
     */
    protected $_azureSelect;

    /**
     * Constructor
     *
     * @param  null|\ZendService\WindowsAzure\Storage\TableEntityQuery $select Table select object
     * @return void
     */
    public function __construct($select = null)
    {
        if (!$select instanceof \ZendService\WindowsAzure\Storage\TableEntityQuery) {
            $select = new TableEntityQuery();
        }
        $this->_azureSelect = $select;
    }

    /**
     * SELECT clause (fields to be selected)
     *
     * Does nothing for Azure.
     *
     * @param  string $select
     * @return \ZendCloud\DocumentService\Adapter\WindowsAzure\Query
     */
    public function select($select)
    {
        return $this;
    }

    /**
     * FROM clause (table name)
     *
     * @param string $from
     * @return \ZendCloud\DocumentService\Adapter\WindowsAzure\Query
     */
    public function from($from)
    {
        $this->_azureSelect->from($from);
        return $this;
    }

    /**
     * WHERE clause (conditions to be used)
     *
     * @param string $where
     * @param mixed $value Value or array of values to be inserted instead of ?
     * @param string $op Operation to use to join where clauses (AND/OR)
     * @return \ZendCloud\DocumentService\Adapter\WindowsAzure\Query
     */
    public function where($where, $value = null, $op = 'and')
    {
        if (!empty($value) && !is_array($value)) {
            // fix buglet in Azure - numeric values are quoted unless passed as an array
            $value = array($value);
        }
        $this->_azureSelect->where($where, $value, $op);
        return $this;
    }

    /**
     * WHERE clause for item ID
     *
     * This one should be used when fetching specific rows since some adapters
     * have special syntax for primary keys
     *
     * @param  array $value Row ID for the document (PartitionKey, RowKey)
     * @return \ZendCloud\DocumentService\Adapter\WindowsAzure\Query
     */
    public function whereId($value)
    {
        if (!is_array($value)) {
            throw new Exception\InvalidArgumentException('Invalid document key');
        }
        $this->_azureSelect->wherePartitionKey($value[0])->whereRowKey($value[1]);
        return $this;
    }

    /**
     * LIMIT clause (how many rows to return)
     *
     * @param  int $limit
     * @return \ZendCloud\DocumentService\Adapter\WindowsAzure\Query
     */
    public function limit($limit)
    {
        $this->_azureSelect->top($limit);
        return $this;
    }

    /**
     * ORDER BY clause (sorting)
     *
     * @todo   Azure service doesn't seem to support this yet; emulate?
     * @param  string $sort Column to sort by
     * @param  string $direction Direction - asc/desc
     * @return \ZendCloud\DocumentService\Adapter\WindowsAzure\Query
     * @throws \ZendCloud\Exception\OperationNotAvailableException
     */
    public function order($sort, $direction = 'asc')
    {
        throw new Exception\OperationNotAvailableException('No support for sorting for Azure yet');
    }

    /**
     * Get Azure select query
     *
     * @return \ZendService\WindowsAzure\Storage\TableEntityQuery
     */
    public function getAzureSelect()
    {
        return  $this->_azureSelect;
    }

    /**
     * Assemble query
     *
     * Simply return the WindowsAzure table entity query object
     *
     * @return \ZendService\WindowsAzure\Storage\TableEntityQuery
     */
    public function assemble()
    {
        return $this->getAzureSelect();
    }
}
