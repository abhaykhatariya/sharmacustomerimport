<?php
 
namespace Sharma\Vivek\Model;
 
use Exception;
use Generator;
use Magento\Framework\Filesystem\Io\File;
use Magento\Store\Model\StoreManagerInterface;
use Sharma\Vivek\Model\Import\CustomerImport;
use Symfony\Component\Console\Output\OutputInterface;
 
class CreateCustomer
{
	private $file;
	private $storeManagerInterface;
	private $customerImport;
	private $output;

	public function __construct(
	    File $file,
	    StoreManagerInterface $storeManagerInterface,
	    CustomerImport $customerImport
	) {
	    $this->file = $file;
	    $this->storeManagerInterface = $storeManagerInterface;
	    $this->customerImport = $customerImport;
	}

	public function install(string $fixture, OutputInterface $output, $inputType)
	{
	    $this->output = $output;

	    $store = $this->storeManagerInterface->getStore();
	    $websiteId = (int) $this->storeManagerInterface->getWebsite()->getId();
	    $storeId = (int) $store->getId();
	    
	    if($inputType == 'sample-json'){
	    	try {
	    		$jsonData = file_get_contents($fixture, false);	
	    		$customerRow = json_decode($jsonData);
	    		foreach ($customerRow as $cusData) {
			    	
			    	$ot = $this->createCustomer( (array) $cusData, $websiteId, $storeId);
			    	$output->writeln("Imported Customer Email : " . $ot);
			    }	
	    	} catch (Exception $e) {
	    		$output->writeln('<error>'.$e->getMessage(). '</error>');
	    		return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
	    	}
	    }
	    
	    else if ($inputType == 'sample-csv') {
	    	try {
	    		$header = $this->readCsvHeader($fixture)->current();
			 	//$output->writeln("Importing CSV " . print_r($header));
			    $row = $this->readCsvRows($fixture, $header);
			    $row->next();
			 
			    while ($row->valid()) {
			        $data = $row->current();
			        $ot = $this->createCustomer($data, $websiteId, $storeId);
			        $output->writeln("Imported Customer Email : " . $ot);
			        $row->next();
			    }		
	    	} catch (Exception $e) {
	    		$output->writeln('<error>'.$e->getMessage(). '</error>');
	    		return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
	    	}
	    }
	}

	private function readCsvRows(string $file, array $header): ?Generator
	{
	    $handle = fopen($file, 'rb');
	 
	    while (!feof($handle)) {
	        $data = [];
	        $rowData = fgetcsv($handle);
	        if ($rowData) {
	            foreach ($rowData as $key => $value) {
	                $data[$header[$key]] = $value;
	            }
	            yield $data;
	        }
	    }
	 
	    fclose($handle);
	}
	 
	private function readCsvHeader(string $file): ?Generator
	{
	    $handle = fopen($file, 'rb');
	 
	    while (!feof($handle)) {
	        yield fgetcsv($handle);
	    }
	 
	    fclose($handle);
	}

	private function createCustomer(array $data, int $websiteId, int $storeId)
	{
	  try {
	      // collect the customer data
	      $customerData = [
	          'email'         => $data['emailaddress'],
	          '_website'      => 'base',
	          '_store'        => 'default',
	          'confirmation'  => null,
	          'dob'           => null,
	          'firstname'     => $data['fname'],
	          'gender'        => null,
	          'lastname'      => $data['lname'],
	          'middlename'    => null,
	          'prefix'        => null,
	          'store_id'      => $storeId,
	          'website_id'    => $websiteId,
	          'password'      => null,
	          'disable_auto_group_change' => 0
	       ];
	 
	      // save the customer data
	      return $this->customerImport->importCustomerData($customerData);
	  } catch (Exception $e) {
	      $this->output->writeln(
	          '<error>'. $e->getMessage() .'</error>',
	          OutputInterface::OUTPUT_NORMAL
	      );
	  }
	}
}