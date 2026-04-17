<?php

namespace CarriersDelivery\Model\Base;

use \Exception;
use \PDO;
use CarriersDelivery\Model\CarriersdeliveryAreas as ChildCarriersdeliveryAreas;
use CarriersDelivery\Model\CarriersdeliveryAreasQuery as ChildCarriersdeliveryAreasQuery;
use CarriersDelivery\Model\CarriersdeliveryAreascosts as ChildCarriersdeliveryAreascosts;
use CarriersDelivery\Model\CarriersdeliveryAreascostsQuery as ChildCarriersdeliveryAreascostsQuery;
use CarriersDelivery\Model\CarriersdeliveryAreascostskg as ChildCarriersdeliveryAreascostskg;
use CarriersDelivery\Model\CarriersdeliveryAreascostskgQuery as ChildCarriersdeliveryAreascostskgQuery;
use CarriersDelivery\Model\CarriersdeliveryCarrier as ChildCarriersdeliveryCarrier;
use CarriersDelivery\Model\CarriersdeliveryCarrierQuery as ChildCarriersdeliveryCarrierQuery;
use CarriersDelivery\Model\Map\CarriersdeliveryAreasTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;

abstract class CarriersdeliveryAreas implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\CarriersDelivery\\Model\\Map\\CarriersdeliveryAreasTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the name field.
     * @var        string
     */
    protected $name;

    /**
     * The value for the carrier_id field.
     * @var        int
     */
    protected $carrier_id;

    /**
     * The value for the departments field.
     * @var        array
     */
    protected $departments;

    /**
     * The unserialized $departments value - i.e. the persisted object.
     * This is necessary to avoid repeated calls to unserialize() at runtime.
     * @var object
     */
    protected $departments_unserialized;

    /**
     * @var        CarriersdeliveryCarrier
     */
    protected $aCarriersdeliveryCarrier;

    /**
     * @var        ObjectCollection|ChildCarriersdeliveryAreascosts[] Collection to store aggregation of ChildCarriersdeliveryAreascosts objects.
     */
    protected $collCarriersdeliveryAreascostss;
    protected $collCarriersdeliveryAreascostssPartial;

    /**
     * @var        ObjectCollection|ChildCarriersdeliveryAreascostskg[] Collection to store aggregation of ChildCarriersdeliveryAreascostskg objects.
     */
    protected $collCarriersdeliveryAreascostskgs;
    protected $collCarriersdeliveryAreascostskgsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $carriersdeliveryAreascostssScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $carriersdeliveryAreascostskgsScheduledForDeletion = null;

    /**
     * Initializes internal state of CarriersDelivery\Model\Base\CarriersdeliveryAreas object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>CarriersdeliveryAreas</code> instance.  If
     * <code>obj</code> is an instance of <code>CarriersdeliveryAreas</code>, delegates to
     * <code>equals(CarriersdeliveryAreas)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return CarriersdeliveryAreas The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return CarriersdeliveryAreas The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [name] column value.
     *
     * @return   string
     */
    public function getName(): string
    {

        return $this->name;
    }

    /**
     * Get the [carrier_id] column value.
     *
     * @return   int
     */
    public function getCarrierId()
    {

        return $this->carrier_id;
    }

    /**
     * Get the [departments] column value.
     *
     * @return   array
     */
    public function getDepartments()
    {
        if (null === $this->departments_unserialized) {
            $this->departments_unserialized = array();
        }
        if (!$this->departments_unserialized && null !== $this->departments) {
            $departments_unserialized = substr($this->departments, 2, -2);
            $this->departments_unserialized = $departments_unserialized ? explode(' | ', $departments_unserialized) : array();
        }

        return $this->departments_unserialized;
    }

    /**
     * Test the presence of a value in the [departments] array column value.
     * @param      mixed $value
     *
     * @return boolean
     */
    public function hasDepartment($value)
    {
        return in_array($value, $this->getDepartments());
    } // hasDepartment()

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[CarriersdeliveryAreasTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [name] column.
     *
     * @param      string $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[CarriersdeliveryAreasTableMap::NAME] = true;
        }


        return $this;
    } // setName()

    /**
     * Set the value of [carrier_id] column.
     *
     * @param      int $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function setCarrierId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->carrier_id !== $v) {
            $this->carrier_id = $v;
            $this->modifiedColumns[CarriersdeliveryAreasTableMap::CARRIER_ID] = true;
        }

        if ($this->aCarriersdeliveryCarrier !== null && $this->aCarriersdeliveryCarrier->getId() !== $v) {
            $this->aCarriersdeliveryCarrier = null;
        }


        return $this;
    } // setCarrierId()

    /**
     * Set the value of [departments] column.
     *
     * @param      array $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function setDepartments($v)
    {
        if ($this->departments_unserialized !== $v) {
            $this->departments_unserialized = $v;
            $this->departments = '| ' . implode(' | ', $v) . ' |';
            $this->modifiedColumns[CarriersdeliveryAreasTableMap::DEPARTMENTS] = true;
        }


        return $this;
    } // setDepartments()

    /**
     * Adds a value to the [departments] array column value.
     * @param      mixed $value
     *
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function addDepartment($value)
    {
        $currentArray = $this->getDepartments();
        $currentArray []= $value;
        $this->setDepartments($currentArray);

        return $this;
    } // addDepartment()

    /**
     * Removes a value from the [departments] array column value.
     * @param      mixed $value
     *
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function removeDepartment($value)
    {
        $targetArray = array();
        foreach ($this->getDepartments() as $element) {
            if ($element != $value) {
                $targetArray []= $element;
            }
        }
        $this->setDepartments($targetArray);

        return $this;
    } // removeDepartment()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : CarriersdeliveryAreasTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : CarriersdeliveryAreasTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : CarriersdeliveryAreasTableMap::translateFieldName('CarrierId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->carrier_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : CarriersdeliveryAreasTableMap::translateFieldName('Departments', TableMap::TYPE_PHPNAME, $indexType)];
            $this->departments = $col;
            $this->departments_unserialized = null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 4; // 4 = CarriersdeliveryAreasTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \CarriersDelivery\Model\CarriersdeliveryAreas object", 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aCarriersdeliveryCarrier !== null && $this->carrier_id !== $this->aCarriersdeliveryCarrier->getId()) {
            $this->aCarriersdeliveryCarrier = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CarriersdeliveryAreasTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildCarriersdeliveryAreasQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aCarriersdeliveryCarrier = null;
            $this->collCarriersdeliveryAreascostss = null;

            $this->collCarriersdeliveryAreascostskgs = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see CarriersdeliveryAreas::setDeleted()
     * @see CarriersdeliveryAreas::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CarriersdeliveryAreasTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildCarriersdeliveryAreasQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CarriersdeliveryAreasTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                CarriersdeliveryAreasTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aCarriersdeliveryCarrier !== null) {
                if ($this->aCarriersdeliveryCarrier->isModified() || $this->aCarriersdeliveryCarrier->isNew()) {
                    $affectedRows += $this->aCarriersdeliveryCarrier->save($con);
                }
                $this->setCarriersdeliveryCarrier($this->aCarriersdeliveryCarrier);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->carriersdeliveryAreascostssScheduledForDeletion !== null) {
                if (!$this->carriersdeliveryAreascostssScheduledForDeletion->isEmpty()) {
                    \CarriersDelivery\Model\CarriersdeliveryAreascostsQuery::create()
                        ->filterByPrimaryKeys($this->carriersdeliveryAreascostssScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->carriersdeliveryAreascostssScheduledForDeletion = null;
                }
            }

                if ($this->collCarriersdeliveryAreascostss !== null) {
            foreach ($this->collCarriersdeliveryAreascostss as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->carriersdeliveryAreascostskgsScheduledForDeletion !== null) {
                if (!$this->carriersdeliveryAreascostskgsScheduledForDeletion->isEmpty()) {
                    \CarriersDelivery\Model\CarriersdeliveryAreascostskgQuery::create()
                        ->filterByPrimaryKeys($this->carriersdeliveryAreascostskgsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->carriersdeliveryAreascostskgsScheduledForDeletion = null;
                }
            }

                if ($this->collCarriersdeliveryAreascostskgs !== null) {
            foreach ($this->collCarriersdeliveryAreascostskgs as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[CarriersdeliveryAreasTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CarriersdeliveryAreasTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::NAME)) {
            $modifiedColumns[':p' . $index++]  = 'NAME';
        }
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::CARRIER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'CARRIER_ID';
        }
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::DEPARTMENTS)) {
            $modifiedColumns[':p' . $index++]  = 'DEPARTMENTS';
        }

        $sql = sprintf(
            'INSERT INTO carriersdelivery_areas (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'NAME':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case 'CARRIER_ID':
                        $stmt->bindValue($identifier, $this->carrier_id, PDO::PARAM_INT);
                        break;
                    case 'DEPARTMENTS':
                        $stmt->bindValue($identifier, $this->departments, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CarriersdeliveryAreasTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getName();
                break;
            case 2:
                return $this->getCarrierId();
                break;
            case 3:
                return $this->getDepartments();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['CarriersdeliveryAreas'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['CarriersdeliveryAreas'][$this->getPrimaryKey()] = true;
        $keys = CarriersdeliveryAreasTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getName(),
            $keys[2] => $this->getCarrierId(),
            $keys[3] => $this->getDepartments(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aCarriersdeliveryCarrier) {
                $result['CarriersdeliveryCarrier'] = $this->aCarriersdeliveryCarrier->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collCarriersdeliveryAreascostss) {
                $result['CarriersdeliveryAreascostss'] = $this->collCarriersdeliveryAreascostss->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCarriersdeliveryAreascostskgs) {
                $result['CarriersdeliveryAreascostskgs'] = $this->collCarriersdeliveryAreascostskgs->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CarriersdeliveryAreasTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setName($value);
                break;
            case 2:
                $this->setCarrierId($value);
                break;
            case 3:
                if (!is_array($value)) {
                    $v = trim(substr($value, 2, -2));
                    $value = $v ? explode(' | ', $v) : array();
                }
                $this->setDepartments($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = CarriersdeliveryAreasTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setName($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setCarrierId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setDepartments($arr[$keys[3]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CarriersdeliveryAreasTableMap::DATABASE_NAME);

        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::ID)) $criteria->add(CarriersdeliveryAreasTableMap::ID, $this->id);
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::NAME)) $criteria->add(CarriersdeliveryAreasTableMap::NAME, $this->name);
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::CARRIER_ID)) $criteria->add(CarriersdeliveryAreasTableMap::CARRIER_ID, $this->carrier_id);
        if ($this->isColumnModified(CarriersdeliveryAreasTableMap::DEPARTMENTS)) $criteria->add(CarriersdeliveryAreasTableMap::DEPARTMENTS, $this->departments);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(CarriersdeliveryAreasTableMap::DATABASE_NAME);
        $criteria->add(CarriersdeliveryAreasTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \CarriersDelivery\Model\CarriersdeliveryAreas (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setName($this->getName());
        $copyObj->setCarrierId($this->getCarrierId());
        $copyObj->setDepartments($this->getDepartments());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getCarriersdeliveryAreascostss() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCarriersdeliveryAreascosts($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCarriersdeliveryAreascostskgs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCarriersdeliveryAreascostskg($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \CarriersDelivery\Model\CarriersdeliveryAreas Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildCarriersdeliveryCarrier object.
     *
     * @param                  ChildCarriersdeliveryCarrier $v
     * @return                 \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCarriersdeliveryCarrier(ChildCarriersdeliveryCarrier $v = null)
    {
        if ($v === null) {
            $this->setCarrierId(NULL);
        } else {
            $this->setCarrierId($v->getId());
        }

        $this->aCarriersdeliveryCarrier = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCarriersdeliveryCarrier object, it will not be re-added.
        if ($v !== null) {
            $v->addCarriersdeliveryAreas($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCarriersdeliveryCarrier object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildCarriersdeliveryCarrier The associated ChildCarriersdeliveryCarrier object.
     * @throws PropelException
     */
    public function getCarriersdeliveryCarrier(ConnectionInterface $con = null)
    {
        if ($this->aCarriersdeliveryCarrier === null && ($this->carrier_id !== null)) {
            $this->aCarriersdeliveryCarrier = ChildCarriersdeliveryCarrierQuery::create()->findPk($this->carrier_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCarriersdeliveryCarrier->addCarriersdeliveryAreass($this);
             */
        }

        return $this->aCarriersdeliveryCarrier;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('CarriersdeliveryAreascosts' == $relationName) {
            return $this->initCarriersdeliveryAreascostss();
        }
        if ('CarriersdeliveryAreascostskg' == $relationName) {
            return $this->initCarriersdeliveryAreascostskgs();
        }
    }

    /**
     * Clears out the collCarriersdeliveryAreascostss collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCarriersdeliveryAreascostss()
     */
    public function clearCarriersdeliveryAreascostss()
    {
        $this->collCarriersdeliveryAreascostss = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCarriersdeliveryAreascostss collection loaded partially.
     */
    public function resetPartialCarriersdeliveryAreascostss($v = true)
    {
        $this->collCarriersdeliveryAreascostssPartial = $v;
    }

    /**
     * Initializes the collCarriersdeliveryAreascostss collection.
     *
     * By default this just sets the collCarriersdeliveryAreascostss collection to an empty array (like clearcollCarriersdeliveryAreascostss());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCarriersdeliveryAreascostss($overrideExisting = true)
    {
        if (null !== $this->collCarriersdeliveryAreascostss && !$overrideExisting) {
            return;
        }
        $this->collCarriersdeliveryAreascostss = new ObjectCollection();
        $this->collCarriersdeliveryAreascostss->setModel('\CarriersDelivery\Model\CarriersdeliveryAreascosts');
    }

    /**
     * Gets an array of ChildCarriersdeliveryAreascosts objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCarriersdeliveryAreas is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCarriersdeliveryAreascosts[] List of ChildCarriersdeliveryAreascosts objects
     * @throws PropelException
     */
    public function getCarriersdeliveryAreascostss($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryAreascostssPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryAreascostss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryAreascostss) {
                // return empty collection
                $this->initCarriersdeliveryAreascostss();
            } else {
                $collCarriersdeliveryAreascostss = ChildCarriersdeliveryAreascostsQuery::create(null, $criteria)
                    ->filterByCarriersdeliveryAreas($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCarriersdeliveryAreascostssPartial && count($collCarriersdeliveryAreascostss)) {
                        $this->initCarriersdeliveryAreascostss(false);

                        foreach ($collCarriersdeliveryAreascostss as $obj) {
                            if (false == $this->collCarriersdeliveryAreascostss->contains($obj)) {
                                $this->collCarriersdeliveryAreascostss->append($obj);
                            }
                        }

                        $this->collCarriersdeliveryAreascostssPartial = true;
                    }

                    reset($collCarriersdeliveryAreascostss);

                    return $collCarriersdeliveryAreascostss;
                }

                if ($partial && $this->collCarriersdeliveryAreascostss) {
                    foreach ($this->collCarriersdeliveryAreascostss as $obj) {
                        if ($obj->isNew()) {
                            $collCarriersdeliveryAreascostss[] = $obj;
                        }
                    }
                }

                $this->collCarriersdeliveryAreascostss = $collCarriersdeliveryAreascostss;
                $this->collCarriersdeliveryAreascostssPartial = false;
            }
        }

        return $this->collCarriersdeliveryAreascostss;
    }

    /**
     * Sets a collection of CarriersdeliveryAreascosts objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $carriersdeliveryAreascostss A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildCarriersdeliveryAreas The current object (for fluent API support)
     */
    public function setCarriersdeliveryAreascostss(Collection $carriersdeliveryAreascostss, ConnectionInterface $con = null)
    {
        $carriersdeliveryAreascostssToDelete = $this->getCarriersdeliveryAreascostss(new Criteria(), $con)->diff($carriersdeliveryAreascostss);


        $this->carriersdeliveryAreascostssScheduledForDeletion = $carriersdeliveryAreascostssToDelete;

        foreach ($carriersdeliveryAreascostssToDelete as $carriersdeliveryAreascostsRemoved) {
            $carriersdeliveryAreascostsRemoved->setCarriersdeliveryAreas(null);
        }

        $this->collCarriersdeliveryAreascostss = null;
        foreach ($carriersdeliveryAreascostss as $carriersdeliveryAreascosts) {
            $this->addCarriersdeliveryAreascosts($carriersdeliveryAreascosts);
        }

        $this->collCarriersdeliveryAreascostss = $carriersdeliveryAreascostss;
        $this->collCarriersdeliveryAreascostssPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CarriersdeliveryAreascosts objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CarriersdeliveryAreascosts objects.
     * @throws PropelException
     */
    public function countCarriersdeliveryAreascostss(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryAreascostssPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryAreascostss || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryAreascostss) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCarriersdeliveryAreascostss());
            }

            $query = ChildCarriersdeliveryAreascostsQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCarriersdeliveryAreas($this)
                ->count($con);
        }

        return count($this->collCarriersdeliveryAreascostss);
    }

    /**
     * Method called to associate a ChildCarriersdeliveryAreascosts object to this object
     * through the ChildCarriersdeliveryAreascosts foreign key attribute.
     *
     * @param    ChildCarriersdeliveryAreascosts $l ChildCarriersdeliveryAreascosts
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function addCarriersdeliveryAreascosts(ChildCarriersdeliveryAreascosts $l)
    {
        if ($this->collCarriersdeliveryAreascostss === null) {
            $this->initCarriersdeliveryAreascostss();
            $this->collCarriersdeliveryAreascostssPartial = true;
        }

        if (!in_array($l, $this->collCarriersdeliveryAreascostss->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCarriersdeliveryAreascosts($l);
        }

        return $this;
    }

    /**
     * @param CarriersdeliveryAreascosts $carriersdeliveryAreascosts The carriersdeliveryAreascosts object to add.
     */
    protected function doAddCarriersdeliveryAreascosts($carriersdeliveryAreascosts)
    {
        $this->collCarriersdeliveryAreascostss[]= $carriersdeliveryAreascosts;
        $carriersdeliveryAreascosts->setCarriersdeliveryAreas($this);
    }

    /**
     * @param  CarriersdeliveryAreascosts $carriersdeliveryAreascosts The carriersdeliveryAreascosts object to remove.
     * @return ChildCarriersdeliveryAreas The current object (for fluent API support)
     */
    public function removeCarriersdeliveryAreascosts($carriersdeliveryAreascosts)
    {
        if ($this->getCarriersdeliveryAreascostss()->contains($carriersdeliveryAreascosts)) {
            $this->collCarriersdeliveryAreascostss->remove($this->collCarriersdeliveryAreascostss->search($carriersdeliveryAreascosts));
            if (null === $this->carriersdeliveryAreascostssScheduledForDeletion) {
                $this->carriersdeliveryAreascostssScheduledForDeletion = clone $this->collCarriersdeliveryAreascostss;
                $this->carriersdeliveryAreascostssScheduledForDeletion->clear();
            }
            $this->carriersdeliveryAreascostssScheduledForDeletion[]= clone $carriersdeliveryAreascosts;
            $carriersdeliveryAreascosts->setCarriersdeliveryAreas(null);
        }

        return $this;
    }

    /**
     * Clears out the collCarriersdeliveryAreascostskgs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCarriersdeliveryAreascostskgs()
     */
    public function clearCarriersdeliveryAreascostskgs()
    {
        $this->collCarriersdeliveryAreascostskgs = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCarriersdeliveryAreascostskgs collection loaded partially.
     */
    public function resetPartialCarriersdeliveryAreascostskgs($v = true)
    {
        $this->collCarriersdeliveryAreascostskgsPartial = $v;
    }

    /**
     * Initializes the collCarriersdeliveryAreascostskgs collection.
     *
     * By default this just sets the collCarriersdeliveryAreascostskgs collection to an empty array (like clearcollCarriersdeliveryAreascostskgs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCarriersdeliveryAreascostskgs($overrideExisting = true)
    {
        if (null !== $this->collCarriersdeliveryAreascostskgs && !$overrideExisting) {
            return;
        }
        $this->collCarriersdeliveryAreascostskgs = new ObjectCollection();
        $this->collCarriersdeliveryAreascostskgs->setModel('\CarriersDelivery\Model\CarriersdeliveryAreascostskg');
    }

    /**
     * Gets an array of ChildCarriersdeliveryAreascostskg objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCarriersdeliveryAreas is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCarriersdeliveryAreascostskg[] List of ChildCarriersdeliveryAreascostskg objects
     * @throws PropelException
     */
    public function getCarriersdeliveryAreascostskgs($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryAreascostskgsPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryAreascostskgs || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryAreascostskgs) {
                // return empty collection
                $this->initCarriersdeliveryAreascostskgs();
            } else {
                $collCarriersdeliveryAreascostskgs = ChildCarriersdeliveryAreascostskgQuery::create(null, $criteria)
                    ->filterByCarriersdeliveryAreas($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCarriersdeliveryAreascostskgsPartial && count($collCarriersdeliveryAreascostskgs)) {
                        $this->initCarriersdeliveryAreascostskgs(false);

                        foreach ($collCarriersdeliveryAreascostskgs as $obj) {
                            if (false == $this->collCarriersdeliveryAreascostskgs->contains($obj)) {
                                $this->collCarriersdeliveryAreascostskgs->append($obj);
                            }
                        }

                        $this->collCarriersdeliveryAreascostskgsPartial = true;
                    }

                    reset($collCarriersdeliveryAreascostskgs);

                    return $collCarriersdeliveryAreascostskgs;
                }

                if ($partial && $this->collCarriersdeliveryAreascostskgs) {
                    foreach ($this->collCarriersdeliveryAreascostskgs as $obj) {
                        if ($obj->isNew()) {
                            $collCarriersdeliveryAreascostskgs[] = $obj;
                        }
                    }
                }

                $this->collCarriersdeliveryAreascostskgs = $collCarriersdeliveryAreascostskgs;
                $this->collCarriersdeliveryAreascostskgsPartial = false;
            }
        }

        return $this->collCarriersdeliveryAreascostskgs;
    }

    /**
     * Sets a collection of CarriersdeliveryAreascostskg objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $carriersdeliveryAreascostskgs A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildCarriersdeliveryAreas The current object (for fluent API support)
     */
    public function setCarriersdeliveryAreascostskgs(Collection $carriersdeliveryAreascostskgs, ConnectionInterface $con = null)
    {
        $carriersdeliveryAreascostskgsToDelete = $this->getCarriersdeliveryAreascostskgs(new Criteria(), $con)->diff($carriersdeliveryAreascostskgs);


        $this->carriersdeliveryAreascostskgsScheduledForDeletion = $carriersdeliveryAreascostskgsToDelete;

        foreach ($carriersdeliveryAreascostskgsToDelete as $carriersdeliveryAreascostskgRemoved) {
            $carriersdeliveryAreascostskgRemoved->setCarriersdeliveryAreas(null);
        }

        $this->collCarriersdeliveryAreascostskgs = null;
        foreach ($carriersdeliveryAreascostskgs as $carriersdeliveryAreascostskg) {
            $this->addCarriersdeliveryAreascostskg($carriersdeliveryAreascostskg);
        }

        $this->collCarriersdeliveryAreascostskgs = $carriersdeliveryAreascostskgs;
        $this->collCarriersdeliveryAreascostskgsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CarriersdeliveryAreascostskg objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CarriersdeliveryAreascostskg objects.
     * @throws PropelException
     */
    public function countCarriersdeliveryAreascostskgs(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryAreascostskgsPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryAreascostskgs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryAreascostskgs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCarriersdeliveryAreascostskgs());
            }

            $query = ChildCarriersdeliveryAreascostskgQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCarriersdeliveryAreas($this)
                ->count($con);
        }

        return count($this->collCarriersdeliveryAreascostskgs);
    }

    /**
     * Method called to associate a ChildCarriersdeliveryAreascostskg object to this object
     * through the ChildCarriersdeliveryAreascostskg foreign key attribute.
     *
     * @param    ChildCarriersdeliveryAreascostskg $l ChildCarriersdeliveryAreascostskg
     * @return   \CarriersDelivery\Model\CarriersdeliveryAreas The current object (for fluent API support)
     */
    public function addCarriersdeliveryAreascostskg(ChildCarriersdeliveryAreascostskg $l)
    {
        if ($this->collCarriersdeliveryAreascostskgs === null) {
            $this->initCarriersdeliveryAreascostskgs();
            $this->collCarriersdeliveryAreascostskgsPartial = true;
        }

        if (!in_array($l, $this->collCarriersdeliveryAreascostskgs->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCarriersdeliveryAreascostskg($l);
        }

        return $this;
    }

    /**
     * @param CarriersdeliveryAreascostskg $carriersdeliveryAreascostskg The carriersdeliveryAreascostskg object to add.
     */
    protected function doAddCarriersdeliveryAreascostskg($carriersdeliveryAreascostskg)
    {
        $this->collCarriersdeliveryAreascostskgs[]= $carriersdeliveryAreascostskg;
        $carriersdeliveryAreascostskg->setCarriersdeliveryAreas($this);
    }

    /**
     * @param  CarriersdeliveryAreascostskg $carriersdeliveryAreascostskg The carriersdeliveryAreascostskg object to remove.
     * @return ChildCarriersdeliveryAreas The current object (for fluent API support)
     */
    public function removeCarriersdeliveryAreascostskg($carriersdeliveryAreascostskg)
    {
        if ($this->getCarriersdeliveryAreascostskgs()->contains($carriersdeliveryAreascostskg)) {
            $this->collCarriersdeliveryAreascostskgs->remove($this->collCarriersdeliveryAreascostskgs->search($carriersdeliveryAreascostskg));
            if (null === $this->carriersdeliveryAreascostskgsScheduledForDeletion) {
                $this->carriersdeliveryAreascostskgsScheduledForDeletion = clone $this->collCarriersdeliveryAreascostskgs;
                $this->carriersdeliveryAreascostskgsScheduledForDeletion->clear();
            }
            $this->carriersdeliveryAreascostskgsScheduledForDeletion[]= clone $carriersdeliveryAreascostskg;
            $carriersdeliveryAreascostskg->setCarriersdeliveryAreas(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->name = null;
        $this->carrier_id = null;
        $this->departments = null;
        $this->departments_unserialized = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collCarriersdeliveryAreascostss) {
                foreach ($this->collCarriersdeliveryAreascostss as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCarriersdeliveryAreascostskgs) {
                foreach ($this->collCarriersdeliveryAreascostskgs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collCarriersdeliveryAreascostss = null;
        $this->collCarriersdeliveryAreascostskgs = null;
        $this->aCarriersdeliveryCarrier = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CarriersdeliveryAreasTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
