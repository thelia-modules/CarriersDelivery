<?php

namespace CarriersDelivery\Model\Base;

use \Exception;
use \PDO;
use CarriersDelivery\Model\CarriersdeliveryAreas as ChildCarriersdeliveryAreas;
use CarriersDelivery\Model\CarriersdeliveryAreasQuery as ChildCarriersdeliveryAreasQuery;
use CarriersDelivery\Model\CarriersdeliveryCarrier as ChildCarriersdeliveryCarrier;
use CarriersDelivery\Model\CarriersdeliveryCarrierQuery as ChildCarriersdeliveryCarrierQuery;
use CarriersDelivery\Model\CarriersdeliveryProduct as ChildCarriersdeliveryProduct;
use CarriersDelivery\Model\CarriersdeliveryProductQuery as ChildCarriersdeliveryProductQuery;
use CarriersDelivery\Model\Map\CarriersdeliveryCarrierTableMap;
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
use Thelia\Model\Country as ChildCountry;
use Thelia\Model\CountryQuery;

abstract class CarriersdeliveryCarrier implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\CarriersDelivery\\Model\\Map\\CarriersdeliveryCarrierTableMap';


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
     * The value for the country_id field.
     * @var        int
     */
    protected $country_id;

    /**
     * The value for the diesel_tax_percent field.
     * Note: this column has a database default value of: '0.000000'
     * @var        string
     */
    protected $diesel_tax_percent;

    /**
     * The value for the fees_cost field.
     * Note: this column has a database default value of: '0.000000'
     * @var        string
     */
    protected $fees_cost;

    /**
     * The value for the unit_per_kg field.
     * @var        int
     */
    protected $unit_per_kg;

    /**
     * @var        Country
     */
    protected $aCountry;

    /**
     * @var        ObjectCollection|ChildCarriersdeliveryAreas[] Collection to store aggregation of ChildCarriersdeliveryAreas objects.
     */
    protected $collCarriersdeliveryAreass;
    protected $collCarriersdeliveryAreassPartial;

    /**
     * @var        ObjectCollection|ChildCarriersdeliveryProduct[] Collection to store aggregation of ChildCarriersdeliveryProduct objects.
     */
    protected $collCarriersdeliveryProducts;
    protected $collCarriersdeliveryProductsPartial;

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
    protected $carriersdeliveryAreassScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $carriersdeliveryProductsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->diesel_tax_percent = '0.000000';
        $this->fees_cost = '0.000000';
    }

    /**
     * Initializes internal state of CarriersDelivery\Model\Base\CarriersdeliveryCarrier object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
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
     * Compares this with another <code>CarriersdeliveryCarrier</code> instance.  If
     * <code>obj</code> is an instance of <code>CarriersdeliveryCarrier</code>, delegates to
     * <code>equals(CarriersdeliveryCarrier)</code>.  Otherwise, returns <code>false</code>.
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
     * @return CarriersdeliveryCarrier The current object, for fluid interface
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
     * @return CarriersdeliveryCarrier The current object, for fluid interface
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
     * Get the [country_id] column value.
     *
     * @return   int
     */
    public function getCountryId()
    {

        return $this->country_id;
    }

    /**
     * Get the [diesel_tax_percent] column value.
     *
     * @return   string
     */
    public function getDieselTaxPercent()
    {

        return $this->diesel_tax_percent;
    }

    /**
     * Get the [fees_cost] column value.
     *
     * @return   string
     */
    public function getFeesCost()
    {

        return $this->fees_cost;
    }

    /**
     * Get the [unit_per_kg] column value.
     *
     * @return   int
     */
    public function getUnitPerKg()
    {

        return $this->unit_per_kg;
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[CarriersdeliveryCarrierTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [name] column.
     *
     * @param      string $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[CarriersdeliveryCarrierTableMap::NAME] = true;
        }


        return $this;
    } // setName()

    /**
     * Set the value of [country_id] column.
     *
     * @param      int $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setCountryId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->country_id !== $v) {
            $this->country_id = $v;
            $this->modifiedColumns[CarriersdeliveryCarrierTableMap::COUNTRY_ID] = true;
        }

        if ($this->aCountry !== null && $this->aCountry->getId() !== $v) {
            $this->aCountry = null;
        }


        return $this;
    } // setCountryId()

    /**
     * Set the value of [diesel_tax_percent] column.
     *
     * @param      string $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setDieselTaxPercent($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->diesel_tax_percent !== $v) {
            $this->diesel_tax_percent = $v;
            $this->modifiedColumns[CarriersdeliveryCarrierTableMap::DIESEL_TAX_PERCENT] = true;
        }


        return $this;
    } // setDieselTaxPercent()

    /**
     * Set the value of [fees_cost] column.
     *
     * @param      string $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setFeesCost($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->fees_cost !== $v) {
            $this->fees_cost = $v;
            $this->modifiedColumns[CarriersdeliveryCarrierTableMap::FEES_COST] = true;
        }


        return $this;
    } // setFeesCost()

    /**
     * Set the value of [unit_per_kg] column.
     *
     * @param      int $v new value
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setUnitPerKg($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->unit_per_kg !== $v) {
            $this->unit_per_kg = $v;
            $this->modifiedColumns[CarriersdeliveryCarrierTableMap::UNIT_PER_KG] = true;
        }


        return $this;
    } // setUnitPerKg()

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
            if ($this->diesel_tax_percent !== '0.000000') {
                return false;
            }

            if ($this->fees_cost !== '0.000000') {
                return false;
            }

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


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : CarriersdeliveryCarrierTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : CarriersdeliveryCarrierTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : CarriersdeliveryCarrierTableMap::translateFieldName('CountryId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->country_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : CarriersdeliveryCarrierTableMap::translateFieldName('DieselTaxPercent', TableMap::TYPE_PHPNAME, $indexType)];
            $this->diesel_tax_percent = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : CarriersdeliveryCarrierTableMap::translateFieldName('FeesCost', TableMap::TYPE_PHPNAME, $indexType)];
            $this->fees_cost = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : CarriersdeliveryCarrierTableMap::translateFieldName('UnitPerKg', TableMap::TYPE_PHPNAME, $indexType)];
            $this->unit_per_kg = (null !== $col) ? (int) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = CarriersdeliveryCarrierTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \CarriersDelivery\Model\CarriersdeliveryCarrier object", 0, $e);
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
        if ($this->aCountry !== null && $this->country_id !== $this->aCountry->getId()) {
            $this->aCountry = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(CarriersdeliveryCarrierTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildCarriersdeliveryCarrierQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aCountry = null;
            $this->collCarriersdeliveryAreass = null;

            $this->collCarriersdeliveryProducts = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see CarriersdeliveryCarrier::setDeleted()
     * @see CarriersdeliveryCarrier::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CarriersdeliveryCarrierTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildCarriersdeliveryCarrierQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(CarriersdeliveryCarrierTableMap::DATABASE_NAME);
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
                CarriersdeliveryCarrierTableMap::addInstanceToPool($this);
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

            if ($this->aCountry !== null) {
                if ($this->aCountry->isModified() || $this->aCountry->isNew()) {
                    $affectedRows += $this->aCountry->save($con);
                }
                $this->setCountry($this->aCountry);
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

            if ($this->carriersdeliveryAreassScheduledForDeletion !== null) {
                if (!$this->carriersdeliveryAreassScheduledForDeletion->isEmpty()) {
                    \CarriersDelivery\Model\CarriersdeliveryAreasQuery::create()
                        ->filterByPrimaryKeys($this->carriersdeliveryAreassScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->carriersdeliveryAreassScheduledForDeletion = null;
                }
            }

                if ($this->collCarriersdeliveryAreass !== null) {
            foreach ($this->collCarriersdeliveryAreass as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->carriersdeliveryProductsScheduledForDeletion !== null) {
                if (!$this->carriersdeliveryProductsScheduledForDeletion->isEmpty()) {
                    \CarriersDelivery\Model\CarriersdeliveryProductQuery::create()
                        ->filterByPrimaryKeys($this->carriersdeliveryProductsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->carriersdeliveryProductsScheduledForDeletion = null;
                }
            }

                if ($this->collCarriersdeliveryProducts !== null) {
            foreach ($this->collCarriersdeliveryProducts as $referrerFK) {
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

        $this->modifiedColumns[CarriersdeliveryCarrierTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CarriersdeliveryCarrierTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::NAME)) {
            $modifiedColumns[':p' . $index++]  = 'NAME';
        }
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::COUNTRY_ID)) {
            $modifiedColumns[':p' . $index++]  = 'COUNTRY_ID';
        }
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::DIESEL_TAX_PERCENT)) {
            $modifiedColumns[':p' . $index++]  = 'DIESEL_TAX_PERCENT';
        }
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::FEES_COST)) {
            $modifiedColumns[':p' . $index++]  = 'FEES_COST';
        }
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::UNIT_PER_KG)) {
            $modifiedColumns[':p' . $index++]  = 'UNIT_PER_KG';
        }

        $sql = sprintf(
            'INSERT INTO carriersdelivery_carrier (%s) VALUES (%s)',
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
                    case 'COUNTRY_ID':
                        $stmt->bindValue($identifier, $this->country_id, PDO::PARAM_INT);
                        break;
                    case 'DIESEL_TAX_PERCENT':
                        $stmt->bindValue($identifier, $this->diesel_tax_percent, PDO::PARAM_STR);
                        break;
                    case 'FEES_COST':
                        $stmt->bindValue($identifier, $this->fees_cost, PDO::PARAM_STR);
                        break;
                    case 'UNIT_PER_KG':
                        $stmt->bindValue($identifier, $this->unit_per_kg, PDO::PARAM_INT);
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
        $pos = CarriersdeliveryCarrierTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getCountryId();
                break;
            case 3:
                return $this->getDieselTaxPercent();
                break;
            case 4:
                return $this->getFeesCost();
                break;
            case 5:
                return $this->getUnitPerKg();
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
        if (isset($alreadyDumpedObjects['CarriersdeliveryCarrier'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['CarriersdeliveryCarrier'][$this->getPrimaryKey()] = true;
        $keys = CarriersdeliveryCarrierTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getName(),
            $keys[2] => $this->getCountryId(),
            $keys[3] => $this->getDieselTaxPercent(),
            $keys[4] => $this->getFeesCost(),
            $keys[5] => $this->getUnitPerKg(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aCountry) {
                $result['Country'] = $this->aCountry->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collCarriersdeliveryAreass) {
                $result['CarriersdeliveryAreass'] = $this->collCarriersdeliveryAreass->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCarriersdeliveryProducts) {
                $result['CarriersdeliveryProducts'] = $this->collCarriersdeliveryProducts->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = CarriersdeliveryCarrierTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setCountryId($value);
                break;
            case 3:
                $this->setDieselTaxPercent($value);
                break;
            case 4:
                $this->setFeesCost($value);
                break;
            case 5:
                $this->setUnitPerKg($value);
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
        $keys = CarriersdeliveryCarrierTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setName($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setCountryId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setDieselTaxPercent($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setFeesCost($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setUnitPerKg($arr[$keys[5]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CarriersdeliveryCarrierTableMap::DATABASE_NAME);

        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::ID)) $criteria->add(CarriersdeliveryCarrierTableMap::ID, $this->id);
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::NAME)) $criteria->add(CarriersdeliveryCarrierTableMap::NAME, $this->name);
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::COUNTRY_ID)) $criteria->add(CarriersdeliveryCarrierTableMap::COUNTRY_ID, $this->country_id);
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::DIESEL_TAX_PERCENT)) $criteria->add(CarriersdeliveryCarrierTableMap::DIESEL_TAX_PERCENT, $this->diesel_tax_percent);
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::FEES_COST)) $criteria->add(CarriersdeliveryCarrierTableMap::FEES_COST, $this->fees_cost);
        if ($this->isColumnModified(CarriersdeliveryCarrierTableMap::UNIT_PER_KG)) $criteria->add(CarriersdeliveryCarrierTableMap::UNIT_PER_KG, $this->unit_per_kg);

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
        $criteria = new Criteria(CarriersdeliveryCarrierTableMap::DATABASE_NAME);
        $criteria->add(CarriersdeliveryCarrierTableMap::ID, $this->id);

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
     * @param      object $copyObj An object of \CarriersDelivery\Model\CarriersdeliveryCarrier (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setName($this->getName());
        $copyObj->setCountryId($this->getCountryId());
        $copyObj->setDieselTaxPercent($this->getDieselTaxPercent());
        $copyObj->setFeesCost($this->getFeesCost());
        $copyObj->setUnitPerKg($this->getUnitPerKg());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getCarriersdeliveryAreass() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCarriersdeliveryAreas($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCarriersdeliveryProducts() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCarriersdeliveryProduct($relObj->copy($deepCopy));
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
     * @return                 \CarriersDelivery\Model\CarriersdeliveryCarrier Clone of current object.
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
     * Declares an association between this object and a ChildCountry object.
     *
     * @param                  ChildCountry $v
     * @return                 \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCountry(ChildCountry $v = null)
    {
        if ($v === null) {
            $this->setCountryId(NULL);
        } else {
            $this->setCountryId($v->getId());
        }

        $this->aCountry = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCountry object, it will not be re-added.
        if ($v !== null) {
            $v->addCarriersdeliveryCarrier($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCountry object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildCountry The associated ChildCountry object.
     * @throws PropelException
     */
    public function getCountry(ConnectionInterface $con = null)
    {
        if ($this->aCountry === null && ($this->country_id !== null)) {
            $this->aCountry = CountryQuery::create()->findPk($this->country_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCountry->addCarriersdeliveryCarriers($this);
             */
        }

        return $this->aCountry;
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
        if ('CarriersdeliveryAreas' == $relationName) {
            return $this->initCarriersdeliveryAreass();
        }
        if ('CarriersdeliveryProduct' == $relationName) {
            return $this->initCarriersdeliveryProducts();
        }
    }

    /**
     * Clears out the collCarriersdeliveryAreass collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCarriersdeliveryAreass()
     */
    public function clearCarriersdeliveryAreass()
    {
        $this->collCarriersdeliveryAreass = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCarriersdeliveryAreass collection loaded partially.
     */
    public function resetPartialCarriersdeliveryAreass($v = true)
    {
        $this->collCarriersdeliveryAreassPartial = $v;
    }

    /**
     * Initializes the collCarriersdeliveryAreass collection.
     *
     * By default this just sets the collCarriersdeliveryAreass collection to an empty array (like clearcollCarriersdeliveryAreass());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCarriersdeliveryAreass($overrideExisting = true)
    {
        if (null !== $this->collCarriersdeliveryAreass && !$overrideExisting) {
            return;
        }
        $this->collCarriersdeliveryAreass = new ObjectCollection();
        $this->collCarriersdeliveryAreass->setModel('\CarriersDelivery\Model\CarriersdeliveryAreas');
    }

    /**
     * Gets an array of ChildCarriersdeliveryAreas objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCarriersdeliveryCarrier is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCarriersdeliveryAreas[] List of ChildCarriersdeliveryAreas objects
     * @throws PropelException
     */
    public function getCarriersdeliveryAreass($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryAreassPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryAreass || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryAreass) {
                // return empty collection
                $this->initCarriersdeliveryAreass();
            } else {
                $collCarriersdeliveryAreass = ChildCarriersdeliveryAreasQuery::create(null, $criteria)
                    ->filterByCarriersdeliveryCarrier($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCarriersdeliveryAreassPartial && count($collCarriersdeliveryAreass)) {
                        $this->initCarriersdeliveryAreass(false);

                        foreach ($collCarriersdeliveryAreass as $obj) {
                            if (false == $this->collCarriersdeliveryAreass->contains($obj)) {
                                $this->collCarriersdeliveryAreass->append($obj);
                            }
                        }

                        $this->collCarriersdeliveryAreassPartial = true;
                    }

                    reset($collCarriersdeliveryAreass);

                    return $collCarriersdeliveryAreass;
                }

                if ($partial && $this->collCarriersdeliveryAreass) {
                    foreach ($this->collCarriersdeliveryAreass as $obj) {
                        if ($obj->isNew()) {
                            $collCarriersdeliveryAreass[] = $obj;
                        }
                    }
                }

                $this->collCarriersdeliveryAreass = $collCarriersdeliveryAreass;
                $this->collCarriersdeliveryAreassPartial = false;
            }
        }

        return $this->collCarriersdeliveryAreass;
    }

    /**
     * Sets a collection of CarriersdeliveryAreas objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $carriersdeliveryAreass A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildCarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setCarriersdeliveryAreass(Collection $carriersdeliveryAreass, ConnectionInterface $con = null)
    {
        $carriersdeliveryAreassToDelete = $this->getCarriersdeliveryAreass(new Criteria(), $con)->diff($carriersdeliveryAreass);


        $this->carriersdeliveryAreassScheduledForDeletion = $carriersdeliveryAreassToDelete;

        foreach ($carriersdeliveryAreassToDelete as $carriersdeliveryAreasRemoved) {
            $carriersdeliveryAreasRemoved->setCarriersdeliveryCarrier(null);
        }

        $this->collCarriersdeliveryAreass = null;
        foreach ($carriersdeliveryAreass as $carriersdeliveryAreas) {
            $this->addCarriersdeliveryAreas($carriersdeliveryAreas);
        }

        $this->collCarriersdeliveryAreass = $carriersdeliveryAreass;
        $this->collCarriersdeliveryAreassPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CarriersdeliveryAreas objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CarriersdeliveryAreas objects.
     * @throws PropelException
     */
    public function countCarriersdeliveryAreass(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryAreassPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryAreass || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryAreass) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCarriersdeliveryAreass());
            }

            $query = ChildCarriersdeliveryAreasQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCarriersdeliveryCarrier($this)
                ->count($con);
        }

        return count($this->collCarriersdeliveryAreass);
    }

    /**
     * Method called to associate a ChildCarriersdeliveryAreas object to this object
     * through the ChildCarriersdeliveryAreas foreign key attribute.
     *
     * @param    ChildCarriersdeliveryAreas $l ChildCarriersdeliveryAreas
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function addCarriersdeliveryAreas(ChildCarriersdeliveryAreas $l)
    {
        if ($this->collCarriersdeliveryAreass === null) {
            $this->initCarriersdeliveryAreass();
            $this->collCarriersdeliveryAreassPartial = true;
        }

        if (!in_array($l, $this->collCarriersdeliveryAreass->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCarriersdeliveryAreas($l);
        }

        return $this;
    }

    /**
     * @param CarriersdeliveryAreas $carriersdeliveryAreas The carriersdeliveryAreas object to add.
     */
    protected function doAddCarriersdeliveryAreas($carriersdeliveryAreas)
    {
        $this->collCarriersdeliveryAreass[]= $carriersdeliveryAreas;
        $carriersdeliveryAreas->setCarriersdeliveryCarrier($this);
    }

    /**
     * @param  CarriersdeliveryAreas $carriersdeliveryAreas The carriersdeliveryAreas object to remove.
     * @return ChildCarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function removeCarriersdeliveryAreas($carriersdeliveryAreas)
    {
        if ($this->getCarriersdeliveryAreass()->contains($carriersdeliveryAreas)) {
            $this->collCarriersdeliveryAreass->remove($this->collCarriersdeliveryAreass->search($carriersdeliveryAreas));
            if (null === $this->carriersdeliveryAreassScheduledForDeletion) {
                $this->carriersdeliveryAreassScheduledForDeletion = clone $this->collCarriersdeliveryAreass;
                $this->carriersdeliveryAreassScheduledForDeletion->clear();
            }
            $this->carriersdeliveryAreassScheduledForDeletion[]= clone $carriersdeliveryAreas;
            $carriersdeliveryAreas->setCarriersdeliveryCarrier(null);
        }

        return $this;
    }

    /**
     * Clears out the collCarriersdeliveryProducts collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCarriersdeliveryProducts()
     */
    public function clearCarriersdeliveryProducts()
    {
        $this->collCarriersdeliveryProducts = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCarriersdeliveryProducts collection loaded partially.
     */
    public function resetPartialCarriersdeliveryProducts($v = true)
    {
        $this->collCarriersdeliveryProductsPartial = $v;
    }

    /**
     * Initializes the collCarriersdeliveryProducts collection.
     *
     * By default this just sets the collCarriersdeliveryProducts collection to an empty array (like clearcollCarriersdeliveryProducts());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCarriersdeliveryProducts($overrideExisting = true)
    {
        if (null !== $this->collCarriersdeliveryProducts && !$overrideExisting) {
            return;
        }
        $this->collCarriersdeliveryProducts = new ObjectCollection();
        $this->collCarriersdeliveryProducts->setModel('\CarriersDelivery\Model\CarriersdeliveryProduct');
    }

    /**
     * Gets an array of ChildCarriersdeliveryProduct objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCarriersdeliveryCarrier is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCarriersdeliveryProduct[] List of ChildCarriersdeliveryProduct objects
     * @throws PropelException
     */
    public function getCarriersdeliveryProducts($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryProductsPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryProducts || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryProducts) {
                // return empty collection
                $this->initCarriersdeliveryProducts();
            } else {
                $collCarriersdeliveryProducts = ChildCarriersdeliveryProductQuery::create(null, $criteria)
                    ->filterByCarriersdeliveryCarrier($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCarriersdeliveryProductsPartial && count($collCarriersdeliveryProducts)) {
                        $this->initCarriersdeliveryProducts(false);

                        foreach ($collCarriersdeliveryProducts as $obj) {
                            if (false == $this->collCarriersdeliveryProducts->contains($obj)) {
                                $this->collCarriersdeliveryProducts->append($obj);
                            }
                        }

                        $this->collCarriersdeliveryProductsPartial = true;
                    }

                    reset($collCarriersdeliveryProducts);

                    return $collCarriersdeliveryProducts;
                }

                if ($partial && $this->collCarriersdeliveryProducts) {
                    foreach ($this->collCarriersdeliveryProducts as $obj) {
                        if ($obj->isNew()) {
                            $collCarriersdeliveryProducts[] = $obj;
                        }
                    }
                }

                $this->collCarriersdeliveryProducts = $collCarriersdeliveryProducts;
                $this->collCarriersdeliveryProductsPartial = false;
            }
        }

        return $this->collCarriersdeliveryProducts;
    }

    /**
     * Sets a collection of CarriersdeliveryProduct objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $carriersdeliveryProducts A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildCarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function setCarriersdeliveryProducts(Collection $carriersdeliveryProducts, ConnectionInterface $con = null)
    {
        $carriersdeliveryProductsToDelete = $this->getCarriersdeliveryProducts(new Criteria(), $con)->diff($carriersdeliveryProducts);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->carriersdeliveryProductsScheduledForDeletion = clone $carriersdeliveryProductsToDelete;

        foreach ($carriersdeliveryProductsToDelete as $carriersdeliveryProductRemoved) {
            $carriersdeliveryProductRemoved->setCarriersdeliveryCarrier(null);
        }

        $this->collCarriersdeliveryProducts = null;
        foreach ($carriersdeliveryProducts as $carriersdeliveryProduct) {
            $this->addCarriersdeliveryProduct($carriersdeliveryProduct);
        }

        $this->collCarriersdeliveryProducts = $carriersdeliveryProducts;
        $this->collCarriersdeliveryProductsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CarriersdeliveryProduct objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CarriersdeliveryProduct objects.
     * @throws PropelException
     */
    public function countCarriersdeliveryProducts(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCarriersdeliveryProductsPartial && !$this->isNew();
        if (null === $this->collCarriersdeliveryProducts || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCarriersdeliveryProducts) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCarriersdeliveryProducts());
            }

            $query = ChildCarriersdeliveryProductQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCarriersdeliveryCarrier($this)
                ->count($con);
        }

        return count($this->collCarriersdeliveryProducts);
    }

    /**
     * Method called to associate a ChildCarriersdeliveryProduct object to this object
     * through the ChildCarriersdeliveryProduct foreign key attribute.
     *
     * @param    ChildCarriersdeliveryProduct $l ChildCarriersdeliveryProduct
     * @return   \CarriersDelivery\Model\CarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function addCarriersdeliveryProduct(ChildCarriersdeliveryProduct $l)
    {
        if ($this->collCarriersdeliveryProducts === null) {
            $this->initCarriersdeliveryProducts();
            $this->collCarriersdeliveryProductsPartial = true;
        }

        if (!in_array($l, $this->collCarriersdeliveryProducts->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCarriersdeliveryProduct($l);
        }

        return $this;
    }

    /**
     * @param CarriersdeliveryProduct $carriersdeliveryProduct The carriersdeliveryProduct object to add.
     */
    protected function doAddCarriersdeliveryProduct($carriersdeliveryProduct)
    {
        $this->collCarriersdeliveryProducts[]= $carriersdeliveryProduct;
        $carriersdeliveryProduct->setCarriersdeliveryCarrier($this);
    }

    /**
     * @param  CarriersdeliveryProduct $carriersdeliveryProduct The carriersdeliveryProduct object to remove.
     * @return ChildCarriersdeliveryCarrier The current object (for fluent API support)
     */
    public function removeCarriersdeliveryProduct($carriersdeliveryProduct)
    {
        if ($this->getCarriersdeliveryProducts()->contains($carriersdeliveryProduct)) {
            $this->collCarriersdeliveryProducts->remove($this->collCarriersdeliveryProducts->search($carriersdeliveryProduct));
            if (null === $this->carriersdeliveryProductsScheduledForDeletion) {
                $this->carriersdeliveryProductsScheduledForDeletion = clone $this->collCarriersdeliveryProducts;
                $this->carriersdeliveryProductsScheduledForDeletion->clear();
            }
            $this->carriersdeliveryProductsScheduledForDeletion[]= clone $carriersdeliveryProduct;
            $carriersdeliveryProduct->setCarriersdeliveryCarrier(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this CarriersdeliveryCarrier is new, it will return
     * an empty collection; or if this CarriersdeliveryCarrier has previously
     * been saved, it will retrieve related CarriersdeliveryProducts from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in CarriersdeliveryCarrier.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return Collection|ChildCarriersdeliveryProduct[] List of ChildCarriersdeliveryProduct objects
     */
    public function getCarriersdeliveryProductsJoinProduct($criteria = null, $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildCarriersdeliveryProductQuery::create(null, $criteria);
        $query->joinWith('Product', $joinBehavior);

        return $this->getCarriersdeliveryProducts($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->name = null;
        $this->country_id = null;
        $this->diesel_tax_percent = null;
        $this->fees_cost = null;
        $this->unit_per_kg = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
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
            if ($this->collCarriersdeliveryAreass) {
                foreach ($this->collCarriersdeliveryAreass as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCarriersdeliveryProducts) {
                foreach ($this->collCarriersdeliveryProducts as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collCarriersdeliveryAreass = null;
        $this->collCarriersdeliveryProducts = null;
        $this->aCountry = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CarriersdeliveryCarrierTableMap::DEFAULT_STRING_FORMAT);
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
