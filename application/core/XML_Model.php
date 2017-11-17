<?php

/**
 * CSV-persisted collection.
 * 
 * @author		JLP
 * @copyright           Copyright (c) 2010-2017, James L. Parry
 * ------------------------------------------------------------------------
 */
class XML_Model extends Memory_Model
{
//---------------------------------------------------------------------------
//  Housekeeping methods
//---------------------------------------------------------------------------

	/**
	 * Constructor.
	 * @param string $origin Filename of the XML file
	 * @param string $keyfield  Name of the primary key field
	 * @param string $entity	Task name meaningful to the persistence
	 */
	function __construct($origin = null, $keyfield = 'id', $entity = null)
	{
		parent::__construct();

		// guess at persistent name if not specified
		if ($origin == null)
			$this->_origin = get_class($this);
		else
			$this->_origin = $origin;

		// remember the other constructor fields
		$this->_keyfield = $keyfield;
		$this->_entity = $entity;

		// start with an empty collection
		$this->_data = array(); // an array of objects
		$this->fields = array(); // an array of strings
		// and populate the collection
		$this->load();
	}

	/**
	 * Load the collection state appropriately, depending on persistence choice.
	 * OVER-RIDE THIS METHOD in persistence choice implementations
	 */
	protected function load()
	{
            $xml = '';
            if (file_exists($this->_origin)) {
                $xml = simplexml_load_file($this->_origin);
//                print_r($xml);
            } else {
                exit('Failed to open data.xml.');
            }
           
            // build the list of tasks 
            foreach ($xml->item as $item) {
//                print_r($item->id);
                $record = new stdClass();
                $record->id = (string)$item->id;
                $record->task = (string)$item->task;
                $record->priority = (string)$item->priority;
                $record->size = (string)$item->size;
                $record->group = (string)$item->group;
                $record->deadline = (string)$item->deadline;
                $record->status = (string)$item->status;
                $record->flag = (string)$item->flag;
                $this->_data[(string) $item->id] = $record;
            }
                        
            $this->reindex();
        }
        

	/**
	 * Store the collection state appropriately, depending on persistence choice.
	 * OVER-RIDE THIS METHOD in persistence choice implementations
	 */
	protected function store()
	{
		// rebuild the keys table
		$this->reindex();
		//---------------------
		if (($handle = fopen($this->_origin, "w")) !== FALSE)
		{
			fputcsv($handle, $this->_fields);
			foreach ($this->_data as $key => $record)
				fputcsv($handle, array_values((array) $record));
			fclose($handle);
		}
		// --------------------
	}

}