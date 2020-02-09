# Ethereum Module ![GitHub](https://img.shields.io/github/license/Coinsence/coinsence-monorepo.svg) [![Build Status](https://travis-ci.org/Coinsence/humhub-modules-ethereum.svg?branch=master)](https://travis-ci.org/Coinsence/humhub-modules-ethereum) [![Coverage Status](https://coveralls.io/repos/github/Coinsence/humhub-modules-ethereum/badge.svg?branch=master)](https://coveralls.io/github/Coinsence/humhub-modules-ethereum?branch=master)


Ethereum module ensure smart contracts integration with [humhub-modules-xcoin](https://github.com/Coinsence/humhub-modules-xcoin).


# Table of content

- **[Overview](#Overview)**
- **[Development](#Development)**
	- **[Installation](#0)**
	- **[Testing](#1)**

# Overview 
 
Ethereum module represents a connector between [Xcoin](https://github.com/Coinsence/humhub-modules-xcoin) and Blockchain [Smart Contract](https://github.com/Coinsence/coinsence-monorepo). 

This module will not functional without **Xcoin Module**, in order to install this latter check its [documentation](https://github.com/Coinsence/humhub-modules-xcoin).

--- 

Principal calls made through this module : 

*	`POST /coin/mint`  when issuing new coins 
*	`POST /coin/transfer`when transferring coins 
* 	`GET /coin/balance` to get a wallet balance 
* 	`POST /coin/setTransferEventListener` to set a listener for  a specific coin 
*	`POST /dao` to create a new dao 
*	`GET /dao` to get a specific dao details 
*	`POST /space/addMembers` to add member(s) to a specific space 
*	`POST /space/removeMember` to remove a member from a specific space 
*	`POST /space/leave` to leave space
*	`POST /migrate/space` to migrate an existing space before enabling ethereum
*	`POST /wallet` to create wallet(s)
*	`GET /wallet` to get a specific wallet details

# Development 

### Installation 

Two ways are possible : 

- External Installation (recommended for development purpose) : 

	Clone the module outside your [Humhub](http://docs.humhub.org/admin-installation.html) root directory for example in a folder called `modules` : 

		 $ cd modules 
   		 $ git clone https://github.com/Coinsence/humhub-modules-ethereum.git

	Configure `Autoload` path by adding this small code block in the `humhub_root_direcotry/protected/config/common.php` file : 

		return [
          	'params' => [
            	'moduleAutoloadPaths' => ['/path/to/modules'],        
        	],
    	]


- Internal Installation (recommended for direct usage purpose) :

	Just clone the module directly under `humhub_root_direcotry/protected/humhub/modules` 
    
=> Either ways you need to enable the module through through *Browse online* tab in the *Administration menu* under modules section.

### Testing

Codeception framework is used for testing, you can check some of the implemented tests in `tests` folder.

* To simply run tests : 

		$ humhub_root_directory/protected/vendor/bin/codecept run  
    
* To run specific type of tests (`acceptance`, `unit` or `functional`) : 

	 	$ humhub_root_directory/protected/vendor/bin/codecept run unit  
    
* To extract `xml` or `html` output : 

		$ humhub_root_directory/protected/vendor/bin/codecept run unit --coverage-xml --coverage-html
