<?php


abstract class BasePerson extends BaseObject  implements Persistent {


	
	protected static $peer;


	
	protected $id_person;


	
	protected $name_given;


	
	protected $location;


	
	protected $gender;


	
	protected $dob;


	
	protected $name_family;


	
	protected $enabled;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

	
	public function getIdPerson()
	{

		return $this->id_person;
	}

	
	public function getNameGiven()
	{

		return $this->name_given;
	}

	
	public function getLocation()
	{

		return $this->location;
	}

	
	public function getGender()
	{

		return $this->gender;
	}

	
	public function getDob($format = 'Y-m-d')
	{

		if ($this->dob === null || $this->dob === '') {
			return null;
		} elseif (!is_int($this->dob)) {
						$ts = strtotime($this->dob);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse value of [dob] as date/time value: " . var_export($this->dob, true));
			}
		} else {
			$ts = $this->dob;
		}
		if ($format === null) {
			return $ts;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $ts);
		} else {
			return date($format, $ts);
		}
	}

	
	public function getNameFamily()
	{

		return $this->name_family;
	}

	
	public function getEnabled()
	{

		return $this->enabled;
	}

	
	public function setIdPerson($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->id_person !== $v) {
			$this->id_person = $v;
			$this->modifiedColumns[] = PersonPeer::ID_PERSON;
		}

	} 
	
	public function setNameGiven($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->name_given !== $v) {
			$this->name_given = $v;
			$this->modifiedColumns[] = PersonPeer::NAME_GIVEN;
		}

	} 
	
	public function setLocation($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->location !== $v) {
			$this->location = $v;
			$this->modifiedColumns[] = PersonPeer::LOCATION;
		}

	} 
	
	public function setGender($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->gender !== $v) {
			$this->gender = $v;
			$this->modifiedColumns[] = PersonPeer::GENDER;
		}

	} 
	
	public function setDob($v)
	{

		if ($v !== null && !is_int($v)) {
			$ts = strtotime($v);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse date/time value for [dob] from input: " . var_export($v, true));
			}
		} else {
			$ts = $v;
		}
		if ($this->dob !== $ts) {
			$this->dob = $ts;
			$this->modifiedColumns[] = PersonPeer::DOB;
		}

	} 
	
	public function setNameFamily($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->name_family !== $v) {
			$this->name_family = $v;
			$this->modifiedColumns[] = PersonPeer::NAME_FAMILY;
		}

	} 
	
	public function setEnabled($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->enabled !== $v) {
			$this->enabled = $v;
			$this->modifiedColumns[] = PersonPeer::ENABLED;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->id_person = $rs->getString($startcol + 0);

			$this->name_given = $rs->getString($startcol + 1);

			$this->location = $rs->getString($startcol + 2);

			$this->gender = $rs->getString($startcol + 3);

			$this->dob = $rs->getDate($startcol + 4, null);

			$this->name_family = $rs->getString($startcol + 5);

			$this->enabled = $rs->getInt($startcol + 6);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 7; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Person object", $e);
		}
	}

	
	public function delete($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(PersonPeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			PersonPeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	public function save($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(PersonPeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			$affectedRows = $this->doSave($con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	protected function doSave($con)
	{
		$affectedRows = 0; 		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;


						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = PersonPeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setNew(false);
				} else {
					$affectedRows += PersonPeer::doUpdate($this, $con);
				}
				$this->resetModified(); 			}

			$this->alreadyInSave = false;
		}
		return $affectedRows;
	} 
	
	protected $validationFailures = array();

	
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			if (($retval = PersonPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}



			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = PersonPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->getByPosition($pos);
	}

	
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getIdPerson();
				break;
			case 1:
				return $this->getNameGiven();
				break;
			case 2:
				return $this->getLocation();
				break;
			case 3:
				return $this->getGender();
				break;
			case 4:
				return $this->getDob();
				break;
			case 5:
				return $this->getNameFamily();
				break;
			case 6:
				return $this->getEnabled();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = PersonPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getIdPerson(),
			$keys[1] => $this->getNameGiven(),
			$keys[2] => $this->getLocation(),
			$keys[3] => $this->getGender(),
			$keys[4] => $this->getDob(),
			$keys[5] => $this->getNameFamily(),
			$keys[6] => $this->getEnabled(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = PersonPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setIdPerson($value);
				break;
			case 1:
				$this->setNameGiven($value);
				break;
			case 2:
				$this->setLocation($value);
				break;
			case 3:
				$this->setGender($value);
				break;
			case 4:
				$this->setDob($value);
				break;
			case 5:
				$this->setNameFamily($value);
				break;
			case 6:
				$this->setEnabled($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = PersonPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setIdPerson($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setNameGiven($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setLocation($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setGender($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setDob($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setNameFamily($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setEnabled($arr[$keys[6]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(PersonPeer::DATABASE_NAME);

		if ($this->isColumnModified(PersonPeer::ID_PERSON)) $criteria->add(PersonPeer::ID_PERSON, $this->id_person);
		if ($this->isColumnModified(PersonPeer::NAME_GIVEN)) $criteria->add(PersonPeer::NAME_GIVEN, $this->name_given);
		if ($this->isColumnModified(PersonPeer::LOCATION)) $criteria->add(PersonPeer::LOCATION, $this->location);
		if ($this->isColumnModified(PersonPeer::GENDER)) $criteria->add(PersonPeer::GENDER, $this->gender);
		if ($this->isColumnModified(PersonPeer::DOB)) $criteria->add(PersonPeer::DOB, $this->dob);
		if ($this->isColumnModified(PersonPeer::NAME_FAMILY)) $criteria->add(PersonPeer::NAME_FAMILY, $this->name_family);
		if ($this->isColumnModified(PersonPeer::ENABLED)) $criteria->add(PersonPeer::ENABLED, $this->enabled);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(PersonPeer::DATABASE_NAME);

		$criteria->add(PersonPeer::ID_PERSON, $this->id_person);

		return $criteria;
	}

	
	public function getPrimaryKey()
	{
		return $this->getIdPerson();
	}

	
	public function setPrimaryKey($key)
	{
		$this->setIdPerson($key);
	}

	
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setNameGiven($this->name_given);

		$copyObj->setLocation($this->location);

		$copyObj->setGender($this->gender);

		$copyObj->setDob($this->dob);

		$copyObj->setNameFamily($this->name_family);

		$copyObj->setEnabled($this->enabled);


		$copyObj->setNew(true);

		$copyObj->setIdPerson(NULL); 
	}

	
	public function copy($deepCopy = false)
	{
				$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		return $copyObj;
	}

	
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new PersonPeer();
		}
		return self::$peer;
	}

} 