<?php
//header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function main()
{
	$upload = new Upload2DB(); 	
}
class Upload2DB 
{		
	private $dbconn;
    private $csvhandle;
	public function Upload2DB() {    

	$this->checkSubmit();
	$this->validateFile();
	$this->connectDB();
	if($this->validateUser())
		{
			echo "successfully validate user password" ."<br>";
			$this->csv2DB();	
			$this->showMthData();
			$this->showAllData();
		}
	}
	private function checkSubmit()
	{
		if(!isset($_POST["submit"])) {		
				die("wrong button pressed<br>");
		}
	}
	private function validateFile()
	{
		if (
			!isset($_FILES['fileToUpload']['error']) ||
			is_array($_FILES['fileToUpload']['error'])
		) 
		{
			die('Invalid parameters.1<br>');
		}
		if ($_FILES['fileToUpload']['error'] !==UPLOAD_ERR_OK)
		{
			die('Invalid parameters.2<br>');
		}
		    // You should also check filesize here. 
		if ($_FILES['fileToUpload']['size'] > 20000000) { // max 20M
			die('Exceeded filesize limit.<br>');
		}
		
		$FileInfo = pathinfo($_FILES['fileToUpload']['name'],PATHINFO_EXTENSION);
		if($FileInfo !== 'csv'){
			die("Sorry, not a CSV file!<br>");			
		}	
	}
	private function connectDB()
	{
		$servername = "localhost";
		$username = "custom";
		$password = "obscure";
        $dbname   = "uwaterloo";
		$this->dbconn = new mysqli($servername, $username, $password,$dbname);		
		if ($this->dbconn->connect_error) {
			die("Connection failed: " . $this->dbconn->connect_error."<br>");
		} 
		echo ("Connected successfully". "<br>");
	}	
	private function validateUser()
    {
		$stmt = $this->dbconn->prepare("select password from clients where clientname = ? ");
		if ($stmt)
			echo "prepare success" ."<br>";
		else	
			die( "prepare failed" ."<br>");
		$query = "";
		
		$username = $_POST["uname"];
		//echo $username;
		$bindsuccess = $stmt->bind_param("s", $username);

		if ($stmt->execute()) {
			$stmt->bind_result($dbpassword);			
//			echo "got password form db" ."<br>";
			if ( $stmt->fetch()) {										
					if($dbpassword == openssl_digest($_POST['psw'], 'sha512'))
						return true;
					else
					{
						echo "invalid password" ."<br>";
					}
			}
			
		}
		$stmt->close();
		die("done");
        return false;
    }
	
	private function csv2DB()
	{		
		$this->csvhandle = fopen($_FILES['fileToUpload']['tmp_name'], "r");					              
	//	$this->createDBTable();
		$data = fgetcsv($this->csvhandle, 1000, ",");	
		$this->insertCSVData();                    
        fclose($this->csvhandle);
	}
 
	private function createDBTable()
	{
		$data = fgetcsv($this->csvhandle, 1000, ",");	
		$query = "SELECT date FROM Subsidiary";
		$result = mysqli_query($this->dbconn, $query);
		if(empty($result)) {
                $query = "CREATE TABLE Subsidiary (
                          ID int(11) AUTO_INCREMENT,
                          date varchar(255) NOT NULL,
                          category varchar(255) NOT NULL,
                          employee_name varchar(255) not null,
                          employee_addr varchar(255) not null,
                          expense_dscr varchar(255) NOT NULL,
                          pre_tax_v  float,                          
                          tax_name varchar(255),
                          tax_v float,
                          PRIMARY KEY  (ID)
                          )";
                $result = mysqli_query($this->dbconn, $query);
                if(!empty($result)) {
                    echo "succeed to create table <br>";
                }
                else
                    {
                        echo("failed to create table".  mysqli_error($this->dbconn."<br>"));
                    }
                        
                        
        }
        else
            {
                echo "table Subsidiary already exists!<br>";
            }
	}
	
	private function insertCSVData()
	{
		while( ($data = fgetcsv($this->csvhandle, 1000, ",")) !== false)
		{
			$clientname = $_POST["uname"];
			$fieldCount = count($data);
			$data[0] = $this->convertDate($data[0]);
			$data[5] = str_replace(',','',$data[5]);
			$data[7] = str_replace(',','',$data[7]);
			$query  = "insert into Subsidiary  values(NULL,'$clientname','$data[0]','$data[1]','$data[2]','$data[3]','$data[4]',$data[5],'$data[6]',$data[7]);";  
			//echo($query . "<br>");
			//echo($query);
			$result = mysqli_query($this->dbconn, $query);
			if(!empty($result)) {
				//echo ("succeed to insert data<br>");
			}
			else
			{
				die("failed to insert data".  mysqli_error($this->dbconn). "<br>");
			}
		}
		
	}
	private function convertDate($str)
	{
		$date = DateTime::createFromFormat('m/d/Y', $str);
		return $date->format('Y-m-d');
	}
	private function showMthData()
	{
		$clientname = $_POST["uname"];
		$query = "select Year(transaction_date) as 'Year', 
			Month(transaction_date) as 'Month', 
			cast(sum(pre_tax_v + tax_v) as DECIMAL(10,2))  as 'Monthly_Expense'
			from Subsidiary 
			where clientname = '$clientname' 
			group by Year(transaction_date), Month(transaction_date);"; 
		$result = mysqli_query($this->dbconn, $query);
		?>
		<table border="2" align="center">
		<caption>List of Monthly Expenses</caption>
		<br>
  		<thead>
    	<tr>
     	<th>Year</th>  
     	<th>Month</th>		
		<th>Monthly Expense</th>
    	</tr>
  		</thead>
	  	<tbody>
	   <?php
	   
	   	while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
		{   //Creates a loop to loop through results
			echo "<tr> " ;
			echo "<td>"   . $row['Year']           .  "</td>" ;
			echo "<td>"   . $row['Month']           .  "</td>" ;
			echo "<td>"  . number_format($row['Monthly_Expense'], 2, '.', '') ."</td>" ;		  
			echo "</tr>";  
		}
		?>
		</tbody>
		</table>
		<?php
	}
	private function showAllData()
	{
		$query = "SELECT * FROM Subsidiary where clientname = '$_POST[uname]'"; 
		$result = mysqli_query($this->dbconn, $query);

		?>
		<br>
		<br>
		<br>
		
		<table border="2" align="center">
		<caption>List of Detailed Transactions</caption>
		<br>
  		<thead>
    	<tr>
     	<th>Transaction Date</th>  
     	<th>Transaction Category</th>
		<th>Employee Name</th>
		<th>Employee Address</th>
		<th>Expense Description</th>
		<th>Before Tax Amount</th>
		<th>Tax Name</th>
		<th>Tax Amount</th>
    	</tr>
  		</thead>
	  	<tbody>
	   <?php
		
		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
		{   //Creates a loop to loop through results
			echo "<tr> " ;
			echo "<td>"   . $row['transaction_date']           .  "</td>" ;
			echo "<td>"   . $row['category']    .  "</td>" ;
			echo "<td>"   . $row['employee_name']    .  "</td>" ;
			echo "<td>"   . $row['employee_addr']    .  "</td>" ;
			echo "<td>"   . $row['expense_dscr']    .  "</td>" ;
			//$pre_tax_v = $row['pre_tax_v'];
			echo "<td>"  . number_format($row['pre_tax_v'], 2, '.', '') ."</td>" ;
			echo "<td>"   . $row['tax_name']    .  "</td>" ;
			echo "<td>" . number_format($row['tax_v'],2,'.', '') . "</td>";		   
			echo "</tr>";  
		}

		?>
		</tbody>
		</table>
		<?php
		
	}
}

////////////////////program entry point//////////////////////////////////////////////////
main();
?>