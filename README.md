Maverick-Entity-Generator
=========================

This Magento extension enables you to generate entities for tests :

- Generate Customers with fake data using FAKE library
- Generate orders for registred customers
    - orders uses randomly Checkmo and banktransfer payment methods
    - orders uses flaterate_flaterate shipping method
- Automatically assign created customers to website, store and group
- Based on payment methods, order invoice and shipment can created

Requirements
============
- This extension uses the PHP library "Faker".

  @see : https://github.com/fzaninotto/Faker

- This extension uses "Check" and "Bank Transfer" payment methods, please make sure those methods are enabled

Installation
============
1. First Install the PHP library FAKE :
---------------------------------------
* In your Magento root folder, dowload composer by running :
```curl -sS https://getcomposer.org/installer | php```

* Or, if you don't have curl : 
```
php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
```
* Or download it manually from : [Composer Website](http://getcomposer.org/download/)
    
      
* Once it is downloaded, create (always in your root folder) a json file called composer.json and put in it the following content :

```
{
    "require":{
        "fzaninotto/faker" : "*"
    }
}
```
     
     
* Finally run : php composer.phar install
  This will install the latest version of Faker library in a folder named "vendor" in your root directory and will also generate the autoloader automatically


2. Install "Entity Generator" extension :
-----------------------------------------

* Install it with [modman](https://github.com/colinmollenhour/modman/wiki)
* Or download and install it manually

3. Refresh your magento cache : 
-------------------------------
Log in in your magento Backend and configure the extension

When you generate an entity you can see the generation details in real time :
``` tail -f var/log/maverick_generator.log ```

Compatibility
=============
- Tested only on Magento >= 1.7

Support and Contribution
========================
If you have any issues with this extension, please open an issue on Github.

Any contributions are highly appreciated. If you want to contribute, please open [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Copyright and License
=====================
License   : [OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php).

Copyright : (c) 2013 Mohammed NAHHAS
