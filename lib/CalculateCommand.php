<?php
//error_reporting(E_ERROR | E_PARSE);
//php composer.phar require symfony/console
require 'vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use League\Csv\Writer;

class CalculateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'calculate';
    public $helper;
    public $assistant;
    public $progressBar;

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Payroll planning');
        $this->addArgument('path', InputArgument::REQUIRED, 'Where do you want to store the results?');
        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This Utility to help a fictional company determine the
dates they need to pay salaries to their sales department.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->formatter = $this->getHelper('formatter');
        $this->helper    = $this->getHelper('question');
        $this->input     = $input;
        $this->output    = $output;

        // Clean filename
        $outputFile = $this->cleanOutPutPath($this->input->getArgument('path'));
                
        // Display introduction
        $introduction = $this->formatter->formatSection(
            'Creating report',
            'Please wait while the payment forecast report is created...'
        );
        $this->output->writeln($introduction);
        // Get the remaining month for the current year
        $todaysMonth = date('n', strtotime(date('d-m-y h:i:s')));
        $currentYear = date('Y');

        // Check if we are in the last month of this year
        if($todaysMonth == 12){
            $this->output->writeln('<error>There is no remaining month for this year</error>');
            return Command::SUCCESS;
        }

        // Set Saturday and Sunday position
        $weekEnd = [6,7];
        $rows = [];
		for($i = $todaysMonth; $i <= 12; $i++){
    		$dateObj   = DateTime::createFromFormat('!m', $i);
    		$monthName = $dateObj->format('F');
    		$month = $i;
    		$rows [] = [$monthName, $this->baseSalaryPaymentDate($month, $currentYear, $monthName, $weekEnd), $this->bonusPaymentDate($month, $currentYear,$monthName, $weekEnd)];
		}
		// Display result in markdown format inside console
		$this->displayOnConsole($this->output, $rows);
		$this->writeToCsv($outputFile, $rows);
        $this->writeToCsv($outputFile, $rows);
        $this->output->writeln('<comment>The report is also available in ' . $outputFile . '</comment>');
        return Command::SUCCESS;
    }

    /**
    * Calculate Salary Payment date
    * @param $month
    * @param $currentYear
    * @param $monthName
    * @param $weekEnd
    * @return mixed
    */
    private function baseSalaryPaymentDate($month, $currentYear, $monthName, $weekEnd)
    {

        // Get the base salary payment date
	    $lastDayOfMonth = date('t', mktime(0, 0, 0, $month, 1, $currentYear));
	    $lastDayOfWeek = date('N', mktime(0, 0, 0, $month, $lastDayOfMonth, $currentYear));
	    
	    if (in_array($lastDayOfWeek, $weekEnd)) {
	        // If the last day of the month is a Saturday or Sunday, set the base salary payment date to the last Friday of the same month
	        $salaryPaymentDate = date('Y-m-d', strtotime("last friday of $monthName $currentYear"));
	    } else {
	        // Otherwise, set the base salary payment date to the last day of the month
	        $salaryPaymentDate = date('Y-m-d', mktime(0, 0, 0, $month, $lastDayOfMonth, $currentYear));
	    }
	    return $salaryPaymentDate;
    }

    /**
    * Calculate Bonus Payment date
    * @param $month
    * @param $currentYear
    * @param $monthName
    * @param $weekEnd
    * @return mixed
    */

    private function bonusPaymentDate($month, $currentYear, $monthName, $weekEnd)
    {
        // Get the bonus payment date
	    $bonusPaymentDate = date('Y-m-d', strtotime("15th $monthName $currentYear"));
	    $bonusPaymentDayOfWeek = date('N', strtotime($bonusPaymentDate));
	    
	    if (in_array($bonusPaymentDayOfWeek, $weekEnd)) {	
	        // If the bonus payment date is a Saturday, set the bonus payment date to the following Wednesday
	        $bonusPaymentDate = date('Y-m-d', strtotime('next wednesday', strtotime($bonusPaymentDate)));
	    } elseif ($bonusPaymentDayOfWeek == 7) {
	        // If the bonus payment date is a Sunday, set the bonus payment date to the following Wednesday
	        $bonusPaymentDate = date('Y-m-d', strtotime('next wednesday', strtotime($bonusPaymentDate)));
	    }
	    return $bonusPaymentDate;
    }

    /**
    * Display result on markdown format
    * @param $output
    * @param $rows
    * @return mixed
    */
    private function displayOnConsole($output, $rows){
    	$table         = new Table($output);
    	$table
            ->setHeaders(array('Month', 'Salary payment date', 'Bonus Payment date'))
            ->setRows($rows)
            ->render();
    }

    /**
    * Write into CSV file
    * @param $path
    * @param $rows
    * @return mixed
    */
    private function writeToCsv($path, $rows){
    	array_unshift($rows, array('MONTH', 'SALARY_PAYMENT_DATE', 'BONUS_PAYMENT_DATE'));
    	$csv = Writer::createFromPath($path, 'w');
		$csv->insertAll($rows);
    }

    /**
    * Clean output file name path
    * @param $outputFile
    * @return mixed
    */
    private function cleanOutPutPath($outputFile){
        return preg_replace('/[^A-Za-z0-9 _ .-]/', '', $outputFile); 
    }
}
