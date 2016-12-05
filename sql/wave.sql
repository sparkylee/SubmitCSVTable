--------------------initialization sql statements---------
create database uwaterloo;

CREATE USER 'custom'@'localhost' IDENTIFIED BY 'obscure';

 CREATE TABLE Subsidiary (
                          ID int(11) AUTO_INCREMENT,
						  clientname varchar(255) NOT NULL,
                          transaction_date varchar(255) NOT NULL,
                          category varchar(255) NOT NULL,
                          employee_name varchar(255) not null,
                          employee_addr varchar(255) not null,
                          expense_dscr varchar(255) NOT NULL,
                          pre_tax_v  float,                          
                          tax_name varchar(255),
                          tax_v float,
                          PRIMARY KEY  (ID)
                          );
 

GRANT SELECT,INSERT,UPDATE,DELETE
 ON uwaterloo.*
 TO 'custom'@'localhost';

CREATE TABLE clients (                          
  clientname varchar(255) NOT NULL,
  password varchar(255) NOT NULL,                          
  PRIMARY KEY  (clientname)
  );

insert into clients values ("wave",SHA2("bestcompany",512));
----------------testing sql statements-------------------------
delete from Subsidiary;

select Year(transaction_date) as 'Year', 
	Month(transaction_date) as 'Month', 
	cast(sum(pre_tax_v + tax_v) as DECIMAL(10,2))  as 'Monthly Expense'
	from Subsidiary 
	where clientname = 'wave' 
	group by Year(transaction_date), Month(transaction_date);