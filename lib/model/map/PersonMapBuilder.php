<?php



class PersonMapBuilder {

	
	const CLASS_NAME = 'lib.model.map.PersonMapBuilder';

	
	private $dbMap;

	
	public function isBuilt()
	{
		return ($this->dbMap !== null);
	}

	
	public function getDatabaseMap()
	{
		return $this->dbMap;
	}

	
	public function doBuild()
	{
		$this->dbMap = Propel::getDatabaseMap('propel');

		$tMap = $this->dbMap->addTable('person');
		$tMap->setPhpName('Person');

		$tMap->setUseIdGenerator(false);

		$tMap->addPrimaryKey('ID_PERSON', 'IdPerson', 'string', CreoleTypes::BIGINT, true, null);

		$tMap->addColumn('NAME_GIVEN', 'NameGiven', 'string', CreoleTypes::VARCHAR, false, 20);

		$tMap->addColumn('LOCATION', 'Location', 'string', CreoleTypes::VARCHAR, false, 30);

		$tMap->addColumn('GENDER', 'Gender', 'string', CreoleTypes::CHAR, false, 1);

		$tMap->addColumn('DOB', 'Dob', 'int', CreoleTypes::DATE, false, null);

		$tMap->addColumn('NAME_FAMILY', 'NameFamily', 'string', CreoleTypes::VARCHAR, false, 40);

		$tMap->addColumn('ENABLED', 'Enabled', 'int', CreoleTypes::INTEGER, false, null);

	} 
} 