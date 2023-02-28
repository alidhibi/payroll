## Payroll calculation tools

### Requirement

In order to use this tools, you need to:
- PHP >= 7.4 installed
- PHP cli available on environnement variable
- Composer installed and added to your environnement PATH
- Internet connexion

### Starting
- Unzip the folder project in your workspace
- Run a command line interface
- Navigate to the project directory
- Install package dependencies

```
C:\workspace\Blauwtrust_Payroll> composer install
```

> This command will install the required packages inside a vendor folder

### Usage
This package is based on Symfony console component.
You can run the following commands

#### Display help

```
C:\workspace\Blauwtrust_Payroll> php payroll calculate --help
```

#### Calculate a payment roll 

```
C:\workspace\Blauwtrust_Payroll> php payroll calculate 2023.csv
```

#### TODO
- [ ] Add file name validation
- [ ] Optimize cleanOutPutPath function on CalculateCommand.php
- [ ] Add unit test

