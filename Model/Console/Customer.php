<?php
namespace Sharma\Vivek\Model\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Sharma\Vivek\Model\CreateCustomer;

class Customer extends Command
{
	const FILE_NAME = "file_name";

    const INPUT_PROFILE = "file_type";
    
    public function __construct(
	    Filesystem $filesystem,
	    CreateCustomer $customer,
	    State $state
	) {
	parent::__construct();
	    $this->filesystem = $filesystem;
	    $this->customer = $customer;
	    $this->state = $state;
	}

    protected function configure()
    {
        $this->setName('customer:import')
             ->setDescription('Import customer using CSV file format.');
        $this->setDefinition([
            new InputArgument(self::INPUT_PROFILE, InputArgument::OPTIONAL, "File type")
        ]);
        $this->addArgument(
            self::FILE_NAME);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument(self::FILE_NAME);
        $inputType = $input->getArgument(self::INPUT_PROFILE);
        
        if($inputType == ''){
        	$output->writeln("Please specify profile.");
        	return Cli::RETURN_SUCCESS;
        }

        if($inputType != 'sample-csv' && $inputType != 'sample-json'){
        	$output->writeln("Profile is not correct.");
        	return Cli::RETURN_SUCCESS;
        }

        if($name == ''){
        	$output->writeln("Please specify file name after profile.");
        	return Cli::RETURN_SUCCESS;
        }
       
      
        try {
	      $this->state->setAreaCode(Area::AREA_GLOBAL);
	 	
	      $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
	      $fixture = $mediaDir->getAbsolutePath() . 'import_csv/'.$name;
	      $ext = pathinfo($fixture, PATHINFO_EXTENSION);

	      if(($inputType != 'sample-csv' || $ext != 'csv') && ($inputType != 'sample-json' || $ext != 'json') ) {
        	$output->writeln("Invalid file format.");
        	return Cli::RETURN_SUCCESS;
        }
	 		// $output->writeln("Importing CSV " . $fixture);
	      $this->customer->install($fixture, $output, $inputType );
	 
	      return Cli::RETURN_SUCCESS;
	  } catch (Exception $e) {
	      $msg = $e->getMessage();
	      $output->writeln("<error>$msg</error>", OutputInterface::OUTPUT_NORMAL);
	      return Cli::RETURN_FAILURE;
	  }
    }
}