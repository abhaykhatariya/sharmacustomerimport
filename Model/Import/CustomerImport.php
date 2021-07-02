<?php
 
namespace Sharma\Vivek\Model\Import;
 
use Magento\CustomerImportExport\Model\Import\Customer;
 
class CustomerImport extends Customer
{
  	public function importCustomerData(array $rowData)
	{
	  $this->prepareCustomerData($rowData);
	  $entitiesToCreate = [];
	  $entitiesToUpdate = [];
	  $entitiesToDelete = [];
	 
	  $processedData = $this->_prepareDataForUpdate($rowData);
	  $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
	  $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
	 
	  $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);
	 
	  if ($entitiesToCreate || $entitiesToUpdate) {
	      $this->_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
	  }
	 
	  return $entitiesToCreate[0]['email'] ?? $entitiesToUpdate[0]['email'] ?? null;
	}
}